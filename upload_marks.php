<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') { 
    header('Location: index.php'); exit(); 
}

$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_DB";
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("DB Fail"); }

$selected_class = $_GET['class'] ?? 'N/A';

// --- DELETE LOGIC ---
if (isset($_GET['del_id'])) {
    $del_stmt = $conn->prepare("DELETE FROM student_marks WHERE mark_id = ?");
    $del_stmt->execute([$_GET['del_id']]);
    echo "<script>alert('Record Deleted!'); window.location.href='upload_marks.php?class=$selected_class';</script>";
}

// FETCH STUDENTS FOR DROPDOWN
$stmt = $conn->prepare("SELECT stu_name, stu_enrollment_number FROM students WHERE stu_class = ?");
$stmt->execute([$selected_class]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// SAVE / UPDATE MARKS LOGIC
if(isset($_POST['save_marks'])){
    $enroll = $_POST['student_enroll'];
    $term = $_POST['term'];
    $subjects = $_POST['subject'];
    $totals = $_POST['total'];
    $obtains = $_POST['obtained'];

    for($i=0; $i < count($subjects); $i++){
        if(!empty($subjects[$i])){
            $perc = ($obtains[$i] / $totals[$i]) * 100;
            // UPDATE if exists for same student+term+subject, else INSERT
            $check = $conn->prepare("SELECT mark_id FROM student_marks WHERE enrollment_number=? AND term_name=? AND subject_name=?");
            $check->execute([$enroll, $term, $subjects[$i]]);
            $existing = $check->fetch();

            if($existing) {
                $upd = $conn->prepare("UPDATE student_marks SET total_marks=?, obtained_marks=?, percentage=? WHERE mark_id=?");
                $upd->execute([$totals[$i], $obtains[$i], $perc, $existing['mark_id']]);
            } else {
                $ins = $conn->prepare("INSERT INTO student_marks (enrollment_number, term_name, subject_name, total_marks, obtained_marks, percentage) VALUES (?,?,?,?,?,?)");
                $ins->execute([$enroll, $term, $subjects[$i], $totals[$i], $obtains[$i], $perc]);
            }
        }
    }
    echo "<script>alert('Marks Saved Successfully!'); window.location.href='upload_marks.php?class=$selected_class';</script>";
}

// FETCH EXISTING MARKS FOR DISPLAY
$view_marks = $conn->prepare("SELECT * FROM student_marks WHERE enrollment_number IN (SELECT stu_enrollment_number FROM students WHERE stu_class = ?) ORDER BY upload_date DESC LIMIT 50");
$view_marks->execute([$selected_class]);
$existing_data = $view_marks->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Marks | Premium</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* APKI EXISTING CSS IDHAR HAI - PRESERVED */
        @import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap");
        :root { --primary: #00d4ff; --secondary: #00ff88; --bg: #050505; --card-bg: rgba(255, 255, 255, 0.03); --border: rgba(255, 255, 255, 0.1); }
        body { background: var(--bg); color: white; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; padding: 40px 20px; min-height: 100vh; background: radial-gradient(circle at top right, #001a1a, transparent), radial-gradient(circle at bottom left, #1a001a, transparent); }
        .container { max-width: 1000px; margin: auto; background: var(--card-bg); backdrop-filter: blur(20px); padding: 40px; border-radius: 24px; border: 1px solid var(--border); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
        h2 { font-size: 2rem; font-weight: 700; background: linear-gradient(to right, #fff, var(--primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; display: flex; align-items: center; gap: 15px; margin-bottom: 30px;}
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px; }
        label { display: block; margin-bottom: 10px; font-size: 0.9rem; color: #aaa; font-weight: 600; }
        select, input { background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: white; padding: 14px; border-radius: 12px; width: 100%; outline: none; transition: 0.3s; }
        select:focus, input:focus { border-color: var(--primary); background: rgba(255,255,255,0.1); }
        table { width: 100%; border-spacing: 0 10px; margin-top: 20px; }
        th { text-align: left; padding: 10px 15px; color: #666; text-transform: uppercase; font-size: 0.75rem; }
        .subject-row { background: rgba(255,255,255,0.02); }
        .btn-add { background: transparent; color: var(--secondary); border: 1px dashed var(--secondary); padding: 12px 25px; border-radius: 12px; cursor: pointer; font-weight: 600; margin-top: 20px; display: flex; align-items: center; gap: 8px; }
        .summary-card { margin-top: 40px; background: linear-gradient(135deg, rgba(0, 212, 255, 0.1), rgba(0, 255, 136, 0.1)); padding: 25px; border-radius: 20px; display: flex; justify-content: space-around; border: 1px solid var(--border); }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: var(--primary); }
        .btn-submit { background: var(--primary); color: black; border: none; padding: 18px; width: 100%; border-radius: 15px; font-weight: 800; cursor: pointer; margin-top: 30px; text-transform: uppercase; box-shadow: 0 10px 20px rgba(0, 212, 255, 0.3); }

        /* NEW: History Table CSS */
        .history-section { margin-top: 60px; border-top: 1px solid var(--border); pt: 40px; }
        .history-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.85rem; }
        .history-table th { background: rgba(255,255,255,0.05); padding: 15px; color: var(--primary); }
        .history-table td { padding: 12px 15px; border-bottom: 1px solid var(--border); }
        .action-btn { cursor: pointer; padding: 5px; border-radius: 5px; transition: 0.2s; }
        .btn-edit { color: var(--secondary); }
        .btn-del { color: #ff4d4d; }
        .action-btn:hover { background: rgba(255,255,255,0.1); }
    </style>
</head>
<body>

<div class="container">
    <h2><i data-lucide="graduation-cap"></i> Manage Result: <?php echo $selected_class; ?></h2>
    
    <form method="POST" id="mainForm">
        <div class="form-row">
            <div>
                <label><i data-lucide="user" style="width:14px"></i> Target Student</label>
                <select name="student_enroll" id="student_enroll" required>
                    <?php foreach($students as $s): ?>
                        <option value="<?php echo $s['stu_enrollment_number']; ?>">
                            <?php echo $s['stu_name']; ?> (<?php echo $s['stu_enrollment_number']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label><i data-lucide="calendar" style="width:14px"></i> Select Term</label>
                <select name="term" id="term_name" required>
                    <option>First Assessments</option>
                    <option>First Term Exams</option>
                    <option>Second Assessments</option>
                    <option>Final Examination</option>
                </select>
            </div>
        </div>

        <table id="marksTable">
            <thead>
                <tr>
                    <th>Subject Name</th>
                    <th width="120">Total</th>
                    <th width="120">Obtained</th>
                </tr>
            </thead>
            <tbody>
                <tr class="subject-row">
                    <td><input type="text" name="subject[]" id="sub_1" placeholder="Subject Name" required></td>
                    <td><input type="number" name="total[]" id="total_1" class="t-marks" value="100" required oninput="calculate()"></td>
                    <td><input type="number" name="obtained[]" id="obtain_1" class="o-marks" placeholder="0" required oninput="calculate()"></td>
                </tr>
            </tbody>
        </table>
        
        <button type="button" class="btn-add" onclick="addRow()">
            <i data-lucide="plus-circle"></i> Add Subject
        </button>

        <div class="summary-card">
            <div class="stat-item"><div class="stat-label">Grand Total</div><div class="stat-value" id="grandTotal">100</div></div>
            <div class="stat-item"><div class="stat-label">Obtained</div><div class="stat-value" id="grandObtained">0</div></div>
            <div class="stat-item"><div class="stat-label">Aggregate</div><div class="stat-value"><span id="grandPerc">0</span>%</div></div>
        </div>

        <button type="submit" name="save_marks" class="btn-submit">Publish Result</button>
    </form>

    <div class="history-section">
        <h3 style="color: #fff; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="history"></i> Recent Records
        </h3>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Term</th>
                    <th>Subject</th>
                    <th>Marks</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($existing_data as $row): ?>
                <tr>
                    <td><?php echo $row['enrollment_number']; ?></td>
                    <td><?php echo $row['term_name']; ?></td>
                    <td><?php echo $row['subject_name']; ?></td>
                    <td style="font-weight:bold"><?php echo $row['obtained_marks']; ?> / <?php echo $row['total_marks']; ?></td>
                    <td style="color:#666"><?php echo date('d M', strtotime($row['upload_date'])); ?></td>
                    <td>
                        <i data-lucide="edit" class="action-btn btn-edit" onclick="editMark('<?php echo $row['enrollment_number']; ?>', '<?php echo $row['term_name']; ?>', '<?php echo $row['subject_name']; ?>', '<?php echo $row['total_marks']; ?>', '<?php echo $row['obtained_marks']; ?>')"></i>
                        <a href="?class=<?php echo $selected_class; ?>&del_id=<?php echo $row['mark_id']; ?>" onclick="return confirm('Delete this record?')">
                            <i data-lucide="trash-2" class="action-btn btn-del"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    lucide.createIcons();

    function addRow() {
        const tbody = document.querySelector("#marksTable tbody");
        const row = document.createElement("tr");
        row.className = "subject-row";
        row.innerHTML = `
            <td><input type="text" name="subject[]" placeholder="Subject Name"></td>
            <td><input type="number" name="total[]" class="t-marks" value="100" oninput="calculate()"></td>
            <td><input type="number" name="obtained[]" class="o-marks" placeholder="0" oninput="calculate()"></td>
        `;
        tbody.appendChild(row);
        calculate();
    }

    function calculate() {
        let totals = document.getElementsByClassName('t-marks');
        let obtains = document.getElementsByClassName('o-marks');
        let gTotal = 0; let gObtain = 0;
        for(let i=0; i<totals.length; i++) {
            gTotal += Number(totals[i].value) || 0;
            gObtain += Number(obtains[i].value) || 0;
        }
        document.getElementById('grandTotal').innerText = gTotal;
        document.getElementById('grandObtained').innerText = gObtain;
        let perc = gTotal > 0 ? (gObtain / gTotal) * 100 : 0;
        document.getElementById('grandPerc').innerText = perc.toFixed(1);
    }

    // NEW: Edit Functionality
    function editMark(enroll, term, sub, total, obtain) {
        document.getElementById('student_enroll').value = enroll;
        document.getElementById('term_name').value = term;
        document.getElementById('sub_1').value = sub;
        document.getElementById('total_1').value = total;
        document.getElementById('obtain_1').value = obtain;
        calculate();
        document.getElementById('mainForm').scrollIntoView({ behavior: 'smooth' });
        // Visual indicator
        document.querySelector('.btn-submit').innerText = "Update & Re-Publish";
        document.querySelector('.btn-submit').style.background = "var(--secondary)";
    }
</script>
</body>
</html>