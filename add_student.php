<?php
session_start();

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

$error = "";

if (isset($_POST['submit'])) {
    try {
        $enrollment = $_POST['enrollment'];

        $checkSql = "SELECT COUNT(*) FROM students WHERE stu_enrollment_number = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([$enrollment]);
        
        if ($checkStmt->fetchColumn() > 0) {
            $error = "Enrollment Number <b>$enrollment</b> is already registered!";
        } else {
            $target_dir = "uploads/students/";
            if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

            $file_name = "default.png"; 
            if (!empty($_FILES["stu_photo"]["name"])) {
                $file_ext = pathinfo($_FILES["stu_photo"]["name"], PATHINFO_EXTENSION);
                $file_name = $enrollment . "_" . time() . "." . $file_ext;
                $target_file = $target_dir . $file_name;
                move_uploaded_file($_FILES["stu_photo"]["tmp_name"], $target_file);
            }

            $sql = "INSERT INTO students (
                stu_enrollment_number, stu_name, stu_class, stu_email, stu_contact, stu_cnic, 
                stu_wing, fee_category, stu_religion, blood_group, date_of_birth,
                stu_father_name, father_cnic, father_occupation, father_contact,
                mother_name, mother_cnic, mother_occupation, mother_contact,
                residential_address, password, stu_photo, stu_attendance, stu_fee_status
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0,'Not Paid')";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $enrollment, $_POST['name'], $_POST['class'], $_POST['email'], 
                $_POST['contact'], $_POST['cnic'], $_POST['wing'], $_POST['fee_category'],
                $_POST['religion'], $_POST['blood'], $_POST['dob'],
                $_POST['f_name'], $_POST['f_cnic'], $_POST['f_occ'], $_POST['f_contact'],
                $_POST['m_name'], $_POST['m_cnic'], $_POST['m_occ'], $_POST['m_contact'],
                $_POST['address'], $_POST['pass'], $file_name
            ]);

            header('Location: dashboard.php?msg=success');
            exit();
        }
    } catch(PDOException $e) { 
        $error = "Database Error: " . $e->getMessage(); 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student | BCI SMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --primary: #00d4ff; --accent: #ffcc00; --danger: #ff4d4d; --glass: rgba(0, 0, 0, 0.9); }
        body {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('./assets/img/bg_6.png') no-repeat center center fixed;
            background-size: cover; font-family: 'Poppins', sans-serif; color: white; margin: 0; padding: 20px;
        }

        /* Top Navigation Bar */
        .nav-bar {
            max-width: 1100px; margin: 0 auto 20px; display: flex; align-items: center;
            background: #000; padding: 15px 25px; border-radius: 15px; border: 1px solid var(--primary);
        }
        .back-link { text-decoration: none; color: var(--primary); font-weight: 600; display: flex; align-items: center; gap: 10px; }

        .form-container {
            max-width: 1100px; margin: 0 auto; background: var(--glass);
            backdrop-filter: blur(15px); padding: 40px; border-radius: 25px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .header-title { text-align: center; border-bottom: 2px solid var(--primary); margin-bottom: 30px; padding-bottom: 10px; }
        .section-label { color: var(--accent); font-size: 13px; text-transform: uppercase; margin: 35px 0 15px; display: flex; align-items: center; letter-spacing: 2px; }
        .section-label::after { content: ""; flex: 1; height: 1px; background: rgba(255,255,255,0.1); margin-left: 15px; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .full-row { grid-column: span 3; }
        .input-group label { display: block; font-size: 11px; color: #aaa; margin-bottom: 6px; text-transform: uppercase; }
        
        input, select, textarea {
            width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #444;
            background: rgba(0,0,0,0.5); color: white; font-size: 14px; box-sizing: border-box;
        }

        /* FIX: Visible Black Option Bar */
        select option {
            background-color: #1a1a1a !important;
            color: white !important;
            padding: 10px;
        }

        input:focus, select:focus { border-color: var(--primary); outline: none; }
        .error-alert { background: rgba(255, 77, 77, 0.2); border: 1px solid var(--danger); color: var(--danger); padding: 15px; border-radius: 12px; margin-bottom: 25px; text-align: center; }
        .btn-save { background: var(--primary); color: #000; padding: 18px; font-weight: 700; border: none; border-radius: 12px; cursor: pointer; width: 100%; margin-top: 40px; font-size: 16px; text-transform: uppercase; }
    </style>
</head>
<body>

<div class="nav-bar">
    <a href="dashboard.php" class="back-link"><i data-lucide="arrow-left"></i> RETURN TO DASHBOARD</a>
</div>

<div class="form-container">
    <div class="header-title">
        <h1 style="margin:0;">STUDENT <span style="color:var(--primary);">ENROLLMENT</span></h1>
        <p style="color:#888; font-size: 12px;">BAHRIA COLLEGE ISLAMABAD - OFFICIAL RECORD FILING</p>
    </div>

    <?php if($error): ?>
        <div class="error-alert"> <?php echo $error; ?> </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="section-label">1. Student Academic Profile</div>
        <div class="grid">
            <div class="input-group">
                <label>Student Image</label>
                <input type="file" name="stu_photo" accept="image/*">
            </div>
            <div class="input-group">
                <label>Enrollment Number</label>
                <input type="text" name="enrollment" required>
            </div>
            <div class="input-group">
                <label>Student Full Name</label>
                <input type="text" name="name" required>
            </div>
            
            <div class="input-group">
                <label>Assigned Wing</label>
                <select name="wing" id="wingSelect" onchange="updateClasses()" required>
                    <option value="">-- Select Wing --</option>
                    <option value="Lower Primary">Lower Primary</option>
                    <option value="Upper Primary">Upper Primary</option>
                    <option value="Boys">Boys</option>
                    <option value="Girls">Girls</option>
                    <option value="Cambridge">Cambridge</option>
                    <option value="Special Education">Special Education</option>
                </select>
            </div>

            <div class="input-group">
                <label>Fee Category</label>
                <select name="fee_category" required>
                    <option value="">-- Select Category --</option>
                    <option value="civ">CIV</option>
                    <option value="pn-civ">PN CIV</option>
                    <option value="pn-sailor">PN SAILOR</option>
                    <option value="army">ARMY</option>
                    <option value="staff">STAFF</option>
                    <option value="pnet">PNET</option>
                    <option value="paf">PAF</option>
                    <option value="spd">SPD</option>
                    <option value="pn">PN</option>
                    <option value="F">Faculty</option>
                </select>
            </div>

            <div class="input-group">
                <label>Class / Grade</label>
                <select name="class" id="classSelect" required>
                    <option value="">Select Wing First</option>
                </select>
            </div>
        </div>

        <div class="section-label">2. Personal Information</div>
        <div class="grid">
            <div class="input-group"><label>Email</label><input type="email" name="email"></div>
            <div class="input-group"><label>Contact No</label><input type="text" name="contact"></div>
            <div class="input-group"><label>CNIC / B-Form</label><input type="text" name="cnic" required></div>
            <div class="input-group"><label>Date of Birth</label><input type="date" name="dob"></div>
            <div class="input-group"><label>Religion</label><input type="text" name="religion" value="Islam"></div>
            <div class="input-group"><label>Blood Group</label><input type="text" name="blood"></div>
        </div>

        <div class="section-label">3. Parental Record</div>
        <div class="grid">
            <div class="input-group"><label>Father's Name</label><input type="text" name="f_name" required></div>
            <div class="input-group"><label>Father's CNIC</label><input type="text" name="f_cnic"></div>
            <div class="input-group"><label>Father's Contact</label><input type="text" name="f_contact" required></div>
            <div class="input-group full-row"><label>Father's Occupation</label><input type="text" name="f_occ"></div>
            
            <div class="input-group"><label>Mother's Name</label><input type="text" name="m_name"></div>
            <div class="input-group"><label>Mother's CNIC</label><input type="text" name="m_cnic"></div>
            <div class="input-group"><label>Mother's Contact</label><input type="text" name="m_contact"></div>
            <div class="input-group full-row"><label>Mother's Occupation</label><input type="text" name="m_occ"></div>
        </div>

        <div class="section-label">4. System Security & Address</div>
        <div class="grid">
            <div class="input-group full-row">
                <label>Portal Access Password</label>
                <input type="text" name="pass" required>
            </div>
            <div class="input-group full-row">
                <label>Permanent Residential Address</label>
                <textarea name="address" rows="3"></textarea>
            </div>
        </div>

        <button type="submit" name="submit" class="btn-save">Finalize Registration & Sync Fee</button>
    </form>
</div>

<script>
function updateClasses() {
    const wingSelect = document.getElementById('wingSelect');
    const classSelect = document.getElementById('classSelect');
    const selectedWing = wingSelect.value;
    classSelect.innerHTML = '<option value="">-- Select Class --</option>';
    const classData = {
        "Lower Primary": ["Beginners", "Advance", "Prep", "Class 1", "Class 2"],
        "Upper Primary": ["Class 3", "Class 4", "Class 5"],
        "Boys": ["VI", "VII", "VIII", "IX", "X", "XI", "XII"],
        "Girls": ["VI", "VII", "VIII", "IX", "X", "XI", "XII"],
        "Cambridge": ["P-1", "P-2", "P-3", "Senior-1", "Senior-2", "Senior-3", "AS", "AL"],
        "Special Education": ["Class 1", "Class 2", "Class 3", "Class 4", "Class 5"]
    };
    if (selectedWing && classData[selectedWing]) {
        classData[selectedWing].forEach(className => {
            const option = document.createElement('option');
            option.value = className; option.textContent = className;
            classSelect.appendChild(option);
        });
    }
}
lucide.createIcons();
</script>
</body>
</html>