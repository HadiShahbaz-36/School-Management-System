<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$dbname = "students_DB";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$status = "Welcome! Access your portal below.";
$status_type = "default";

if (!empty($_POST)) {
    $enrollment = $_POST['enrollment'];
    $pwd = $_POST['password'];

    $sql = 'SELECT * FROM students WHERE stu_enrollment_number = ? AND password = ?';
    $stmt = $conn->prepare($sql);
    $stmt->execute([$enrollment, $pwd]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $_SESSION['student_id'] = $student['stu_enrollment_number'];
        $_SESSION['student_name'] = $student['stu_name'];
        header('Location: student_portal.php');
        exit; 
    } else {
        $status = "Invalid Enrollment No. or Password.";
        $status_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal Login | SMS</title>
    <link rel="icon" href="./assets/img/fav.png">
    <style>
        :root {
            --primary: #00d4ff; /* Blue theme for Student Portal */
            --glass: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('./assets/img/bg_6.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: white;
            overflow: hidden;
        }

        .top-left-logo {
            position: absolute;
            top: 30px;
            left: 40px;
            width: 120px;
            transition: 0.5s ease;
        }

        .top-left-logo:hover { transform: scale(1.05); }

        /* Portal Header */
        .portal-header {
            position: absolute;
            top: 10%;
            text-align: center;
            width: 100%;
        }

        .portal-header h1 {
            font-weight: 300;
            letter-spacing: 5px;
            margin: 0;
            opacity: 0.8;
        }

        /* Glass Login Card */
        .login-card {
            background: var(--glass);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 50px 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 45px rgba(0,0,0,0.5);
            text-align: center;
            animation: slideIn 0.8s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        h2 {
            margin: 0 0 10px;
            font-weight: 300;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        #login-status {
            font-size: 13px;
            margin-bottom: 25px;
            padding: 8px;
            border-radius: 5px;
            background: rgba(0,0,0,0.2);
        }

        .status-error {
            color: #ff4d4d;
            border-left: 3px solid #ff4d4d;
        }

        /* Inputs */
        input {
            width: 100%;
            padding: 14px 15px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: white;
            font-size: 15px;
            box-sizing: border-box;
            margin-bottom: 20px;
            transition: 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255,255,255,0.1);
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.2);
        }

        /* Buttons */
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        button {
            padding: 14px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .btn-portal {
            background: var(--primary);
            color: #002b36;
        }

        .btn-portal:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 212, 255, 0.4);
        }

        .btn-admin {
            background: transparent;
            color: white;
            border: 1px solid var(--glass-border);
        }

        .btn-admin:hover {
            background: rgba(255,255,255,0.1);
        }

        footer {
            position: absolute;
            bottom: 30px;
            font-size: 11px;
            color: rgba(255,255,255,0.3);
            text-transform: uppercase;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>

    <img src="./assets/img/logo.png" alt="Logo" class="top-left-logo">

    <div class="portal-header">
        <h1>STUDENT PORTAL</h1>
    </div>

    <div class="login-card">
        <h2>Secure Login</h2>
        <div id="login-status" class="<?php echo ($status_type == 'error') ? 'status-error' : ''; ?>">
            <?php echo $status; ?>
        </div>

        <form action="student_login.php" method="post">
            <input type="text" name="enrollment" placeholder="Enrollment Number" required autocomplete="off">
            <input type="password" name="password" placeholder="Password" required>
            
            <div class="btn-group">
                <button type="submit" class="btn-portal">Enter Portal</button>
                <button type="button" class="btn-admin" onclick="window.location.href='index.php'">Admin Access</button>
            </div>
        </form>
    </div>

    <footer>
        &copy; 2025 HADI SHAHBAZ | STUDENT MANAGEMENT SYSTEM
    </footer>

</body>
</html>