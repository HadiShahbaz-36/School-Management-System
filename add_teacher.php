<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') { 
    header('Location: index.php'); exit(); 
}

$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_DB";
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
} catch(PDOException $e) { die("Connection failed: " . $e->getMessage()); }

$msg = "";

if (isset($_POST['register_teacher'])) {
    // --- PHOTO UPLOAD LOGIC ---
    $photo_name = "default_teacher.png"; // Default if no photo uploaded
    if (!empty($_FILES["t_photo"]["name"])) {
        $target_dir = "uploads/teachers/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $file_ext = pathinfo($_FILES["t_photo"]["name"], PATHINFO_EXTENSION);
        $photo_name = "TCH_" . time() . "_" . rand(1000,9999) . "." . $file_ext;
        move_uploaded_file($_FILES["t_photo"]["tmp_name"], $target_dir . $photo_name);
    }

    $assigned_classes = isset($_POST['t_class']) ? implode(", ", $_POST['t_class']) : "";

    $sql = "INSERT INTO teachers (
        teacher_name, relation_name, dob, gender, marital_status, cnic, contact_no, 
        teacher_email, address, emergency_contact, highest_qualification, 
        university, subject_specialization, teaching_experience, prev_institutes,
        designation, teacher_wing, teacher_class, joining_date, employment_type,
        salary_amount, bank_details, teacher_id_official, password, teacher_photo
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $_POST['t_name'], $_POST['rel_name'], $_POST['dob'], $_POST['gender'], $_POST['m_status'], $_POST['cnic'], $_POST['contact'],
        $_POST['t_email'], $_POST['address'], $_POST['e_contact'], $_POST['qual'],
        $_POST['uni'], $_POST['subject'], $_POST['exp'], $_POST['prev_work'],
        $_POST['desig'], $_POST['t_wing'], $assigned_classes, $_POST['j_date'], $_POST['e_type'],
        $_POST['salary'], $_POST['bank'], $_POST['t_id'], $_POST['t_pass'], $photo_name
    ]);
    $msg = "Teacher Profile Registered Successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BCI HR | Teacher Registration</title>
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
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .header-box { text-align: center; margin-bottom: 40px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; }
        .header-box h1 { font-size: 2.2rem; letter-spacing: 4px; margin: 0; font-weight: 700; color: #fff; }
        .header-box p { color: #00d4ff; font-size: 0.9rem; text-transform: uppercase; margin-top: 5px; letter-spacing: 2px; }

        .form-section { 
            margin-bottom: 35px; background: rgba(0,0,0,0.25); padding: 25px; border-radius: 20px; 
            border: 1px solid rgba(255,255,255,0.05); transition: 0.4s;
        }
        .form-section:hover { 
            border-color: #00d4ff88; 
            background: rgba(0, 212, 255, 0.05);
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.1);
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

        input:focus, select:focus, textarea:focus { 
            border-color: #00d4ff; background: rgba(0,0,0,0.8); outline: none; 
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.2); 
        }

        select option { background-color: #111 !important; color: #fff !important; padding: 10px; }
        select[multiple] { height: 120px; cursor: pointer; }

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
            padding: 10px 20px; border-radius: 30px; border: 1px solid transparent;
        }
        .dashboard-link:hover { 
            color: #00d4ff; 
            border-color: #00d4ff44;
            background: rgba(0, 212, 255, 0.05);
        }
        
        /* Photo Preview Styling */
        .photo-upload-container { display: flex; flex-direction: column; align-items: center; justify-content: center; background: rgba(0,0,0,0.4); border: 2px dashed rgba(0, 212, 255, 0.3); border-radius: 20px; padding: 20px; grid-row: span 2; }
        #photo_preview { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; border: 3px solid #00d4ff; background: #222; }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-thumb { background: #00d4ff; border-radius: 10px; }
    </style>
</head>
<body>

<div class="form-container">
    <div class="header-box">
        <h1>TEACHER RECRUITMENT</h1>
        <p>Bahria College Islamabad | Human Resources</p>
    </div>

    <?php if($msg) echo "<div class='msg-box'>$msg</div>"; ?>

    <form method="POST" enctype="multipart/form-data">
        
        <div class="form-section">
            <span class="section-tag">1. Basic Personal Information</span>
            <div class="grid">
                <div class="field-group photo-upload-container">
                    <label>Teacher Profile Photo</label>
                    <img src="assets/img/default_user.png" id="photo_preview" alt="Preview">
                    <input type="file" name="t_photo" accept="image/*" onchange="previewImage(this)" style="font-size: 0.8rem;">
                </div>

                <div class="field-group"><label>Full Name</label><input type="text" name="t_name" required></div>
                
                <div class="field-group">
                    <label>Gender</label>
                    <select name="gender" id="gender" onchange="toggleRelationLabel()">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div class="field-group">
                    <label>Marital Status</label>
                    <select name="m_status" id="m_status" onchange="toggleRelationLabel()">
                        <option value="Unmarried">Unmarried</option>
                        <option value="Married">Married</option>
                    </select>
                </div>

                <div class="field-group">
                    <label id="rel_label">Father's Name</label>
                    <input type="text" name="rel_name" required>
                </div>

                <div class="field-group"><label>Date of Birth</label><input type="date" name="dob" required></div>
                <div class="field-group"><label>CNIC Number</label><input type="text" name="cnic" placeholder="xxxxx-xxxxxxx-x" required></div>
                <div class="field-group"><label>Teacher ID</label><input type="text" name="t_id" placeholder="BCI-T-000"></div>
                <div class="field-group"><label>Mobile Number</label><input type="text" name="contact" required></div>
                <div class="field-group"><label>Email Address</label><input type="email" name="t_email" required></div>
                <div class="field-group"><label>Emergency Contact</label><input type="text" name="e_contact"></div>
                <div class="field-group full-row"><label>Home Address</label><input type="text" name="address"></div>
            </div>
        </div>

        <div class="form-section">
            <span class="section-tag">2. Education & Qualifications</span>
            <div class="grid">
                <div class="field-group"><label>Highest Degree</label><input type="text" name="qual" placeholder="e.g. MSc Physics" required></div>
                <div class="field-group"><label>University / Board</label><input type="text" name="uni" required></div>
                <div class="field-group"><label>Subjects Studied</label><input type="text" name="subject" placeholder="Major Subjects"></div>
                <div class="field-group"><label>Experience (Years)</label><input type="number" name="exp"></div>
                <div class="field-group full-row"><label>Previous Institutes</label><textarea name="prev_work" rows="2"></textarea></div>
            </div>
        </div>

        <div class="form-section">
            <span class="section-tag">3. Employment Details</span>
            <div class="grid">
                <div class="field-group">
                    <label>Designation</label>
                    <select name="desig">
                        <option value="Teacher">Teacher</option>
                        <option value="Lecturer">Lecturer</option>
                        <option value="Senior Teacher">Senior Teacher</option>
                    </select>
                </div>
                <div class="field-group">
                    <label>Assigned Wing</label>
                    <select name="t_wing" id="wingSelect" onchange="updateClasses()" required>
                        <option value="">Select Wing</option>
                        <option value="Boys">Boys Wing</option>
                        <option value="Girls">Girls Wing</option>
                        <option value="Cambridge">Cambridge Wing</option>
                        <option value="Lower Primary">Lower Primary Wing</option>
                        <option value="Upper Primary">Upper Primary Wing</option>
                    </select>
                </div>
                <div class="field-group">
                    <label>Assigned Classes (Hold Ctrl to select)</label>
                    <select name="t_class[]" id="classSelect" multiple required></select>
                </div>
                <div class="field-group"><label>Joining Date</label><input type="date" name="j_date"></div>
                <div class="field-group">
                    <label>Employment Type</label>
                    <select name="e_type">
                        <option value="Full-time">Full-time</option>
                        <option value="Part-time">Part-time</option>
                        <option value="Visiting">Visiting</option>
                    </select>
                </div>
                <div class="field-group"><label>Portal Password</label><input type="password" name="t_pass" required></div>
            </div>
        </div>

        <div class="form-section">
            <span class="section-tag">4. Salary & Finance</span>
            <div class="grid">
                <div class="field-group"><label>Monthly Salary (PKR)</label><input type="number" name="salary"></div>
                <div class="field-group full-row"><label>Bank Account / IBAN</label><input type="text" name="bank"></div>
            </div>
        </div>

        <button type="submit" name="register_teacher" class="submit-btn">Finalize & Register Teacher</button>
    </form>
</div>

<a href="dashboard.php" class="dashboard-link">
    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
    Return to Dashboard
</a>

<script>
// Image Preview
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photo_preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Logic for dynamic Father/Husband Label
function toggleRelationLabel() {
    const gender = document.getElementById('gender').value;
    const status = document.getElementById('m_status').value;
    const label = document.getElementById('rel_label');

    if (gender === 'Female' && status === 'Married') {
        label.innerText = "Husband's Name";
    } else {
        label.innerText = "Father's Name";
    }
}

// Fixed Dependent Dropdown Logic
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
            classSelect.appendChild(opt);
        });
    }
}
</script>

</body>
</html>
