<?php
session_start();
if (!isset($_SESSION['teacher_id'])) { header('Location: index.php'); exit(); }

$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_DB";
$conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);

// Convert "Class VI, Class VII" into an array
$classes = explode(", ", $_SESSION['assigned_classes']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Portal | BCI</title>
    <style>
        /* Reuse your Glassmorphism CSS here */
        body { background: #0f0f0f; font-family: 'Poppins', sans-serif; color: white; display: flex; justify-content: center; padding: 50px; }
        .portal-container { width: 90%; max-width: 1000px; }
        .class-card { 
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); 
            padding: 30px; border-radius: 20px; margin-bottom: 20px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .btn { padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.8rem; }
        .btn-attendance { background: #00d4ff; color: #000; }
        .btn-marks { background: #ffcc00; color: #000; }
    </style>
</head>
<body>

<div class="portal-container">
    <h1>Welcome, Teacher</h1>
    <p>Assigned Classes Management</p>

    <?php foreach($classes as $c): ?>
    <div class="class-card">
        <h3><?php echo $c; ?></h3>
        <div style="display: flex; gap: 10px;">
            <a href="manage_attendance.php?class=<?php echo urlencode($c); ?>" class="btn btn-attendance">ATTENDANCE</a>
            <a href="manage_marks.php?class=<?php echo urlencode($c); ?>" class="btn btn-marks">MARK SHEET</a>
            <a href="generate_transcripts.php?class=<?php echo urlencode($c); ?>" class="btn" style="background: #00ff88; color: #000;">TRANSCRIPTS</a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

</body>
</html>