<?php
session_start();
if (!isset($_SESSION['username'])) { header('Location: index.php'); exit(); }

$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_DB";
$conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);

$enroll_id = isset($_GET['enroll_id']) ? $_GET['enroll_id'] : '';
$current_month = date('F');
$current_year = date('Y');

// Query: Check if the student exists and if they have a fee record for this month
$sql = "SELECT s.stu_name, f.fee_id, f.status 
        FROM students s 
        LEFT JOIN fees f ON s.stu_enrollment_number = f.student_enrollment 
        AND f.month_for = ? 
        WHERE s.stu_enrollment_number = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$current_month, $enroll_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Result | BCI</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { background: #060606; color: white; font-family: 'Poppins', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .result-card { background: rgba(255,255,255,0.05); padding: 40px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); text-align: center; max-width: 400px; width: 90%; }
        .btn-dl { display: block; background: #00d4ff; color: black; padding: 15px; border-radius: 12px; text-decoration: none; font-weight: bold; margin-top: 20px; }
        .btn-back { color: #888; text-decoration: none; font-size: 14px; margin-top: 20px; display: inline-block; }
    </style>
</head>
<body>

<div class="result-card">
    <?php if (!$result): ?>
        <i data-lucide="user-x" size="48" style="color: #ff4d4d;"></i>
        <h3>Student Not Found</h3>
        <p>No student exists with ID: <?php echo htmlspecialchars($enroll_id); ?></p>
    <?php elseif (!$result['fee_id']): ?>
        <i data-lucide="file-warning" size="48" style="color: #ffcc00;"></i>
        <h3>No Invoice Generated</h3>
        <p>Student <strong><?php echo $result['stu_name']; ?></strong> exists, but no fee record found for <?php echo $current_month; ?>.</p>
    <?php else: ?>
        <i data-lucide="check-circle" size="48" style="color: #00ff88;"></i>
        <h3>Invoice Found!</h3>
        <p>Student: <strong><?php echo $result['stu_name']; ?></strong><br>Status: <?php echo $result['status']; ?></p>
        <a href="download_challan.php?challan_id=<?php echo $result['fee_id']; ?>" class="btn-dl">DOWNLOAD CHALLAN</a>
    <?php endif; ?>
    
    <a href="fee_dashboard.php" class="btn-back">← Back to Dashboard</a>
</div>

<script>lucide.createIcons();</script>
</body>
</html>