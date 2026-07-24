<?php
session_start();
if (!isset($_SESSION['username'])) { header('Location: index.php'); exit(); }

$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_DB";
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
} catch(PDOException $e) { die("Connection failed: " . $e->getMessage()); }

if (!isset($_GET['id'])) { header('Location: dashboard.php'); exit(); }

// Fetch ALL fields for the teacher
$stmt = $conn->prepare("SELECT * FROM teachers WHERE teacher_id = ?");
$stmt->execute([$_GET['id']]);
$t = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$t) { die("Teacher record not found."); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Profile | <?php echo htmlspecialchars($t['teacher_name']); ?></title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap");
        
        body {
            background: linear-gradient(rgba(0,0,0,0.85), rgba(0,0,0,0.85)), url('./assets/img/bg_6.png');
            background-size: cover; background-attachment: fixed;
            font-family: 'Poppins', sans-serif; color: white; margin: 0; padding: 40px 0;
            display: flex; flex-direction: column; align-items: center;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 30px;
            width: 90%; max-width: 1100px; padding: 50px; box-shadow: 0 40px 60px rgba(0,0,0,0.5);
        }

        .header-box { text-align: center; margin-bottom: 40px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; position: relative;}
        .header-box h1 { font-size: 2.2rem; letter-spacing: 4px; margin: 0; font-weight: 700; color: #fff; }
        .header-box p { color: #00d4ff; font-size: 0.9rem; text-transform: uppercase; margin-top: 5px; letter-spacing: 2px; }

        /* Profile Photo Styling */
        .profile-photo-container {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
        }
        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #00d4ff;
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.4);
            background: #222;
        }

        .edit-badge {
            position: absolute; right: 0; top: 10px;
            background: #ffcc00; color: #000; padding: 8px 20px; 
            border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 0.8rem;
            transition: 0.3s;
        }
        .edit-badge:hover { background: #fff; transform: scale(1.05); }

        .form-section { 
            margin-bottom: 35px; background: rgba(0,0,0,0.25); padding: 25px; border-radius: 20px; 
            border: 1px solid rgba(255,255,255,0.05);
        }

        .section-tag { 
            background: #00d4ff; color: #000; padding: 6px 16px; font-size: 0.75rem; 
            font-weight: 800; border-radius: 6px; text-transform: uppercase; margin-bottom: 25px; display: inline-block;
        }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; }
        .field-group { display: flex; flex-direction: column; }
        label { font-size: 0.7rem; color: #00d4ff; text-transform: uppercase; margin-bottom: 8px; font-weight: 600; letter-spacing: 1px; }
        
        .info-box {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            padding: 14px; border-radius: 12px; color: #eee; font-size: 0.95rem; min-height: 20px;
        }

        .full-row { grid-column: 1 / -1; }
        
        .dashboard-link {
            margin-top: 40px; text-decoration: none; color: rgba(255,255,255,0.6); font-size: 0.9rem;
            display: flex; align-items: center; gap: 10px; transition: 0.3s;
            padding: 10px 20px; border-radius: 30px; border: 1px solid transparent;
        }
        .dashboard-link:hover { color: #00d4ff; border-color: #00d4ff44; background: rgba(0, 212, 255, 0.05); }
    </style>
</head>
<body>

<div class="form-container">
    <div class="header-box">
        <a href="edit_teacher.php?id=<?php echo $t['teacher_id']; ?>" class="edit-badge">EDIT PROFILE</a>
        
        <div class="profile-photo-container">
            <?php 
                // Check if photo column exists and file exists
                $photo_name = (isset($t['teacher_photo']) && !empty($t['teacher_photo'])) ? $t['teacher_photo'] : '';
                $img_path = "uploads/teachers/" . $photo_name;
                $display_img = (!empty($photo_name) && file_exists($img_path)) ? $img_path : "assets/img/default_user.png";
            ?>
            <img src="<?php echo $display_img; ?>?v=<?php echo time(); ?>" class="profile-photo" alt="Teacher Photo">
        </div>

        <h1>OFFICIAL PROFILE</h1>
        <p><?php echo htmlspecialchars($t['teacher_name']); ?> | <?php echo htmlspecialchars($t['teacher_id_official']); ?></p>
    </div>

    <div class="form-section">
        <span class="section-tag">1. Personal Details</span>
        <div class="grid">
            <div class="field-group"><label>Full Name</label><div class="info-box"><?php echo htmlspecialchars($t['teacher_name']); ?></div></div>
            <div class="field-group"><label>Gender</label><div class="info-box"><?php echo htmlspecialchars($t['gender']); ?></div></div>
            <div class="field-group"><label>Marital Status</label><div class="info-box"><?php echo htmlspecialchars($t['marital_status']); ?></div></div>
            <div class="field-group"><label>Relation Name</label><div class="info-box"><?php echo htmlspecialchars($t['relation_name']); ?></div></div>
            <div class="field-group"><label>Date of Birth</label><div class="info-box"><?php echo htmlspecialchars($t['dob']); ?></div></div>
            <div class="field-group"><label>CNIC Number</label><div class="info-box"><?php echo htmlspecialchars($t['cnic']); ?></div></div>
            <div class="field-group"><label>Contact Number</label><div class="info-box"><?php echo htmlspecialchars($t['contact_no']); ?></div></div>
            <div class="field-group"><label>Email Address</label><div class="info-box"><?php echo htmlspecialchars($t['teacher_email']); ?></div></div>
            <div class="field-group"><label>Emergency Contact</label><div class="info-box"><?php echo htmlspecialchars($t['emergency_contact'] ?? 'N/A'); ?></div></div>
            <div class="field-group full-row"><label>Permanent Address</label><div class="info-box"><?php echo htmlspecialchars($t['address']); ?></div></div>
        </div>
    </div>

    <div class="form-section">
        <span class="section-tag">2. Academic & Experience</span>
        <div class="grid">
            <div class="field-group"><label>Highest Degree</label><div class="info-box"><?php echo htmlspecialchars($t['highest_qualification'] ?? 'N/A'); ?></div></div>
            <div class="field-group"><label>University</label><div class="info-box"><?php echo htmlspecialchars($t['university'] ?? 'N/A'); ?></div></div>
            <div class="field-group"><label>Specialization</label><div class="info-box"><?php echo htmlspecialchars($t['subject_specialization'] ?? 'N/A'); ?></div></div>
            <div class="field-group"><label>Teaching Experience</label><div class="info-box"><?php echo htmlspecialchars($t['teaching_experience'] ?? '0'); ?> Years</div></div>
            <div class="field-group full-row"><label>Previous Institutes</label><div class="info-box"><?php echo nl2br(htmlspecialchars($t['prev_institutes'] ?? 'N/A')); ?></div></div>
        </div>
    </div>

    <div class="form-section">
        <span class="section-tag">3. Current Assignment</span>
        <div class="grid">
            <div class="field-group"><label>Designation</label><div class="info-box"><?php echo htmlspecialchars($t['designation']); ?></div></div>
            <div class="field-group"><label>Assigned Wing</label><div class="info-box"><?php echo htmlspecialchars($t['teacher_wing']); ?></div></div>
            <div class="field-group"><label>Assigned Classes</label><div class="info-box"><?php echo htmlspecialchars($t['teacher_class']); ?></div></div>
            <div class="field-group"><label>Date of Joining</label><div class="info-box"><?php echo htmlspecialchars($t['joining_date']); ?></div></div>
            <div class="field-group"><label>Employment Type</label><div class="info-box"><?php echo htmlspecialchars($t['employment_type'] ?? 'N/A'); ?></div></div>
            <div class="field-group"><label>Official ID</label><div class="info-box"><?php echo htmlspecialchars($t['teacher_id_official']); ?></div></div>
        </div>
    </div>

    <div class="form-section">
        <span class="section-tag">4. Payroll Information</span>
        <div class="grid">
            <div class="field-group"><label>Monthly Salary</label><div class="info-box">Rs. <?php echo number_format($t['salary_amount'] ?? 0); ?></div></div>
            <div class="field-group full-row"><label>Bank Details / IBAN</label><div class="info-box"><?php echo htmlspecialchars($t['bank_details'] ?? 'N/A'); ?></div></div>
        </div>
    </div>
</div>

<center>
    <a href="dashboard.php" class="dashboard-link">
        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
        Return to Dashboard
    </a>
</center>

</body>
</html>