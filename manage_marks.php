<?php
session_start();
$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_DB";
$conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);

$target_class = $_GET['class']; // Passed from teacher_portal.php

if (isset($_POST['save_marks'])) {
    foreach ($_POST['marks'] as $enrollment => $data) {
        $stmt = $conn->prepare("INSERT INTO results (stu_enrollment_number, subject_name, term1_marks, term2_marks) 
                                VALUES (?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE term1_marks=?, term2_marks=?");
        $stmt->execute([
            $enrollment, $_POST['subject'], $data['t1'], $data['t2'], 
            $data['t1'], $data['t2']
        ]);
    }
    $msg = "Marks updated successfully!";
}

// Fetch students of this class
$stmt = $conn->prepare("SELECT * FROM students WHERE stu_class = ?");
$stmt->execute([$target_class]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Marks | <?php echo $target_class; ?></title>
    <style>
        /* Shared Styles */
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap");
        body { background: #0a0a0a; font-family: 'Poppins', sans-serif; color: white; padding: 40px; }
        .glass-panel { background: rgba(255,255,255,0.03); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.1); padding: 30px; border-radius: 20px; }
        input[type="number"] { background: rgba(0,0,0,0.5); border: 1px solid #444; color: white; padding: 8px; border-radius: 5px; width: 60px; }
        .save-btn { background: #ffcc00; color: black; padding: 15px 40px; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; float: right; margin-top: 20px; }
        .subject-input { background: rgba(255,255,255,0.1); border: 1px solid #00d4ff; color: white; padding: 10px; border-radius: 10px; margin-bottom: 20px; width: 300px; }
    </style>
</head>
<body>

<div class="glass-panel">
    <h2>Update Assessment Marks: <?php echo $target_class; ?></h2>
    <form method="POST">
        <label>Enter Subject Name: </label>
        <input type="text" name="subject" class="subject-input" placeholder="e.g. Mathematics" required>
        
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Enrollment</th>
                    <th>1st Term (Max 50)</th>
                    <th>2nd Term (Max 50)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($students as $s): ?>
                <tr>
                    <td><?php echo $s['stu_name']; ?></td>
                    <td><?php echo $s['enrollment_no']; ?></td>
                    <td><input type="number" name="marks[<?php echo $s['enrollment_no']; ?>][t1]" max="50"></td>
                    <td><input type="number" name="marks[<?php echo $s['enrollment_no']; ?>][t2]" max="50"></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" name="save_marks" class="save-btn">Upload to Students Portal</button>
    </form>
</div>

</body>
</html>