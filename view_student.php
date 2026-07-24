<?php
session_start();
if (!isset($_SESSION['username'])) { header('Location: index.php'); exit(); }

$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_db";
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Connection failed: " . $e->getMessage()); }

$id = isset($_GET['id']) ? $_GET['id'] : '';
$stmt = $conn->prepare("SELECT * FROM students WHERE stu_enrollment_number = ?");
$stmt->execute([$id]);
$s = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$s) { die("Student record not found."); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile | <?php echo htmlspecialchars($s['stu_name']); ?></title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --primary: #00d4ff; --accent: #ffcc00; --bg: #0f0f0f; --card: #1a1a1a; --danger: #ff4444; --success: #00c851; }
        body { background: var(--bg); font-family: 'Segoe UI', sans-serif; color: white; padding: 20px; margin: 0; }
        
        .top-nav {
            max-width: 900px; margin: 0 auto 20px; display: flex; align-items: center;
            background: #000; padding: 15px 25px; border-radius: 12px; border: 1px solid var(--primary);
        }
        .back-link { text-decoration: none; color: var(--primary); font-weight: 600; display: flex; align-items: center; gap: 10px; font-size: 14px; }

        .profile-container { 
            max-width: 900px; margin: auto; background: var(--card); 
            padding: 40px; border-radius: 15px; border: 1px solid #333; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
        }

        .profile-header { display: flex; align-items: center; gap: 30px; margin-bottom: 30px; }
        .profile-pic { 
            width: 120px; height: 120px; border-radius: 15px; 
            object-fit: cover; border: 3px solid var(--accent);
            box-shadow: 0 0 20px rgba(255, 204, 0, 0.2);
        }
        .header-info h1 { margin: 0; color: var(--accent); font-size: 32px; text-transform: uppercase; }
        .header-info p { margin: 5px 0; color: #888; font-size: 14px; letter-spacing: 1px; }

        .grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        .section-header { 
            color: var(--primary); border-bottom: 1px solid #333; 
            padding-bottom: 8px; margin: 35px 0 15px; 
            text-transform: uppercase; font-size: 13px; font-weight: bold; letter-spacing: 2px; 
        }
        
        .box { background: #222; padding: 15px; border-radius: 8px; border-left: 3px solid transparent; transition: 0.3s; }
        .box:hover { border-left-color: var(--primary); background: #282828; }
        
        /* Dynamic Status Colors - MATCHED WITH DB SYNC */
        .status-paid { border-left: 4px solid var(--success) !important; color: var(--success) !important; font-weight: bold; }
        .status-unpaid { border-left: 4px solid var(--danger) !important; color: var(--danger) !important; font-weight: bold; animation: pulse 2s infinite; }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 68, 68, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(255, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 68, 68, 0); }
        }

        label { color: #666; font-size: 11px; display: block; margin-bottom: 5px; text-transform: uppercase; }
        span { font-size: 15px; color: #eee; font-weight: 500; }

        @media (max-width: 768px) { .grid { grid-template-columns: 1fr 1fr; } }
    </style>
</head>
<body>

<div class="top-nav">
    <a href="dashboard.php" class="back-link"><i data-lucide="arrow-left"></i> BACK TO DASHBOARD</a>
</div>

<div class="profile-container">
    <div class="profile-header">
        <?php 
            $photoPath = "uploads/students/" . ($s['stu_photo'] ? $s['stu_photo'] : 'default.png');
        ?>
        <img src="<?php echo $photoPath; ?>?v=<?php echo time(); ?>" 
             class="profile-pic" 
             onerror="this.src='uploads/students/default.png'">
             
        <div class="header-info">
            <h1><?php echo htmlspecialchars($s['stu_name']); ?></h1>
            <p>ENROLLMENT ID: <span style="color:var(--primary);"><?php echo htmlspecialchars($s['stu_enrollment_number']); ?></span></p>
            <p>WING: <?php echo htmlspecialchars($s['stu_wing']); ?></p>
        </div>
    </div>

    <div class="section-header">1. Academic & Personal Details</div>
    <div class="grid">
        <div class="box"><label>Current Class</label><span><?php echo htmlspecialchars($s['stu_class']); ?></span></div>
        
        <div class="box" style="border-left-color: var(--accent);">
            <label>Fee Category</label>
            <span style="color:var(--accent); text-transform: uppercase;"><?php echo htmlspecialchars($s['fee_category']); ?></span>
        </div>

        <div class="box"><label>Email Address</label><span><?php echo htmlspecialchars($s['stu_email']); ?></span></div>
        <div class="box"><label>Contact Number</label><span><?php echo htmlspecialchars($s['stu_contact']); ?></span></div>
        <div class="box"><label>CNIC / B-Form</label><span><?php echo htmlspecialchars($s['stu_cnic']); ?></span></div>
        <div class="box"><label>Date of Birth</label><span><?php echo htmlspecialchars($s['date_of_birth']); ?></span></div>
        <div class="box"><label>Blood Group</label><span style="color:#ff4444;"><?php echo htmlspecialchars($s['blood_group']); ?></span></div>
        <div class="box"><label>Religion</label><span><?php echo htmlspecialchars($s['stu_religion']); ?></span></div>
        <div class="box"><label>Attendance</label><span><?php echo htmlspecialchars($s['stu_attendance']); ?>%</span></div>
        
        <?php 
            // SYNCED LOGIC: Checks for 'Paid', everything else is 'status-unpaid'
            $currentStatus = trim($s['stu_fee_status']);
            $statusClass = ($currentStatus == 'Paid') ? 'status-paid' : 'status-unpaid';
        ?>
        <div class="box <?php echo $statusClass; ?>">
            <label>Current Month Fee Status</label>
            <span><?php echo htmlspecialchars($currentStatus); ?></span>
        </div>
    </div>

    <div class="section-header">2. Parent / Guardian Details</div>
    <div class="grid">
        <div class="box"><label>Father Name</label><span><?php echo htmlspecialchars($s['stu_father_name']); ?></span></div>
        <div class="box"><label>Father CNIC</label><span><?php echo htmlspecialchars($s['father_cnic']); ?></span></div>
        <div class="box"><label>Father Occupation</label><span><?php echo htmlspecialchars($s['father_occupation']); ?></span></div>
        <div class="box"><label>Mother Name</label><span><?php echo htmlspecialchars($s['mother_name']); ?></span></div>
        <div class="box"><label>Mother CNIC</label><span><?php echo htmlspecialchars($s['mother_cnic']); ?></span></div>
        <div class="box"><label>Mother Contact</label><span><?php echo htmlspecialchars($s['mother_contact']); ?></span></div>
    </div>

    <div class="section-header">3. Residential Information</div>
    <div class="box" style="width: 100%; box-sizing: border-box;">
        <label>Full Residential Address</label>
        <span><?php echo htmlspecialchars($s['residential_address']); ?></span>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
</body>
</html> 