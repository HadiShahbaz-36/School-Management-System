<?php
session_start();
if (!isset($_SESSION['username'])) { 
    die("Access Denied: Please login first."); 
}

$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_db";
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Database Connection Fail"); }

$fee_id = isset($_GET['challan_id']) ? $_GET['challan_id'] : 0; 

// --- 1. FETCH STUDENT & CHALLAN DATA ---
$sql = "SELECT f.*, s.*, 
        (SELECT discount_percentage FROM scholarships WHERE stu_enrollment_number = s.stu_enrollment_number LIMIT 1) as scholar_per,
        (SELECT reason FROM scholarships WHERE stu_enrollment_number = s.stu_enrollment_number LIMIT 1) as scholar_reason
        FROM fees f 
        JOIN students s ON f.student_enrollment = s.stu_enrollment_number 
        WHERE f.fee_id = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$fee_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) { die("Challan record not found."); }

$enroll = $data['stu_enrollment_number'];
$kuickpay_id = "25240" . $enroll;

// --- 2. FETCH MULTI-COLUMN RATES ---
$rate_stmt = $conn->prepare("SELECT amount, allied_charges, annual_charges FROM fee_structure WHERE wing_name = ? AND category_name = ?");
$rate_stmt->execute([$data['stu_wing'], $data['fee_category']]);
$rates = $rate_stmt->fetch(PDO::FETCH_ASSOC);

$base_tuition = $rates['amount'] ?: 0;
$allied = $rates['allied_charges'] ?: 0;
$annual = $rates['annual_charges'] ?: 0;

// --- 3. PREPARE DYNAMIC LIST ---
$fee_items = [];
$fee_items[] = ["desc" => "MONTHLY TUITION FEE", "amt" => $base_tuition];

// Scholarship Logic (On Tuition Only)
$scholar_amt = ($data['scholar_per'] > 0) ? ($base_tuition * ($data['scholar_per'] / 100)) : 0;
if($scholar_amt > 0) {
    $fee_items[] = ["desc" => "SCHOLARSHIP (".$data['scholar_reason']." ".$data['scholar_per']."%)", "amt" => -$scholar_amt];
}

// Adding Allied & Annual
if($allied > 0) $fee_items[] = ["desc" => "ALLIED CHARGES", "amt" => $allied];
if($annual > 0) $fee_items[] = ["desc" => "ANNUAL CHARGES", "amt" => $annual];

// Adjustments (Fines etc)
$adj_stmt = $conn->prepare("SELECT * FROM fee_adjustments WHERE stu_enrollment_number = ? AND status = 'Applied'");
$adj_stmt->execute([$enroll]);
$adjustments = $adj_stmt->fetchAll(PDO::FETCH_ASSOC);

$current_total = ($base_tuition - $scholar_amt) + $allied + $annual;

foreach($adjustments as $adj) {
    $amt = ($adj['adj_type'] == 'Fine') ? $adj['amount'] : -$adj['amount'];
    $fee_items[] = ["desc" => strtoupper($adj['adj_type'] . ": " . $adj['reason']), "amt" => $amt];
    $current_total += $amt;
}

$after_due = $current_total + 200;
$copies = ["Bank Copy", "College Copy", "Student Copy"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BCI_Challan_<?php echo $enroll; ?></title>
    <style>
        @page { size: landscape; margin: 4mm; }
        body { font-family: 'Arial Narrow', sans-serif; margin: 0; padding: 0; color: #000; }
        .challan-wrapper { display: flex; width: 100%; gap: 5px; }
        .copy-box { flex: 1; padding: 10px; border: 1px solid #000; border-right: 1.5px dashed #444; position: relative; }
        .copy-box:last-child { border-right: 1px solid #000; }
        
        .header { display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #000; padding-bottom: 5px; }
        .header img { width: 35px; }
        .brand h1 { font-size: 12px; margin: 0; font-weight: bold; color: #003366; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 9px; }
        .info-table td { border: 0.5px solid #000; padding: 2px 5px; }
        .label { background: #f0f0f0; font-weight: bold; width: 25%; }

        .k-pay-strip { background: #000; color: #fff; text-align: center; padding: 3px; font-weight: bold; font-size: 10px; margin: 5px 0; }

        .fee-details { width: 100%; border-collapse: collapse; font-size: 9px; margin-top: 5px; }
        .fee-details th { border: 1px solid #000; background: #f0f0f0; padding: 3px; text-align: left; }
        .fee-details td { border: 1px solid #000; padding: 3px; }

        .total-area { margin-top: 5px; border: 1.5px solid #000; font-size: 10px; font-weight: bold; }
        .row { display: flex; justify-content: space-between; padding: 3px 6px; border-bottom: 1px solid #000; }
        .row.dark { background: #000; color: #fff; border-bottom: none; }

        .barcode { font-family: 'Libre Barcode 39', cursive; font-size: 25px; text-align: center; margin-top: 5px; }
        .footer-note { font-size: 7.5px; margin-top: 8px; line-height: 1.2; font-style: italic; }
        
        @media print { .no-print { display: none; } }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+39&display=swap" rel="stylesheet">
</head>
<body>

<div class="no-print" style="background:#222; padding:15px; text-align:center;">
    <button onclick="window.print()" style="padding:10px 40px; background:#00d4ff; color:#000; border:none; border-radius:8px; font-weight:bold; cursor:pointer; font-size:16px;">
        Click to Print Official Challan
    </button>
</div>

<div class="challan-wrapper">
    <?php foreach ($copies as $title): ?>
    <div class="copy-box">
        <div style="display:flex; justify-content:space-between; font-size:8px; font-weight:bold; margin-bottom:2px;">
            <span><?php echo strtoupper($title); ?></span>
            <span>CH-<?php echo $data['fee_id']; ?></span>
        </div>

        <div class="header">
            <img src="assets/img/logo.png" alt="BCI">
            <div class="brand">
                <h1>BAHRIA COLLEGE ISLAMABAD</h1>
                <p style="font-size:8px; margin:0;">MONTH: <?php echo strtoupper($data['month_for']); ?> 2026</p>
            </div>
        </div>

        <table class="info-table">
            <tr><td class="label">REG #</td><td colspan="3"><b><?php echo $enroll; ?></b></td></tr>
            <tr><td class="label">NAME</td><td colspan="3"><?php echo strtoupper($data['stu_name']); ?></td></tr>
            <tr><td class="label">FATHER</td><td><?php echo strtoupper($data['stu_father_name']); ?></td><td class="label">CLASS</td><td><?php echo $data['stu_class']; ?></td></tr>
        </table>

        <div class="k-pay-strip">KUICKPAY ID: <?php echo $kuickpay_id; ?></div>

        <table class="fee-details">
            <thead>
                <tr><th width="10%">S#</th><th>PARTICULARS</th><th align="right">AMOUNT</th></tr>
            </thead>
            <tbody>
                <?php $n=1; foreach($fee_items as $item): ?>
                <tr>
                    <td align="center"><?php echo $n++; ?></td>
                    <td><?php echo $item['desc']; ?></td>
                    <td align="right"><?php echo number_format(abs($item['amt'])); ?> <?php echo ($item['amt']<0)?'(-)':'' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-area">
            <div class="row"><span>PAYABLE WITHIN DUE DATE</span><span>Rs. <?php echo number_format($current_total); ?></span></div>
            <div class="row dark"><span>AFTER DUE DATE (+200 FINE)</span><span>Rs. <?php echo number_format($after_due); ?></span></div>
        </div>

        <div class="barcode">*<?php echo $kuickpay_id; ?>*</div>

        <div class="footer-note">
            <b>NOTE:</b> Fees must be deposited by 20th of each month. Late fee of Rs. 200/- will be charged after due date.
            3 Months default leads to Struck Off.
        </div>
        
        <div style="margin-top:10px; font-size:8px; border-top:1px solid #ccc; padding-top:3px;">
            <b>BANK:</b> Askari Bank (000793890000073)
        </div>
    </div>
    <?php endforeach; ?>
</div>

</body>
</html>