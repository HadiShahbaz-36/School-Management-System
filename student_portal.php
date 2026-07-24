<?php
session_start();

// --- AMENDMENT 1: Syncing session with index.php ---
if (!isset($_SESSION['stu_id'])) {
    header('Location: index.php'); 
    exit();
}

$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_DB";
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Database Connection Fail"); }

// --- AMENDMENT 2: Using the correct session variable ---
$enroll = $_SESSION['stu_id']; 
$today = date('Y-m-d');

// --- FETCH STUDENT DETAILS ---
$stmt = $conn->prepare("SELECT * FROM students WHERE stu_enrollment_number = ?");
$stmt->execute([$enroll]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) { session_destroy(); header('Location: index.php'); exit(); }

// --- FETCH LATEST GENERATED FEE RECORD ---
$fee_stmt = $conn->prepare("SELECT * FROM fees WHERE student_enrollment = ? ORDER BY fee_id DESC LIMIT 1");
$fee_stmt->execute([$enroll]);
$latest_fee = $fee_stmt->fetch(PDO::FETCH_ASSOC);

// --- ATTENDANCE LOGIC (FIXED COLUMN NAME) ---
$today_status = "Not Marked";
try {
    // UPDATED: 'attendance_date' instead of 'date' to match your DB
    $today_stmt = $conn->prepare("SELECT status FROM attendance WHERE student_id = ? AND attendance_date = ?");
    $today_stmt->execute([$enroll, $today]);
    $attendance_row = $today_stmt->fetch(PDO::FETCH_ASSOC);
    if ($attendance_row) { $today_status = $attendance_row['status']; }
} catch (PDOException $e) { $today_status = "Not Marked"; }

// --- EXAMINATION RESULTS (PRESERVED) ---
$marks_stmt = $conn->prepare("SELECT term_name, subject_name, total_marks, obtained_marks FROM student_marks WHERE enrollment_number = ?");
$marks_stmt->execute([$enroll]);
$all_marks = $marks_stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

// --- PHOTO PATH LOGIC ---
$photo_name = $data['stu_photo'] ?? ''; 
$img_path = "uploads/students/" . $photo_name;
$display_img = (!empty($photo_name) && file_exists($img_path)) ? $img_path : "assets/img/default_student.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Portal | <?php echo htmlspecialchars($data['stu_name']); ?></title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* AAPKI SAARI CSS IDHAR HAI - NO CHANGES MADE */
        @import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap");
        :root { --primary: #00d4ff; --bg: #060606; --card: rgba(255,255,255,0.03); --border: rgba(255,255,255,0.1); }

        body { background: var(--bg); color: white; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; display: flex; justify-content: center; min-height: 100vh; }
        .wrapper { width: 100%; max-width: 550px; padding: 40px 20px; }

        .glass-card { background: var(--card); border: 1px solid var(--border); border-radius: 30px; padding: 30px; margin-bottom: 20px; backdrop-filter: blur(10px); }
        
        .profile-header { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; }
        .student-avatar { width: 85px; height: 85px; border-radius: 20px; object-fit: cover; border: 2px solid var(--primary); box-shadow: 0 0 15px rgba(0,212,255,0.2); }

        .attendance-badge { display: inline-block; padding: 6px 15px; border-radius: 20px; font-size: 11px; font-weight: 800; border: 1px solid; margin-bottom: 15px;}
        .Present { color: #00ff88; border-color: #00ff88; background: rgba(0,255,136,0.05); }
        .Absent { color: #ff4d4d; border-color: #ff4d4d; background: rgba(255,77,77,0.05); }
        .Not.Marked { color: #888; border-color: #444; }

        .profile-info h1 { margin: 0; font-size: 1.4rem; font-weight: 800; }
        .grid-info { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 5px; }
        .info-pill { background: rgba(0,0,0,0.2); padding: 12px; border-radius: 15px; border: 1px solid var(--border); }
        .info-pill label { display: block; font-size: 9px; color: #666; text-transform: uppercase; font-weight: 700; margin-bottom: 4px; }

        .fee-status { font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 8px; text-transform: uppercase; }
        .status-Pending { background: rgba(255, 204, 0, 0.1); color: #ffcc00; border: 1px solid #ffcc00; }
        .status-Paid { background: rgba(0, 255, 136, 0.1); color: #00ff88; border: 1px solid #00ff88; }
        .btn-download-fee { 
            display: flex; align-items: center; justify-content: center; gap: 10px;
            background: white; color: black; text-decoration: none; padding: 12px;
            border-radius: 15px; font-weight: 800; font-size: 12px; margin-top: 15px;
            transition: 0.3s;
        }
        .btn-download-fee:hover { transform: scale(1.02); background: var(--primary); }

        .term-item { background: var(--card); border: 1px solid var(--border); padding: 20px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; cursor: pointer; transition: 0.3s; }
        .term-item:hover { border-color: var(--primary); transform: translateX(5px); }

        #portalView { display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }
        .portal-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .portal-table td { padding: 12px 0; border-bottom: 1px solid var(--border); font-size: 14px; }
        .btn-print { background: var(--primary); color: black; border: none; padding: 15px; width: 100%; border-radius: 12px; font-weight: 800; margin-top: 20px; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; }

        #printableTranscript { display: none; }

        @media print {
            @page { size: A4; margin: 0; }
            html, body { background: white !important; margin: 0 !important; padding: 0 !important; width: 100%; display: block !important; }
            .wrapper, .glass-card, .term-item, h4, .attendance-badge, .btn-print { display: none !important; }

            #printableTranscript { display: block !important; margin: 50px auto !important; width: 85% !important; color: black !important; background: white !important; padding: 20px; }
            .official-header { text-align: center; border-bottom: 4px double #003366; padding-bottom: 15px; margin-bottom: 30px; position: relative; }
            .print-photo { position: absolute; right: 0; top: 0; width: 100px; height: 100px; border: 1px solid #000; object-fit: cover; }
            .official-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            .official-table th, .official-table td { border: 1px solid #000; padding: 10px; text-align: left; font-size: 13px; color: black !important; }
            .official-table th { background: #f2f2f2 !important; }
            .official-footer { margin-top: 50px; text-align: center; font-size: 11px; border-top: 1px solid #ddd; padding-top: 20px; font-style: italic; color: black !important; }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="glass-card">
        <div class="attendance-badge <?php echo str_replace(' ', '.', $today_status); ?>">TODAY: <?php echo strtoupper($today_status); ?></div>
        
        <div class="profile-header">
            <img src="<?php echo $display_img; ?>?v=<?php echo time(); ?>" class="student-avatar" alt="Profile">
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($data['stu_name']); ?></h1>
                <p style="color: #888; font-size: 12px; margin: 2px 0;"><?php echo $enroll; ?></p>
                <span style="color: var(--primary); font-size: 11px; font-weight: 800; text-transform: uppercase;"><?php echo $data['stu_wing']; ?> Wing</span>
            </div>
        </div>

        <div class="grid-info">
            <div class="info-pill"><label>Father</label><span><?php echo $data['stu_father_name']; ?></span></div>
            <div class="info-pill"><label>Class</label><span><?php echo $data['stu_class']; ?></span></div>
        </div>
    </div>

    <h4 style="color: #444; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">Financial Overview</h4>
    <div class="glass-card" style="padding: 20px;">
        <?php if($latest_fee): ?>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="display:block; font-size: 10px; color: #888; text-transform: uppercase;">Fee Bill (<?php echo $latest_fee['month_for']; ?>)</span>
                    <span style="font-size: 18px; font-weight: 800; color: var(--primary);">Rs. <?php echo number_format($latest_fee['amount_paid']); ?></span>
                </div>
                <span class="fee-status status-<?php echo $latest_fee['status']; ?>"><?php echo $latest_fee['status']; ?></span>
            </div>
            <a href="download_challan.php?challan_id=<?php echo $latest_fee['fee_id']; ?>" target="_blank" class="btn-download-fee">
                <i data-lucide="download-cloud" style="width:16px;"></i> DOWNLOAD CHALLAN
            </a>
        <?php else: ?>
            <p style="font-size: 12px; color: #666; text-align: center; margin: 0;">No active invoices found.</p>
        <?php endif; ?>
    </div>

    <h4 style="color: #444; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">Examination Results</h4>
    <?php if(empty($all_marks)): ?>
         <p style="font-size: 12px; color: #666; text-align: center;">No results available yet.</p>
    <?php endif; ?>

    <?php foreach($all_marks as $term => $subjects): ?>
        <div class="term-item" onclick="viewOnPortal('<?php echo $term; ?>', <?php echo htmlspecialchars(json_encode($subjects)); ?>)">
            <span style="font-weight: 700;"><?php echo $term; ?></span>
            <div style="color: var(--primary); font-size: 11px; font-weight: 800;">VIEW RESULT <i data-lucide="chevron-right" style="width:12px; vertical-align:middle;"></i></div>
        </div>
    <?php endforeach; ?>

    <div id="portalView" class="glass-card">
        <h3 id="displayTerm" style="margin: 0; color: var(--primary);"></h3>
        <table class="portal-table"><tbody id="portalTableBody"></tbody></table>
        <button class="btn-print" onclick="window.print()"><i data-lucide="printer" style="width:16px;"></i> PRINT OFFICIAL TRANSCRIPT</button>
    </div>
</div>

<div id="printableTranscript">
    <div class="official-header">
        <img src="<?php echo $display_img; ?>" class="print-photo">
        <h1 style="margin: 0; font-family: serif; color: #003366; font-size: 30px; font-weight: 900;">BAHRIA COLLEGE ISLAMABAD</h1>
        <p style="margin: 5px 0; font-weight: 800; letter-spacing: 4px;"><?php echo strtoupper($data['stu_wing']); ?> WING</p>
        <p style="font-size: 12px; font-weight: 600;">ACADEMIC PERFORMANCE REPORT</p>
    </div>

    <div style="display: flex; justify-content: space-between; margin-bottom: 25px; color: black; font-size: 13px;">
        <div style="line-height: 1.8;">
            <strong>STUDENT NAME:</strong> <?php echo strtoupper($data['stu_name']); ?><br>
            <strong>FATHER'S NAME:</strong> <?php echo strtoupper($data['stu_father_name']); ?><br>
            <strong>ENROLLMENT NO:</strong> <?php echo $enroll; ?>
        </div>
        <div style="text-align: right; line-height: 1.8; margin-right: 120px;">
            <strong>CLASS / SECTION:</strong> <?php echo $data['stu_class']; ?><br>
            <strong>EXAMINATION:</strong> <span id="printTermName"></span><br>
            <strong>DATE:</strong> <?php echo date('d-M-Y'); ?>
        </div>
    </div>

    <table class="official-table">
        <thead>
            <tr>
                <th>SUBJECT TITLE</th>
                <th>MAX MARKS</th>
                <th>OBTAINED</th>
                <th>PERCENTAGE</th>
            </tr>
        </thead>
        <tbody id="printTableBody"></tbody>
    </table>

    <div class="official-footer">
        <p>This is a computer-generated transcript. No physical signature or seal is required.</p>
        <p>Verified by Bahria College Student Management System © <?php echo date('Y'); ?></p>
    </div>
</div>

<script>
    lucide.createIcons();
    function viewOnPortal(term, subjects) {
        document.getElementById('portalView').style.display = 'block';
        document.getElementById('displayTerm').innerText = term;
        document.getElementById('printTermName').innerText = term;
        
        let pBody = document.getElementById('portalTableBody');
        let prBody = document.getElementById('printTableBody');
        
        pBody.innerHTML = ""; prBody.innerHTML = "";
        let tM = 0, tO = 0;

        subjects.forEach(s => {
            let m = parseFloat(s.total_marks), o = parseFloat(s.obtained_marks);
            tM += m; tO += o;
            let perc = ((o/m)*100).toFixed(1) + "%";

            pBody.innerHTML += `<tr><td style="color:#aaa;">${s.subject_name}</td><td style="text-align:right; font-weight:700;">${o} / ${m}</td></tr>`;
            prBody.innerHTML += `<tr><td>${s.subject_name}</td><td>${m}</td><td>${o}</td><td style="font-weight:bold;">${perc}</td></tr>`;
        });

        prBody.innerHTML += `<tr style="background:#f2f2f2; font-weight:900;"><td style="text-align:right;">AGGREGATE TOTAL:</td><td>${tM}</td><td>${tO}</td><td>${((tO/tM)*100).toFixed(1)}%</td></tr>`;
        
        document.getElementById('portalView').scrollIntoView({ behavior: 'smooth' });
    }
</script>
</body>
</html>             