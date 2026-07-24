<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'fee_manager') {
    header('Location: index.php'); exit();
}

$host = "localhost"; $user = "root"; $pass = ""; $db = "students_DB";
$conn = new mysqli($host, $user, $pass, $db);

$student = null;
$msg = "";
$msg_type = "";

if (isset($_GET['search_id'])) {
    $id = $conn->real_escape_string($_GET['search_id']);
    $res = $conn->query("SELECT * FROM students WHERE stu_enrollment_number = '$id'");
    
    if ($res && $res->num_rows > 0) {
        $student = $res->fetch_assoc();
        
        // Image_8d98fc ke mutabiq fees table se latest amount uthana
        $fee_res = $conn->query("SELECT amount_paid FROM fees WHERE student_enrollment = '$id' ORDER BY fee_id DESC LIMIT 1");
        $fee_data = $fee_res->fetch_assoc();
        $student['display_fee'] = $fee_data['amount_paid'] ?? 0;
    } else {
        $msg = "Enrollment ID $id not found!";
        $msg_type = "error";
    }
}

if (isset($_POST['update_all'])) {
    $id = $_POST['enroll_id'];
    $n_fee = $_POST['n_fee'];
    $n_disc = $_POST['n_disc'];
    $n_adj_t = $_POST['n_adj_t'];
    $n_adj_a = $_POST['n_adj_a'];

    // Update Students Table (Scholarship & Fines)
    // Note: Is query mein columns ke naam wahi hain jo aapne bataye
    $upd_stu = "UPDATE students SET 
                discount_percentage = '$n_disc', 
                adj_type = '$n_adj_t', 
                amount = '$n_adj_a' 
                WHERE stu_enrollment_number = '$id'";
    
    // Update Fees Table (amount_paid)
    $upd_fee = "UPDATE fees SET amount_paid = '$n_fee' 
                WHERE s _enrollment = '$id' AND status = 'Pending'";

    if ($conn->query($upd_stu) && $conn->query($upd_fee)) {
        $msg = "Record Updated Successfully!";
        $msg_type = "success";
        // Refresh page data
        header("Location: fee_correction.php?search_id=$id&msg=success");
        exit();
    } else {
        $msg = "Database Error: " . $conn->error;
        $msg_type = "error";
    }
}

if(isset($_GET['msg']) && $_GET['msg'] == 'success') {
    $msg = "Record Updated Successfully!"; $msg_type = "success";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fee Correction | BCI</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap');
        body { background: #020617; color: white; font-family: 'Plus Jakarta Sans', sans-serif; padding: 20px; }
        .container { max-width: 900px; margin: auto; }
        .panel { background: #0f172a; padding: 25px; border-radius: 20px; border: 1px solid #1e293b; }
        .input-box { background: #000; border: 1px solid #334155; color: white; padding: 12px; border-radius: 10px; width: 100%; box-sizing: border-box; margin-top: 5px; }
        .val-view { background: rgba(255,255,255,0.03); padding: 12px; border-radius: 12px; margin-bottom: 10px; border-left: 4px solid #00d4ff; }
        .btn-update { width: 100%; background: #00d4ff; color: #000; border: none; padding: 15px; border-radius: 12px; font-weight: 800; cursor: pointer; margin-top: 20px; }
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .success { background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid #10b981; }
        .error { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; }
    </style>
</head>
<body>

<div class="container">
    <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
        <h3>Correction Center</h3>
        <a href="fee_dashboard.php" style="color:#64748b; text-decoration:none;">Dashboard</a>
    </div>

    <form method="GET" style="display:flex; gap:10px; margin-bottom:20px;">
        <input type="text" name="search_id" class="input-box" style="margin:0;" placeholder="Enter ID (e.g. 12305)" value="<?php echo $_GET['search_id'] ?? ''; ?>">
        <button type="submit" class="btn-update" style="width:auto; margin:0; padding:0 25px;">Search</button>
    </form>

    <?php if($msg): ?> <div class="alert <?php echo $msg_type; ?>"><?php echo $msg; ?></div> <?php endif; ?>

    <?php if($student): ?>
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
        <div class="panel">
            <h4 style="margin:0 0 15px 0;">Current Data</h4>
            <div class="val-view">
                <small style="color:#64748b;">STUDENT NAME</small>
                <div style="font-size:18px; font-weight:700;"><?php echo $student['stu_name']; ?></div>
            </div>
            <div class="val-view">
                <small style="color:#64748b;">MONTHLY FEE (amount_paid)</small>
                <div>Rs. <?php echo number_format($student['display_fee']); ?></div>
            </div>
            <div class="val-view" style="border-color:#a855f7;">
                <small style="color:#64748b;">SCHOLARSHIP (%)</small>
                <div><?php echo $student['discount_percentage'] ?? '0'; ?>%</div>
            </div>
            <div class="val-view" style="border-color:#ff9f43;">
                <small style="color:#64748b;">FINE TYPE</small>
                <div><?php echo $student['adj_type'] ?? 'No Fine'; ?></div>
            </div>
        </div>

        <div class="panel" style="border-color: #00d4ff44;">
            <h4 style="margin:0 0 15px 0; color:#00d4ff;">Edit Values</h4>
            <form method="POST">
                <input type="hidden" name="enroll_id" value="<?php echo $student['stu_enrollment_number']; ?>">
                
                <label style="font-size:12px;">Monthly Fee (amount_paid)</label>
                <input type="number" name="n_fee" class="input-box" value="<?php echo $student['display_fee']; ?>">

                <label style="font-size:12px; display:block; margin-top:10px;">Scholarship Percentage</label>
                <input type="number" name="n_disc" class="input-box" value="<?php echo $student['discount_percentage'] ?? 0; ?>">

                <label style="font-size:12px; display:block; margin-top:10px;">Fine Type (Reason)</label>
                <input type="text" name="n_adj_t" class="input-box" value="<?php echo $student['adj_type'] ?? ''; ?>">

                <label style="font-size:12px; display:block; margin-top:10px;">Fine Amount (Rs.)</label>
                <input type="number" name="n_adj_a" class="input-box" value="<?php echo $student['amount'] ?? 0; ?>">

                <button type="submit" name="update_all" class="btn-update">APPLY CHANGES</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>lucide.createIcons();</script>
</body>
</html>