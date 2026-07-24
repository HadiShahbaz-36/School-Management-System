<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

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

// Check if ID is provided
if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$id = $_GET['id'];

// Fetch teacher name to show who we are deleting
$stmt = $conn->prepare("SELECT teacher_name FROM teachers WHERE teacher_id = ?");
$stmt->execute([$id]);
$teacher_name = $stmt->fetchColumn();

// If teacher doesn't exist, go back
if (!$teacher_name) {
    header('Location: dashboard.php');
    exit();
}

if (isset($_POST['submit'])) {
    try {
        // Updated table name and column name
        $sql = 'DELETE FROM teachers WHERE teacher_id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        header('Location: dashboard.php?msg=Teacher Deleted Successfully');
        exit();
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Teacher Deletion | SMS</title>
    <link rel="icon" href="./assets/img/fav.png">
    <style>
        :root {
            --danger: #ff4d4d;
            --glass: rgba(255, 255, 255, 0.1);
            --border: rgba(255, 255, 255, 0.2);
        }

        body {
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('./assets/img/bg_6.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            color: white;
        }

        .delete-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 50px 40px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }

        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }

        .warning-icon {
            font-size: 60px;
            color: var(--danger);
            margin-bottom: 20px;
        }

        h2 {
            font-weight: 300;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .target-name {
            color: var(--danger);
            font-weight: bold;
            font-size: 20px;
            display: block;
            margin: 15px 0;
            background: rgba(255, 77, 77, 0.1);
            padding: 10px;
            border-radius: 8px;
        }

        p {
            color: #aaa;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .btn-group {
            display: flex;
            gap: 15px;
        }

        button {
            flex: 1;
            padding: 14px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .btn-confirm {
            background: var(--danger);
            color: white;
        }

        .btn-confirm:hover {
            background: #ff1a1a;
            box-shadow: 0 0 20px rgba(255, 77, 77, 0.4);
            transform: translateY(-2px);
        }

        .btn-cancel {
            background: transparent;
            color: white;
            border: 1px solid var(--border);
        }

        .btn-cancel:hover {
            background: rgba(255,255,255,0.1);
        }

        footer {
            position: absolute;
            bottom: 30px;
            font-size: 11px;
            color: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>

    <div class="delete-card">
        <div class="warning-icon">⚠️</div>
        <h2>Confirm Deletion</h2>
        <p>You are about to permanently remove the teacher:</p>
        
        <span class="target-name">
            <?php echo htmlspecialchars($teacher_name); ?>
        </span>

        <p>This action cannot be undone. All employment details, salary history, and personal records for this teacher will be lost from the system.</p>

        <form action="delete_teacher.php?id=<?php echo $id; ?>" method="post">
            <div class="btn-group">
                <button type="submit" name="submit" class="btn-confirm">Delete Forever</button>
                <button type="button" class="btn-cancel" onclick="window.location.href='dashboard.php'">Go Back</button>
            </div>
        </form>
    </div>

    <footer>
        &copy; 2026 HADI SHAHBAZ | SMS SECURITY LAYER
    </footer>

</body>
</html>