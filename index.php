<?php
session_start();

// Database Configuration
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

$status = "Please select your role and sign in.";
$status_type = "default";

if (!empty($_POST)) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role']; 

    // Dynamic SQL selection based on role
    if ($role == 'admin') {
        $sql = 'SELECT * FROM admin WHERE username = ?';
    } elseif ($role == 'teacher') {
        $sql = 'SELECT * FROM teachers WHERE teacher_id_official = ?';
    } else {
        // STUDENT LOGIN: Fetching data for the specific student logging in
        $sql = 'SELECT * FROM students WHERE stu_enrollment_number = ?';
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify credentials
    if ($user_data && $password == $user_data['password']) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        
        // --- ADMIN REDIRECT LOGIC ---
        if ($role == 'admin') {
            $_SESSION['user_type'] = $user_data['user_type']; 
            $redirect = ($user_data['user_type'] === 'fee_manager') ? 'fee_dashboard.php' : 'dashboard.php';
        }
        
        // --- TEACHER SESSION DATA ---
        if($role == 'teacher') {
            $_SESSION['t_id'] = $user_data['teacher_id'];
            $_SESSION['t_name'] = $user_data['teacher_name'];
            $_SESSION['t_wing'] = $user_data['teacher_wing'];
            $_SESSION['t_class'] = $user_data['teacher_class'];
            $redirect = 'teacher_dashboard.php';
        }
        
        // --- STUDENT SESSION DATA (DYNAMIC FIX) ---
        if($role == 'student') {
            $_SESSION['stu_name'] = $user_data['stu_name'];
            $_SESSION['stu_id'] = $user_data['stu_enrollment_number']; // This is critical for portal filtering
            $redirect = 'student_portal.php'; 
        }
        
        header("Location: $redirect");
        exit; 
    } else {
        $status = "Authentication failed for $role.";
        $status_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bahria College Islamabad | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #00d4ff;
            --accent: #ffcc00;
            --glass: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.15);
        }

        body {
            background: linear-gradient(rgba(0,0,0,0.75), rgba(0,0,0,0.75)), url('./assets/img/bg_6.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: white;
            overflow: hidden;
        }

        body::before, body::after {
            content: "";
            position: absolute;
            width: 350px;
            height: 350px;
            background: var(--primary);
            filter: blur(150px);
            border-radius: 50%;
            z-index: -1;
            opacity: 0.4;
        }
        body::before { top: 10%; left: 10%; animation: float 15s infinite alternate; }
        body::after { bottom: 10%; right: 10%; background: #5028ff; animation: float 20s infinite alternate-reverse; }

        @keyframes float {
            from { transform: translate(0,0); }
            to { transform: translate(150px, 100px); }
        }

        .login-card {
            background: var(--glass);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 20px rgba(0, 212, 255, 0.1);
            text-align: center;
            animation: zoomIn 0.8s cubic-bezier(0.17, 0.67, 0.83, 0.67);
        }

        @keyframes zoomIn {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }

        .logo {
            width: 110px;
            margin-bottom: 10px;
            filter: drop-shadow(0 0 15px var(--primary));
        }

        h1 { font-size: 1.6rem; margin: 0; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
        h2 { font-size: 0.85rem; margin: 0 0 25px; font-weight: 300; color: var(--primary); letter-spacing: 5px; text-transform: uppercase; }

        #login-status {
            font-size: 11px;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 12px;
            background: rgba(0,0,0,0.3);
            border-left: 4px solid var(--primary);
            text-transform: uppercase;
        }
        .status-error { border-left-color: #ff4d4d !important; color: #ff4d4d; }

        .role-selector {
            display: flex;
            background: rgba(0,0,0,0.4);
            padding: 6px;
            border-radius: 15px;
            margin-bottom: 25px;
            border: 1px solid var(--glass-border);
        }

        .role-selector label {
            flex: 1;
            padding: 12px;
            cursor: pointer;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            transition: 0.4s;
            color: #888;
        }

        .role-selector input { display: none; }

        .role-selector input:checked + label {
            background: var(--primary);
            color: #000;
            box-shadow: 0 5px 15px rgba(0, 212, 255, 0.4);
        }

        .input-group { margin-bottom: 18px; text-align: left; }
        .input-group label { display: block; font-size: 10px; color: var(--primary); margin-bottom: 6px; text-transform: uppercase; margin-left: 8px; font-weight: 600; }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 15px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: white;
            box-sizing: border-box;
            transition: 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255,255,255,0.1);
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.2);
        }

        .btn-primary {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: #000;
            border: none;
            border-radius: 12px;
            font-weight: 800;
            cursor: pointer;
            text-transform: uppercase;
            margin-top: 10px;
            letter-spacing: 1px;
            transition: 0.4s;
        }

        .btn-primary:hover {
            background: #fff;
            transform: translateY(-4px);
            box-shadow: 0 12px 25px rgba(0, 212, 255, 0.4);
        }

        footer { margin-top: 30px; font-size: 10px; color: #555; }
    </style>
</head>
<body>

    <div class="login-card">
        <img src="./assets/img/logo.png" alt="BCI Logo" class="logo" onerror="this.src='https://via.placeholder.com/100?text=BCI+LOGO'">
        <h1>BAHRIA COLLEGE</h1>
        <h2>Islamabad</h2>

        <div id="login-status" class="<?php echo ($status_type == 'error') ? 'status-error' : ''; ?>">
            WELCOME: <?php echo $status; ?>
        </div>

        <form action="index.php" method="post">
            <div class="role-selector">
                <input type="radio" name="role" id="admin" value="admin" checked onclick="toggleLabel('Username')">
                <label for="admin">Admin / Staff</label>

                <input type="radio" name="role" id="teacher" value="teacher" onclick="toggleLabel('Official Teacher ID')">
                <label for="teacher">Teacher</label>

                <input type="radio" name="role" id="student" value="student" onclick="toggleLabel('Enrollment Number')">
                <label for="student">Student</label>
            </div>

            <div class="input-group">
                <label id="userLabel">Username</label>
                <input type="text" name="username" id="usernameInput" placeholder="Enter Username..." required>
            </div>
            
            <div class="input-group">
                <label>Security Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn-primary">Authenticate & Enter</button>

            <footer>
                &copy; 2026 Bahria College Islamabad | Management System
            </footer>
        </form>
    </div>

    <script>
        function toggleLabel(text) {
            document.getElementById('userLabel').innerText = text;
            document.getElementById('usernameInput').placeholder = "Enter " + text + "...";
            
            const input = document.getElementById('usernameInput');
            input.style.transform = 'scale(0.98)';
            setTimeout(() => { input.style.transform = 'scale(1)'; }, 100);
        }
    </script>
</body>
</html>