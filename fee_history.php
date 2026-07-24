<?php
session_start();

// Database Configuration
$host = "localhost";
$user = "root";
$password = "";
$dbname = "students_db";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}

// FETCH DATA: Primary table 'students' ko rakha hai taake status 'Paid' lazmi milay
$query = "SELECT 
            s.stu_enrollment_number, 
            s.stu_name, 
            s.stu_class, 
            s.stu_wing, 
            s.stu_fee_status,
            f.month_for, 
            f.amount_paid, 
            f.payment_date,
            f.status as record_status
          FROM students s
          LEFT JOIN fees f ON s.stu_enrollment_number = f.student_enrollment
          WHERE s.stu_fee_status = 'Paid' OR f.status = 'Paid'
          ORDER BY f.payment_date DESC";

$stmt = $conn->prepare($query);
$stmt->execute();
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fee History | Correctly Linked</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --primary: #00d4ff; --glass: rgba(255, 255, 255, 0.05); }
        body { 
            background: linear-gradient(rgba(0,0,0,0.9), rgba(0,0,0,0.9)), url('./assets/img/bg_6.png') no-repeat center center fixed;
            background-size: cover; font-family: 'Poppins', sans-serif; color: white; padding: 40px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .history-card { background: var(--glass); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); border-radius: 25px; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 15px; color: var(--primary); text-transform: uppercase; font-size: 11px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 14px; }
        .status-paid { background: rgba(0, 255, 157, 0.1); color: #00ff9d; padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; border: 1px solid rgba(0,255,157,0.2); }
        .wing-badge { font-size: 10px; background: rgba(0, 212, 255, 0.1); color: var(--primary); padding: 2px 8px; border-radius: 4px; }
    </style>
</head>
<body>

    <div class="container">
        <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1><i data-lucide="verified" style="vertical-align:middle; margin-right:10px;"></i> Paid Students History</h1>
            <a href="fee_dashboard.php" style="color:white; text-decoration:none; opacity:0.7;">&larr; Exit</a>
        </div>

        <div class="history-card">
            <table>
                <thead>
                    <tr>
                        <th>Enrollment</th>
                        <th>Student Name</th>
                        <th>Wing / Class</th>
                        <th>Fee Status</th>
                        <th>Paid Amount</th>
                        <th>Month</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($history) > 0): ?>
                        <?php foreach ($history as $row): ?>
                        <tr>
                            <td><strong>#<?php echo $row['stu_enrollment_number']; ?></strong></td>
                            <td><?php echo htmlspecialchars($row['stu_name']); ?></td>
                            <td>
                                <span class="wing-badge"><?php echo $row['stu_wing']; ?></span>
                                <div style="font-size:11px; color:#888; margin-top:4px;"><?php echo $row['stu_class']; ?></div>
                            </td>
                            <td><span class="status-paid">PAID</span></td>
                            <td style="color:var(--primary); font-weight:700;">
                                Rs. <?php echo number_format($row['amount_paid'] ?? 0); ?>
                            </td>
                            <td><?php echo $row['month_for'] ?? 'N/A'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:50px; opacity:0.5;">No students marked as "Paid" in Database.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>