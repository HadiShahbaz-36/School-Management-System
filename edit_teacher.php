<?php
session_start();
// Security check: Only Admins can edit profiles
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') { 
    header('Location: index.php'); exit(); 
}

$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_DB";
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Connection failed: " . $e->getMessage()); }

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: dashboard.php'); exit(); }

$msg = "";

// UPDATE LOGIC
if (isset($_POST['update_teacher'])) {
    $assigned_classes = isset($_POST['t_class']) ? implode(", ", $_POST['t_class']) : "";
    
    // --- PHOTO UPDATE LOGIC ---
    $photo_query = "";
    $params = [
        $_POST['t_name'], $_POST['rel_name'], $_POST['dob'], $_POST['gender'], $_POST['m_status'], $_POST['cnic'], $_POST['contact'],
        $_POST['t_email'], $_POST['address'], $_POST['e_contact'] ?? '', $_POST['qual'] ?? '',
        $_POST['uni'] ?? '', $_POST['subject'] ?? '', $_POST['exp'] ?? 0, $_POST['prev_work'] ?? '',
        $_POST['desig'], $_POST['t_wing'], $assigned_classes, $_POST['j_date'], $_POST['e_type'] ?? 'Full-time',
        $_POST['salary'] ?? 0, $_POST['bank'] ?? '', $_POST['t_id'], $_POST['t_pass']
    ];

    if (!empty($_FILES["t_photo"]["name"])) {
        $target_dir = "uploads/teachers/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $file_ext = pathinfo($_FILES["t_photo"]["name"], PATHINFO_EXTENSION);
        $new_photo_name = "TCH_" . time() . "_" . rand(1000,9999) . "." . $file_ext;
        
        if (move_uploaded_file($_FILES["t_photo"]["tmp_name"], $target_dir . $new_photo_name)) {
            $photo_query = ", teacher_photo=?";
            $params[] = $new_photo_name;
        }
    }
    $params[] = $id; 

    $sql = "UPDATE teachers SET 
        teacher_name=?, relation_name=?, dob=?, gender=?, marital_status=?, cnic=?, contact_no=?, 
        teacher_email=?, address=?, emergency_contact=?, highest_qualification=?, 
        university=?, subject_specialization=?, teaching_experience=?, prev_institutes=?,
        designation=?, teacher_wing=?, teacher_class=?, joining_date=?, employment_type=?,
        salary_amount=?, bank_details=?, teacher_id_official=?, password=? $photo_query
        WHERE teacher_id=?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $msg = "Teacher Profile Updated Successfully!";
}

// FETCH CURRENT DATA
$stmt = $conn->prepare("SELECT * FROM teachers WHERE teacher_id = ?");
$stmt->execute([$id]);
$t = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$t) { die("Teacher not found."); }

$current_classes = explode(", ", $t['teacher_class']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | <?php echo htmlspecialchars($t['teacher_name']); ?></title>
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

        .header-box { text-align: center; margin-bottom: 40px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; }
        .header-box h1 { font-size: 2.2rem; letter-spacing: 4px; margin: 0; font-weight: 700; color: #fff; }
        .header-box p { color: #00d4ff; font-size: 0.9rem; text-transform: uppercase; margin-top: 5px; letter-spacing: 2px; }

        .form-section { 
            margin-bottom: 35px; background: rgba(0,0,0,0.25); padding: 25px; border-radius: 20px; 
            border: 1px solid rgba(255,255,255,0.05); transition: 0.4s;
        }

        .section-tag { 
            background: #00d4ff; color: #000; padding: 6px 16px; font-size: 0.75rem; 
            font-weight: 800; border-radius: 6px; text-transform: uppercase; margin-bottom: 25px; display: inline-block;
        }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; }
        .field-group { display: flex; flex-direction: column; }
        label { font-size: 0.7rem; color: #00d4ff; text-transform: uppercase; margin-bottom: 10px; font-weight: 600; letter-spacing: 1px; }
        
        input, select, textarea {
            background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.1);
            padding: 14px; border-radius: 12px; color: white; font-family: inherit; transition: 0.3s;
        }

        input:focus, select:focus, textarea:focus { border-color: #00d4ff; background: rgba(0,0,0,0.8); outline: none; }

        .full-row { grid-column: 1 / -1; }
        
        .submit-btn {
            background: #00d4ff; color: #000; border: none; padding: 22px; border-radius: 18px;
            font-weight: 800; text-transform: uppercase; cursor: pointer; width: 100%;
            margin-top: 30px; font-size: 1.1rem; transition: 0.4s; letter-spacing: 2px;
        }
        .submit-btn:hover { background: #fff; transform: translateY(-3px); box-shadow: 0 15px 40px rgba(0, 212, 255, 0.4); }

        .msg-box { background: rgba(0, 255, 136, 0.1); color: #00ff88; padding: 20px; border-radius: 15px; text-align: center; margin-bottom: 30px; border: 1px solid rgba(0, 255, 136, 0.3); font-weight: 600; }

        .dashboard-link {
            margin-top: 40px; text-decoration: none; color: rgba(255,255,255,0.6); font-size: 0.9rem;
            display: flex; align-items: center; gap: 10px; transition: 0.3s;
        }
        .dashboard-link:hover { color: #00d4ff; }

        /* Photo Edit Styling Fixed */
        .photo-edit-box { display: flex; align-items: center; gap: 20px; background: rgba(0,0,0,0.3); padding: 15px; border-radius: 15px; border: 1px dashed rgba(0, 212, 255, 0.3); }
        #photo_preview { width: 100px; height: 100px; border-radius: 10px; object-fit: cover; border: 2px solid #00d4ff; background: #222; }
    </style>
</head>
<body>

<div class="form-container">
    <div class="header-box">
        <h1>UPDATE TEACHER PROFILE</h1>
        <p>Current Teacher: <?php echo htmlspecialchars($t['teacher_name']); ?></p>
    </div>

    <?php if($msg) echo "<div class='msg-box'>$msg</div>"; ?>

    <form method="POST" enctype="multipart/form-data">
        
        <div class="form-section">
            <span class="section-tag">1. Personal Information</span>
            <div class="grid">
                <div class="field-group full-row">
                    <label>Profile Image</label>
                    <div class="photo-edit-box">
                        <?php 
                            $img_path = "uploads/teachers/" . $t['teacher_photo'];
                            $display_img = (!empty($t['teacher_photo']) && file_exists($img_path)) ? $img_path : "assets/img/default_user.png";
                        ?>
                        <img src="<?php echo $display_img; ?>?v=<?php echo time(); ?>" id="photo_preview">
                        <div>
                            <input type="file" name="t_photo" accept="image/*" onchange="previewImage(this)">
                            <p style="font-size: 10px; color: #888; margin-top: 5px;">Leave empty to keep current photo</p>
                        </div>
                    </div>
                </div>

                <div class="field-group"><label>Full Name</label><input type="text" name="t_name" value="<?php echo htmlspecialchars($t['teacher_name']); ?>" required></div>
                
                <div class="field-group">
                    <label>Gender</label>
                    <select name="gender" id="gender" onchange="toggleRelationLabel()">
                        <option value="Male" <?php echo ($t['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($t['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>

                <div class="field-group">
                    <label>Marital Status</label>
                    <select name="m_status" id="m_status" onchange="toggleRelationLabel()">
                        <option value="Unmarried" <?php echo ($t['marital_status'] == 'Unmarried') ? 'selected' : ''; ?>>Unmarried</option>
                        <option value="Married" <?php echo ($t['marital_status'] == 'Married') ? 'selected' : ''; ?>>Married</option>
                    </select>
                </div>

                <div class="field-group">
                    <label id="rel_label">Father's Name</label>
                    <input type="text" name="rel_name" value="<?php echo htmlspecialchars($t['relation_name']); ?>" required>
                </div>

                <div class="field-group"><label>Date of Birth</label><input type="date" name="dob" value="<?php echo $t['dob']; ?>" required></div>
                <div class="field-group"><label>CNIC Number</label><input type="text" name="cnic" value="<?php echo htmlspecialchars($t['cnic']); ?>" required></div>
                <div class="field-group"><label>Mobile Number</label><input type="text" name="contact" value="<?php echo htmlspecialchars($t['contact_no']); ?>" required></div>
                <div class="field-group"><label>Email Address</label><input type="email" name="t_email" value="<?php echo htmlspecialchars($t['teacher_email']); ?>" required></div>
                <div class="field-group full-row"><label>Home Address</label><input type="text" name="address" value="<?php echo htmlspecialchars($t['address']); ?>"></div>
            </div>
        </div>

        <div class="form-section">
            <span class="section-tag">2. Portal Access (Login Details)</span>
            <div class="grid">
                <div class="field-group" style="border: 1px solid rgba(0, 212, 255, 0.3); padding: 10px; border-radius: 10px;">
                    <label>Official Teacher ID (Username)</label>
                    <input type="text" name="t_id" value="<?php echo htmlspecialchars($t['teacher_id_official']); ?>" style="color: #00d4ff; font-weight: bold;">
                </div>
                <div class="field-group" style="border: 1px solid rgba(0, 212, 255, 0.3); padding: 10px; border-radius: 10px;">
                    <label>Portal Password</label>
                    <input type="text" name="t_pass" value="<?php echo htmlspecialchars($t['password']); ?>" required style="color: #00d4ff; font-weight: bold;">
                </div>
            </div>
        </div>

        <div class="form-section">
            <span class="section-tag">3. Employment & Academic Wing</span>
            <div class="grid">
                <div class="field-group">
                    <label>Designation</label>
                    <select name="desig">
                        <option value="Teacher" <?php if($t['designation']=='Teacher') echo 'selected'; ?>>Teacher</option>
                        <option value="Lecturer" <?php if($t['designation']=='Lecturer') echo 'selected'; ?>>Lecturer</option>
                        <option value="Senior Teacher" <?php if($t['designation']=='Senior Teacher') echo 'selected'; ?>>Senior Teacher</option>
                    </select>
                </div>
                <div class="field-group">
                    <label>Assigned Wing</label>
                    <select name="t_wing" id="wingSelect" onchange="updateClasses()" required>
                        <option value="Boys" <?php if($t['teacher_wing']=='Boys') echo 'selected'; ?>>Boys Wing</option>
                        <option value="Girls" <?php if($t['teacher_wing']=='Girls') echo 'selected'; ?>>Girls Wing</option>
                        <option value="Cambridge" <?php if($t['teacher_wing']=='Cambridge') echo 'selected'; ?>>Cambridge Wing</option>
                        <option value="Lower Primary" <?php if($t['teacher_wing']=='Lower Primary') echo 'selected'; ?>>Lower Primary Wing</option>
                        <option value="Upper Primary" <?php if($t['teacher_wing']=='Upper Primary') echo 'selected'; ?>>Upper Primary Wing</option>
                    </select>
                </div>
                <div class="field-group">
                    <label>Assigned Classes (Ctrl+Click to Multi-Select)</label>
                    <select name="t_class[]" id="classSelect" multiple required style="height: 120px;"></select>
                </div>
                <div class="field-group"><label>Joining Date</label><input type="date" name="j_date" value="<?php echo $t['joining_date']; ?>"></div>
            </div>
        </div>

        <button type="submit" name="update_teacher" class="submit-btn">Update Profile & Access</button>
        
        <center>
            <a href="dashboard.php" class="dashboard-link">← Return to Admin Dashboard</a>
        </center>
    </form>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photo_preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function toggleRelationLabel() {
    const gender = document.getElementById('gender').value;
    const status = document.getElementById('m_status').value;
    const label = document.getElementById('rel_label');
    label.innerText = (gender === 'Female' && status === 'Married') ? "Husband's Name" : "Father's Name";
}

const currentAssigned = <?php echo json_encode($current_classes); ?>;

function updateClasses() {
    const wing = document.getElementById('wingSelect').value;
    const classSelect = document.getElementById('classSelect');
    classSelect.innerHTML = "";
    
    const classData = {
        "Lower Primary": ["Class Beginners", "Class Advance", "Class Prep", "Class I", "Class II"],
        "Upper Primary": ["Class III", "Class IV", "Class V"],
        "Boys": ["Class VI", "Class VII", "Class VIII", "Class IX", "Class X", "Class XI", "Class XII"],
        "Girls": ["Class VI", "Class VII", "Class VIII", "Class IX", "Class X", "Class XI", "Class XII"],
        "Cambridge": ["P-1", "P-2", "P-3", "Senior-1", "Senior-2", "Senior-3", "AS", "AL"]
    };

    if (classData[wing]) {
        classData[wing].forEach(c => {
            let opt = document.createElement('option');
            opt.value = c; 
            opt.innerHTML = c;
            if(Array.isArray(currentAssigned) && currentAssigned.includes(c)) opt.selected = true;
            classSelect.appendChild(opt);
        });
    }
}

window.onload = function() {
    updateClasses();
    toggleRelationLabel();
};
</script>
</body>
</html>