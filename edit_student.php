<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_db";
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Connection failed: " . $e->getMessage()); }

$id = isset($_GET['id']) ? $_GET['id'] : '';

// Fetch existing data
try {
    $sql = 'SELECT * FROM students WHERE stu_enrollment_number = ?';
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $student_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student_data) {
        die("Student record not found.");
    }
} catch(PDOException $e) { echo "Error: " . $e->getMessage(); }

if (isset($_POST['submit'])) {
    try {
        // --- PHOTO UPDATE LOGIC ---
        $file_name = $student_data['stu_photo']; 

        if (!empty($_FILES["stu_photo"]["name"])) {
            $target_dir = "uploads/students/";
            if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
            
            $file_ext = pathinfo($_FILES["stu_photo"]["name"], PATHINFO_EXTENSION);
            $file_name = $id . "_" . time() . "." . $file_ext; 
            $target_file = $target_dir . $file_name;
            
            if(move_uploaded_file($_FILES["stu_photo"]["tmp_name"], $target_file)) {
                if($student_data['stu_photo'] != 'default.png' && !empty($student_data['stu_photo']) && file_exists($target_dir . $student_data['stu_photo'])) {
                    unlink($target_dir . $student_data['stu_photo']);
                }
            }
        }

        // Logic sync: Humne yahan 'fee_status' field se value uthani hai
        $new_status = $_POST['fee_status']; 

        $sql = 'UPDATE students SET 
                stu_name = ?, stu_class = ?, stu_email = ?, stu_contact = ?, stu_cnic = ?, 
                stu_wing = ?, fee_category = ?, stu_religion = ?, blood_group = ?, date_of_birth = ?,
                stu_father_name = ?, father_cnic = ?, father_occupation = ?, father_contact = ?,
                mother_name = ?, mother_cnic = ?, mother_occupation = ?, mother_contact = ?,
                residential_address = ?, password = ?, stu_attendance = ?, stu_fee_status = ?,
                stu_photo = ?
                WHERE stu_enrollment_number = ?';
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $_POST['name'], $_POST['class'], $_POST['email'], $_POST['contact'], $_POST['cnic'],
            $_POST['wing'], $_POST['fee_category'], $_POST['religion'], $_POST['blood'], $_POST['dob'],
            $_POST['f_name'], $_POST['f_cnic'], $_POST['f_occ'], $_POST['f_contact'],
            $_POST['m_name'], $_POST['m_cnic'], $_POST['m_occ'], $_POST['m_contact'],
            $_POST['address'], $_POST['password'], $_POST['attendance'], $new_status, 
            $file_name, $id
        ]);
        header('Location: dashboard.php?msg=updated');
        exit();
    } catch(PDOException $e) { echo "Error: " . $e->getMessage(); }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Record | BCI SMS</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --primary: #00d4ff; --accent: #ffcc00; --glass: rgba(0, 0, 0, 0.9); }
        body {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('./assets/img/bg_6.png') no-repeat center center fixed;
            background-size: cover; font-family: 'Segoe UI', sans-serif; color: white; margin: 0; padding: 20px;
        }

        .top-bar {
            max-width: 1100px; margin: 0 auto 20px; display: flex; align-items: center;
            background: #000; padding: 15px 25px; border-radius: 15px; border: 1px solid var(--primary);
        }
        .back-link { text-decoration: none; color: var(--primary); font-weight: 600; display: flex; align-items: center; gap: 10px; }

        .form-container {
            max-width: 1100px; margin: 0 auto; background: var(--glass);
            backdrop-filter: blur(15px); padding: 40px; border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }
        .header-title { text-align: center; border-bottom: 2px solid var(--accent); margin-bottom: 30px; padding-bottom: 10px; }
        .section-label { color: var(--accent); font-size: 13px; text-transform: uppercase; margin: 30px 0 15px; display: flex; align-items: center; letter-spacing: 2px; }
        .section-label::after { content: ""; flex: 1; height: 1px; background: rgba(255,255,255,0.1); margin-left: 15px; }
        
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .full-row { grid-column: span 3; }
        
        .input-group label { display: block; font-size: 11px; color: #888; margin-bottom: 5px; text-transform: uppercase; }
        
        input, select, textarea {
            width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #444;
            background: rgba(0,0,0,0.6); color: white; font-size: 14px; box-sizing: border-box;
        }

        select option { background-color: #000 !important; color: #fff !important; }

        input[type="file"] { padding: 5px; border: 1px dashed var(--accent); }
        .btn-row { margin-top: 40px; display: flex; gap: 15px; }
        .btn-save { background: var(--accent); color: #000; flex: 2; padding: 15px; font-weight: bold; border: none; border-radius: 8px; cursor: pointer; }
        .btn-cancel { background: #333; color: white; flex: 1; padding: 15px; text-decoration: none; text-align: center; border-radius: 8px; }
        .btn-del { background: #880000; color: white; flex: 1; padding: 15px; border: none; border-radius: 8px; cursor: pointer; }
        
        .photo-box { text-align: center; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); }
        .current-photo { width: 100px; height: 100px; border-radius: 10px; object-fit: cover; border: 2px solid var(--accent); margin-bottom: 10px; }
    </style>
</head>
<body onload="updateClasses('<?php echo $student_data['stu_class']; ?>')">

<div class="top-bar">
    <a href="dashboard.php" class="back-link"><i data-lucide="arrow-left"></i> BACK TO STUDENT LIST</a>
</div>

<div class="form-container">
    <div class="header-title">
        <h1 style="margin:0; font-weight:300;">EDIT STUDENT <span style="color:var(--accent); font-weight:bold;">APPLICATION</span></h1>
        <p style="color:#aaa; font-size: 12px;">Modifying Record for ID: <?php echo htmlspecialchars($id); ?></p>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="section-label">1. Personal Details</div>
        <div class="grid">
            <div class="photo-box">
                <label style="display:block; font-size:10px; color:var(--accent); margin-bottom:10px;">STUDENT IDENTIFICATION PHOTO</label>
                <?php $imgSrc = "uploads/students/" . ($student_data['stu_photo'] ? $student_data['stu_photo'] : 'default.png'); ?>
                <img src="<?php echo $imgSrc; ?>?v=<?php echo time(); ?>" id="stu_preview" class="current-photo" onerror="this.src='uploads/students/default.png'">
                <input type="file" name="stu_photo" accept="image/*" onchange="previewImage(this)">
            </div>
            
            <div style="grid-column: span 2;">
                <div class="grid" style="grid-template-columns: 1fr 1fr; margin-top: 15px;">
                    <div><label>Enrollment (Locked)</label><input type="text" value="<?php echo $id; ?>" disabled></div>
                    <div><label>Full Name</label><input type="text" name="name" value="<?php echo htmlspecialchars($student_data['stu_name']); ?>" required></div>
                    <div>
                        <label>Assigned Wing</label>
                        <select name="wing" id="wingSelect" onchange="updateClasses()" required>
                            <?php 
                            $wings = ["Lower Primary", "Upper Primary", "Boys wing", "Girls wing", "Cambridge", "Special education"];
                            foreach($wings as $w) {
                                $sel = (trim($student_data['stu_wing']) == trim($w)) ? 'selected' : '';
                                echo "<option value='$w' $sel>$w</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label>Fee Category</label>
                        <select name="fee_category" required>
                            <?php
                            $cats = ['CIV', 'PN CIV', 'PN SAILOR', 'ARMY', 'STAFF', 'PNET', 'PAF', 'SPD', 'PN', 'Faculty'];
                            foreach($cats as $title) {
                                $sel = ($student_data['fee_category'] == $title) ? 'selected' : '';
                                echo "<option value='$title' $sel>$title</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div>
                <label>Class</label>
                <select name="class" id="classSelect" required></select>
            </div>
            <div><label>Email Address</label><input type="email" name="email" value="<?php echo htmlspecialchars($student_data['stu_email']); ?>"></div>
            <div><label>Primary Contact</label><input type="text" name="contact" value="<?php echo htmlspecialchars($student_data['stu_contact']); ?>"></div>
            <div><label>CNIC/B-Form</label><input type="text" name="cnic" value="<?php echo htmlspecialchars($student_data['stu_cnic']); ?>"></div>
            <div><label>Date of Birth</label><input type="date" name="dob" value="<?php echo $student_data['date_of_birth']; ?>"></div>
            <div><label>Religion</label><input type="text" name="religion" value="<?php echo htmlspecialchars($student_data['stu_religion']); ?>"></div>
            <div><label>Blood Group</label><input type="text" name="blood" value="<?php echo htmlspecialchars($student_data['blood_group']); ?>"></div>
        </div>

        <div class="section-label">2. Father's Details</div>
        <div class="grid">
            <div><label>Father's Name</label><input type="text" name="f_name" value="<?php echo htmlspecialchars($student_data['stu_father_name']); ?>"></div>
            <div><label>Father's CNIC</label><input type="text" name="f_cnic" value="<?php echo htmlspecialchars($student_data['father_cnic']); ?>"></div>
            <div><label>Father's Contact</label><input type="text" name="f_contact" value="<?php echo htmlspecialchars($student_data['father_contact']); ?>"></div>
            <div class="full-row"><label>Father's Occupation</label><input type="text" name="f_occ" value="<?php echo htmlspecialchars($student_data['father_occupation']); ?>"></div>
        </div>

        <div class="section-label">4. Status & Security</div>
        <div class="grid">
            <div><label>Attendance (%)</label><input type="text" name="attendance" value="<?php echo $student_data['stu_attendance']; ?>"></div>
            <div>
                <label>Fee Status</label>
                <select name="fee_status">
                    <option value="Paid" <?php echo ($student_data['stu_fee_status'] == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                    <option value="Unpaid" <?php echo ($student_data['stu_fee_status'] == 'Unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                </select>
            </div>
            <div class="full-row"><label>Portal Password</label><input type="text" name="password" value="<?php echo htmlspecialchars($student_data['password']); ?>" required></div>
        </div>

        <div class="btn-row">
            <button type="submit" name="submit" class="btn-save">SAVE CHANGES</button>
            <a href="dashboard.php" class="btn-cancel">CANCEL</a>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { document.getElementById('stu_preview').src = e.target.result; }
        reader.readAsDataURL(input.files[0]);
    }
}

function updateClasses(currentClass = null) {
    const wingSelect = document.getElementById('wingSelect');
    const classSelect = document.getElementById('classSelect');
    const selectedWing = wingSelect.value;
    classSelect.innerHTML = '';
    const classData = {
        "Lower Primary": ["Beginners", "Advance", "Prep", "Class 1", "Class 2"],
        "Upper Primary": ["Class 3", "Class 4", "Class 5"],
        "Boys wing": ["VI", "VII", "VIII", "IX", "X", "XI", "XII"],
        "Girls wing": ["VI", "VII", "VIII", "IX", "X", "XI", "XII"],
        "Cambridge": ["P-1", "P-2", "P-3", "Senior-1", "Senior-2", "Senior-3", "AS", "AL"],
        "Special education": ["Class 1", "Class 2", "Class 3", "Class 4", "Class 5", "Class 6", "Class 7", "Class 8", "Class 9", "Class 10", "Class 11", "Class 12"]
    };
    if (selectedWing && classData[selectedWing]) {
        classData[selectedWing].forEach(className => {
            const option = document.createElement('option');
            option.value = className; option.textContent = className;
            if (currentClass && className === currentClass) { option.selected = true; }
            classSelect.appendChild(option);
        });
    }
}
lucide.createIcons();
</script>
</body>
</html>