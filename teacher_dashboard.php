<?php
session_start();
// Redirect if not logged in as teacher
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') { 
    header('Location: index.php'); exit(); 
}

// --- DATABASE CONNECTION TO FETCH PHOTO FROM DB ---
$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_DB";
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch photo using the official ID stored in session
    $stmt = $conn->prepare("SELECT teacher_photo FROM teachers WHERE teacher_id_official = ?");
    $stmt->execute([$_SESSION['username']]); 
    $teacher_db = $stmt->fetch(PDO::FETCH_ASSOC);
    $photo_from_db = $teacher_db['teacher_photo'] ?? '';
} catch(PDOException $e) { 
    $photo_from_db = ''; 
}

$assigned_string = $_SESSION['t_class'] ?? ''; 
$my_classes = array_filter(explode(", ", $assigned_string));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Portal | Dashboard</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap");

        :root {
            --primary: #00d4ff;
            --secondary: #00ff88;
            --bg: #050505;
            --card-bg: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.1);
        }

        body {
            background: var(--bg);
            color: white;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 40px;
            min-height: 100vh;
            background: radial-gradient(circle at top right, #001a1a, transparent),
                        radial-gradient(circle at bottom left, #1a001a, transparent);
            overflow-x: hidden;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://www.transparenttextures.com/patterns/carbon-fibre.png');
            opacity: 0.1;
            z-index: -1;
        }

        /* PHOTO STYLING AMENDMENT */
        .profile-header {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 15px;
        }

        .teacher-avatar {
            width: 100px;
            height: 100px;
            border-radius: 25px;
            object-fit: cover;
            border: 3px solid var(--primary);
            box-shadow: 0 0 25px rgba(0, 212, 255, 0.3);
            background: #111;
        }

        .welcome-section {
            max-width: 1100px;
            margin: 0 auto 40px;
            animation: slideDown 0.8s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 {
            font-size: 2.5rem;
            margin: 0;
            background: linear-gradient(to right, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
        }

        .role-badge {
            display: inline-block;
            background: rgba(0, 212, 255, 0.1);
            color: var(--primary);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            border: 1px solid var(--primary);
            margin-bottom: 8px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
            max-width: 1100px;
            margin: auto;
        }

        .card { 
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            padding: 35px;
            border-radius: 24px;
            border: 1px solid var(--border);
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .card:hover { 
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.06);
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .card::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            opacity: 0;
            transition: 0.3s;
        }

        .card:hover::before { opacity: 1; }

        .class-icon {
            width: 60px;
            height: 60px;
            background: rgba(0, 212, 255, 0.1);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--primary);
        }

        .class-title {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: white;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .action-btns {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .action-btn { 
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: var(--primary);
            color: #000;
            padding: 14px; 
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            transition: 0.3s;
            border: none;
        }

        .action-btn:hover {
            background: #fff;
            transform: scale(1.02);
        }

        .marks-btn { 
            background: transparent;
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
        }

        .marks-btn:hover {
            background: rgba(255,255,255,0.05);
            border-color: var(--secondary);
            color: var(--secondary);
        }

        .logout-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: rgba(255, 77, 77, 0.1);
            color: #ff4d4d;
            padding: 12px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid rgba(255, 77, 77, 0.3);
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 100;
        }

        .logout-btn:hover {
            background: #ff4d4d;
            color: white;
        }
    </style>
</head>
<body>

    <div class="welcome-section">
        <div class="profile-header">
            <?php 
                // Database link logic for image
                $img_path = "uploads/teachers/" . $photo_from_db;
                $display_img = (!empty($photo_from_db) && file_exists($img_path)) ? $img_path : "assets/img/default_user.png";
            ?>
            <img src="<?php echo $display_img; ?>?v=<?php echo time(); ?>" class="teacher-avatar" alt="Profile">
            <div>
                <div class="role-badge">Faculty Portal</div>
                <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['t_name'] ?? 'Teacher'); ?></h1>
                <p style="color: #888; margin: 5px 0 0 0;">Manage your assigned classes and student records.</p>
            </div>
        </div>
    </div>

    <div class="grid">
        <?php if(!empty($my_classes)): ?>
            <?php foreach($my_classes as $class): ?>
                <div class="card">
                    <div class="class-icon">
                        <i data-lucide="users" style="width: 30px; height: 30px;"></i>
                    </div>
                    <div class="class-title"><?php echo htmlspecialchars($class); ?></div>
                    
                    <div class="action-btns">
                        <a href="manage_attendance.php?class=<?php echo urlencode($class); ?>" class="action-btn">
                            <i data-lucide="calendar-check" style="width: 18px;"></i>
                            Daily Attendance
                        </a>
                        <a href="upload_marks.php?class=<?php echo urlencode($class); ?>" class="action-btn marks-btn">
                            <i data-lucide="file-text" style="width: 18px;"></i>
                            Upload Marks
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card" style="grid-column: 1/-1;">
                <i data-lucide="alert-circle" style="color: #ff4d4d; width: 40px; height: 40px; margin-bottom: 15px; margin: 0 auto;"></i>
                <p>No classes assigned to your profile yet.<br><span style="color: #666;">Please contact the administration office.</span></p>
            </div>
        <?php endif; ?>
    </div>

    <a href="logout.php" class="logout-btn">
        <i data-lucide="log-out" style="width: 18px;"></i>
        Logout
    </a>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();
    </script>
</body>
</html>