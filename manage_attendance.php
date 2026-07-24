<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') { 
    header('Location: index.php'); exit(); 
}

$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_DB";
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Database Connection Fail"); }

$selected_class = $_GET['class'] ?? '';
$today = date('Y-m-d'); 
$display_date = date('d M Y'); 

// --- 1. AJAX SAVING ---
if (isset($_POST['update_status'])) {
    $enroll = $_POST['enroll_num'];
    $status = $_POST['status'];
    $sql = "INSERT INTO attendance (student_id, attendance_date, status) VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE status = ?";
    $conn->prepare($sql)->execute([$enroll, $today, $status, $status]);
    exit;
}

// --- 2. FETCH STUDENTS & HISTORY STATS ---
$stmt = $conn->prepare("
    SELECT s.stu_name, s.stu_enrollment_number, 
    (SELECT COUNT(*) FROM attendance WHERE student_id = s.stu_enrollment_number AND status = 'Present') as total_presents,
    (SELECT COUNT(*) FROM attendance WHERE student_id = s.stu_enrollment_number) as total_days
    FROM students s 
    WHERE s.stu_class = ? 
    ORDER BY s.stu_name ASC
");
$stmt->execute([$selected_class]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 3. GET TODAY'S LIVE RECORD ---
$att_stmt = $conn->prepare("SELECT student_id, status FROM attendance WHERE attendance_date = ?");
$att_stmt->execute([$today]);
$today_records = $att_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$stats = array_count_values($today_records);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Master Attendance | <?php echo $selected_class; ?></title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap");
        :root {
            --primary: #00d4ff; --present: #00ff88; --absent: #ff4d4d; --leave: #f39c12;
            --bg: #030712; --card: rgba(255, 255, 255, 0.03); --border: rgba(255, 255, 255, 0.08);
        }
        body {
            background: var(--bg); color: white; font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0; padding: 20px; min-height: 100vh;
            background-image: radial-gradient(at 0% 0%, rgba(0, 212, 255, 0.1) 0px, transparent 50%);
        }
        .container { max-width: 850px; margin: auto; }

        .master-header {
            background: var(--card); border: 1px solid var(--border); backdrop-filter: blur(20px);
            padding: 25px; border-radius: 24px; margin-bottom: 20px; display: flex;
            justify-content: space-between; align-items: center;
        }
        .date-box { color: var(--primary); font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; margin-top: 5px;}

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
        .stat-card { 
            background: var(--card); border: 1px solid var(--border); padding: 12px; 
            border-radius: 18px; text-align: center;
        }
        .stat-card span { display: block; font-size: 0.65rem; color: #888; text-transform: uppercase; }
        .stat-card b { font-size: 1.3rem; color: var(--primary); }

        .stu-row {
            background: var(--card); border: 1px solid var(--border); padding: 15px 20px;
            border-radius: 20px; margin-bottom: 10px; display: flex; justify-content: space-between;
            align-items: center; transition: 0.3s;
        }
        .stu-row:hover { border-color: var(--primary); background: rgba(255,255,255,0.05); }
        
        .history-btn {
            cursor: pointer; color: #666; transition: 0.3s; margin-left: 8px;
        }
        .history-btn:hover { color: var(--primary); transform: scale(1.2); }

        .perc-badge {
            font-size: 0.65rem; background: rgba(255,255,255,0.05); padding: 3px 8px;
            border-radius: 6px; color: #888; margin-top: 4px; display: inline-block;
        }

        .action-group { display: flex; gap: 8px; }
        .mark-btn {
            width: 42px; height: 42px; border-radius: 12px; border: 1px solid var(--border);
            background: rgba(0,0,0,0.3); color: #555; cursor: pointer; font-weight: 800; transition: 0.2s;
        }
        .btn-p:hover, .active-p { background: var(--present) !important; color: #000 !important; box-shadow: 0 0 15px rgba(0,255,136,0.3); }
        .btn-a:hover, .active-a { background: var(--absent) !important; color: #fff !important; box-shadow: 0 0 15px rgba(255,77,77,0.3); }
        .btn-l:hover, .active-l { background: var(--leave) !important; color: #fff !important; box-shadow: 0 0 15px rgba(243,156,18,0.3); }

        /* Modal Styles */
        .modal {
            display: none; position: fixed; z-index: 2000; left: 0; top: 0; 
            width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(8px);
        }
        .modal-content {
            background: #0f172a; margin: 5% auto; padding: 25px; 
            width: 90%; max-width: 550px; border-radius: 28px; border: 1px solid var(--border);
            animation: modalSlide 0.3s ease-out;
        }
        @keyframes modalSlide { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .finish-btn {
            background: var(--primary); color: #000; padding: 15px; border-radius: 18px;
            width: 100%; border: none; font-weight: 800; cursor: pointer; margin-top: 10px;
            box-shadow: 0 10px 20px rgba(0, 212, 255, 0.2);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="master-header">
        <div>
            <h1 style="margin:0; font-size:1.5rem;"><?php echo htmlspecialchars($selected_class); ?></h1>
            <div class="date-box"><i data-lucide="calendar" size="14"></i> <?php echo $display_date; ?></div>
        </div>
        <button onclick="location.href='teacher_dashboard.php'" style="background:none; border:none; color:#666; cursor:pointer; font-weight:700;">EXIT</button>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><span>Present</span><b id="c-p"><?php echo $stats['Present'] ?? 0; ?></b></div>
        <div class="stat-card"><span>Absent</span><b id="c-a"><?php echo $stats['Absent'] ?? 0; ?></b></div>
        <div class="stat-card"><span>Leave</span><b id="c-l"><?php echo $stats['Leave'] ?? 0; ?></b></div>
    </div>

    <div class="list">
        <?php foreach($students as $s): 
            $enroll = $s['stu_enrollment_number'];
            $current = $today_records[$enroll] ?? '';
            $perc = ($s['total_days'] > 0) ? round(($s['total_presents'] / $s['total_days']) * 100) : 0;
        ?>
        <div class="stu-row">
            <div>
                <div style="display:flex; align-items:center;">
                    <span style="font-weight:700;"><?php echo htmlspecialchars($s['stu_name']); ?></span>
                    <i data-lucide="history" class="history-btn" size="14" onclick="openHistory('<?php echo $enroll; ?>', '<?php echo addslashes($s['stu_name']); ?>')"></i>
                </div>
                <div class="perc-badge">Score: <?php echo $perc; ?>% | Presents: <?php echo $s['total_presents']; ?></div>
            </div>

            <div class="action-group">
                <button class="mark-btn btn-p <?php echo ($current == 'Present') ? 'active-p' : ''; ?>" onclick="mark('<?php echo $enroll; ?>', 'Present', this)">P</button>
                <button class="mark-btn btn-a <?php echo ($current == 'Absent') ? 'active-a' : ''; ?>" onclick="mark('<?php echo $enroll; ?>', 'Absent', this)">A</button>
                <button class="mark-btn btn-l <?php echo ($current == 'Leave') ? 'active-l' : ''; ?>" onclick="mark('<?php echo $enroll; ?>', 'Leave', this)">L</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <button class="finish-btn" onclick="location.href='teacher_dashboard.php'">SAVE & FINISH SESSION</button>
</div>

<div id="hModal" class="modal">
    <div class="modal-content">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 id="hTitle" style="margin:0; font-size:1.1rem; color:var(--primary);"></h3>
            <i data-lucide="x" style="cursor:pointer" onclick="closeModal()"></i>
        </div>
        <div id="hBody" style="max-height:350px; overflow-y:auto; border-radius:15px;"></div>
    </div>
</div>

<script>
    lucide.createIcons();

    function mark(enroll, status, btn) {
        let parent = btn.parentElement;
        parent.querySelectorAll('.mark-btn').forEach(b => b.classList.remove('active-p', 'active-a', 'active-l'));
        
        if(status === 'Present') btn.classList.add('active-p');
        if(status === 'Absent') btn.classList.add('active-a');
        if(status === 'Leave') btn.classList.add('active-l');

        let fd = new FormData();
        fd.append('update_status', '1');
        fd.append('enroll_num', enroll);
        fd.append('status', status);
        fetch('', { method: 'POST', body: fd }).then(() => {
            document.getElementById('c-p').innerText = document.querySelectorAll('.active-p').length;
            document.getElementById('c-a').innerText = document.querySelectorAll('.active-a').length;
            document.getElementById('c-l').innerText = document.querySelectorAll('.active-l').length;
        });
    }

    function openHistory(enroll, name) {
        document.getElementById('hTitle').innerText = name + "'s 30-Day History";
        document.getElementById('hModal').style.display = "block";
        document.getElementById('hBody').innerHTML = "<p style='text-align:center; padding:20px; color:#555;'>Loading records...</p>";
        
        fetch('get_attendance_history.php?enroll=' + enroll)
            .then(r => r.text())
            .then(html => { document.getElementById('hBody').innerHTML = html; });
    }

    function closeModal() { document.getElementById('hModal').style.display = "none"; }
    window.onclick = function(e) { if(e.target == document.getElementById('hModal')) closeModal(); }
</script>
</body>
</html>