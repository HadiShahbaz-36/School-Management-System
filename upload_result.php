<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['result_file'])) {
    $enrollment = $_POST['enrollment'];
    $file = $_FILES['result_file'];
    
    // 1. Create uploads folder if it doesn't exist
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // 2. Validate file type
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($fileType != "pdf") {
        die("Error: Only PDF files are allowed.");
    }

    // 3. Create unique filename
    $fileName = "Result_" . $enrollment . "_" . time() . ".pdf";
    $targetPath = "uploads/" . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // 4. Update Database
        $host = "localhost"; $user = "root"; $password = ""; $dbname = "students_DB";
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
        
        $sql = "UPDATE students SET stu_result_pdf = ? WHERE stu_enrollment_number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$fileName, $enrollment]);
        
        header('Location: dashboard.php?upload=success');
        exit();
    } else {
        echo "File upload failed.";
    }
}
?>