<?php
session_start();

// Database Connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "students_db";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Security Check
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$currentMonth = date('F');
$currentYear = date('Y');

// 1. Total Collection (Paid)
$stmt = $conn->prepare("SELECT SUM(amount_paid) FROM fees WHERE month_for = ? AND status = 'Paid'");
$stmt->execute([$currentMonth]);
$totalCollected = $stmt->fetchColumn() ?: 0;

// 2. Total Pending Amount
$stmt = $conn->prepare("SELECT SUM(amount_paid) FROM fees WHERE month_for = ? AND status = 'Pending'");
$stmt->execute([$currentMonth]);
$totalRemaining = $stmt->fetchColumn() ?: 0;

// 3. Count Students (Using enrollment instead of id to avoid error)
$stmt = $conn->prepare("SELECT COUNT(student_enrollment) FROM fees WHERE month_for = ? AND status = 'Paid'");
$stmt->execute([$currentMonth]);
$paidCount = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(student_enrollment) FROM fees WHERE month_for = ? AND status = 'Pending'");
$stmt->execute([$currentMonth]);
$pendingCount = $stmt->fetchColumn();

// 4. Wing-wise Breakdown (Fixed f.id to f.student_enrollment)
$query = "SELECT s.stu_wing, SUM(f.amount_paid) as wing_total, COUNT(f.student_enrollment) as student_count 
          FROM fees f 
          JOIN students s ON f.student_enrollment = s.stu_enrollment_number 
          WHERE f.month_for = ? AND f.status = 'Paid'
          GROUP BY s.stu_wing";
$stmt = $conn->prepare($query);
$stmt->execute([$currentMonth]);
$wingReport = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Revenue Report | <?php echo $currentMonth; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --primary: #00d4ff; --glass: rgba(255, 255, 255, 0.05); }
        body { 
            background: linear-gradient(rgba(0,0,0,0.9), rgba(0,0,0,0.9)), url('./assets/img/bg_6.png') no-repeat center center fixed;
            background-size: cover; font-family: 'Poppins', sans-serif; color: white; padding: 40px; margin: 0;
        }
        .report-container { max-width: 1000px; margin: 0 auto; background: var(--glass); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); border-radius: 30px; padding: 40px; }
        .stat-card { background: rgba(255,255,255,0.03); padding: 20px; border-radius: 20px; text-align: center; border: 1px solid rgba(255,255,255,0.1); }
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 30px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; padding: 15px; color: var(--primary); border-bottom: 1px solid rgba(255,255,255,0.1); font-size: 13px; }
        td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .btn-print { background: var(--primary); color: black; border: none; padding: 15px 30px; border-radius: 15px; font-weight: 700; cursor: pointer; display: block; margin: 20px auto; }
        
        @media print {
            body { background: white !important; color: black !important; }
            .report-container { border: none; background: white; color: black; width: 100%; }
            .btn-print, .back-link { display: none; }
            th, .stat-card h4 { color: black !important; }
        }
    </style>
</head>
<body>

<div class="report-container">
    <a href="fee_dashboard.php" class="back-link" style="color:var(--primary); text-decoration:none;">&larr; Dashboard</a>
    
    <div style="text-align:center; margin-bottom:40px;">
        <h1 style="margin:0;">BAHRIA COLLEGE ISLAMABAD</h1>
        <p style="opacity:0.7;">Monthly Fee Revenue Summary - <?php echo $currentMonth; ?></p>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <h4>COLLECTED</h4>
            <div style="font-size:24px; color:var(--primary); font-weight:700;">Rs. <?php echo number_format($totalCollected); ?></div>
            <small><?php echo $paidCount; ?> Paid Invoices</small>
        </div>
        <div class="stat-card">
            <h4>OUTSTANDING</h4>
            <div style="font-size:24px; color:#ff4d4d; font-weight:700;">Rs. <?php echo number_format($totalRemaining); ?></div>
            <small><?php echo $pendingCount; ?> Unpaid</small>
        </div>
        <div class="stat-card">
            <h4>TOTAL EXPECTED</h4>
            <div style="font-size:24px; font-weight:700;">Rs. <?php echo number_format($totalCollected + $totalRemaining); ?></div>
            <small>Total Dues</small>
        </div>
    </div>

    <h3>Wing-wise Breakdown</h3>
    <table>
        <thead>
            <tr>
                <th>Wing</th>
                <th>Paid Count</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($wingReport as $wing): ?>
            <tr>
                <td><strong><?php echo $wing['stu_wing']; ?></strong></td>
                <td><?php echo $wing['student_count']; ?></td>
                <td>Rs. <?php echo number_format($wing['wing_total']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top:50px; text-align:right; font-size:12px; opacity:0.5;">
        Report Generated on: <?php echo date('d-M-Y H:i'); ?>
    </div>
</div>

<button class="btn-print" onclick="window.print()">Download PDF Report</button>

<script>lucide.createIcons();</script>
</body>
</html>