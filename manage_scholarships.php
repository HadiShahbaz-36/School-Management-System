<?php
session_start();

// Security & Database
if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'fee_manager') {
    header('Location: index.php'); exit();
}

$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_DB";
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Database error."); }

// AJAX Search for Student
if (isset($_GET['check_id'])) {
    $stmt = $conn->prepare("SELECT stu_name, stu_class, stu_photo FROM students WHERE stu_enrollment_number = ?");
    $stmt->execute([$_GET['check_id']]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $res ? json_encode($res) : json_encode(['error' => 'Not Found']);
    exit();
}

// Handle Delete/Discard
if (isset($_GET['discard'])) {
    $stmt = $conn->prepare("DELETE FROM scholarships WHERE id = ?");
    $stmt->execute([$_GET['discard']]);
    header("Location: manage_scholarships.php"); exit();
}

// Handle Add or Update
if (isset($_POST['grant'])) {
    if (!empty($_POST['edit_id'])) {
        $stmt = $conn->prepare("UPDATE scholarships SET reason = ?, discount_percentage = ? WHERE id = ?");
        $stmt->execute([$_POST['reason'], $_POST['discount'], $_POST['edit_id']]);
    } else {
        $stmt = $conn->prepare("INSERT INTO scholarships (stu_enrollment_number, reason, discount_percentage) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['stu_id'], $_POST['reason'], $_POST['discount']]);
    }
    header("Location: manage_scholarships.php"); exit();
}

$scholarships = $conn->query("SELECT s.*, st.stu_name, st.stu_class FROM scholarships s JOIN students st ON s.stu_enrollment_number = st.stu_enrollment_number ORDER BY s.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scholarship Control | BCI</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --bg: #000000; --card: #0a0a0a; --primary: #00d4ff; 
            --accent: #ffcc00; --danger: #ff4444; --glass: rgba(255, 255, 255, 0.03);
        }

        body {
            background: var(--bg) url('./assets/img/bg_6.png') no-repeat center center fixed;
            background-size: cover; font-family: 'Inter', sans-serif; color: #eee; margin: 0; padding: 20px;
        }

        .main-wrapper { max-width: 1100px; margin: 40px auto; animation: slideIn 0.5s ease; }

        /* Professional Back Nav */
        .nav-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .back-link { 
            text-decoration: none; color: #555; font-size: 12px; font-weight: 700; 
            display: flex; align-items: center; gap: 8px; transition: 0.3s;
            letter-spacing: 1px;
        }
        .back-link:hover { color: var(--primary); }

        .top-bar { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
        
        .fab-add {
            background: none; color: var(--primary); border: 1px solid var(--primary); padding: 10px 20px;
            border-radius: 6px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 10px;
            transition: 0.3s; text-transform: uppercase; font-size: 12px;
        }
        .fab-add:hover { background: var(--primary); color: #000; box-shadow: 0 0 15px rgba(0, 212, 255, 0.3); }

        .list-card { background: var(--card); border-radius: 12px; border: 1px solid #151515; padding: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.8); }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: #333; font-size: 10px; text-transform: uppercase; letter-spacing: 2px; padding: 15px; border-bottom: 1px solid #111; }
        tr.row-item { transition: 0.2s; border-bottom: 1px solid #0f0f0f; }
        tr.row-item:hover { background: #080808; }
        td { padding: 15px; }

        .discount-tag { color: var(--accent); font-weight: 800; border: 1px solid rgba(255, 204, 0, 0.3); padding: 3px 8px; border-radius: 4px; font-size: 11px; background: rgba(255, 204, 0, 0.05); }

        /* Modal */
        .modal {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.95);
            backdrop-filter: blur(10px); z-index: 1000; align-items: center; justify-content: center;
        }
        .modal-content {
            background: #050505; width: 100%; max-width: 400px; padding: 40px;
            border-radius: 12px; border: 1px solid #1a1a1a; position: relative;
        }

        input, textarea {
            width: 100%; padding: 12px; background: #000; border: 1px solid #1a1a1a;
            border-radius: 6px; color: white; margin-top: 8px; margin-bottom: 20px; box-sizing: border-box;
        }
        input:focus { border-color: var(--primary); outline: none; }

        .action-btn { color: #444; transition: 0.3s; margin-left: 12px; cursor: pointer; }
        .action-btn:hover { color: var(--primary); }
        .btn-del:hover { color: var(--danger); }

        @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="nav-header">
        <a href="fee_dashboard.php" class="back-link">
            <i data-lucide="chevron-left" size="16"></i> BACK TO TERMINAL
        </a>
    </div>

    <div class="top-bar">
        <div>
            <h1 style="margin:0; font-size: 30px; font-weight: 900; letter-spacing: -1px;">SCHOLARSHIP <span style="color:var(--primary)">VAULT</span></h1>
            <p style="color:#444; margin: 2px 0 0; font-size: 13px;">Manage institutional fee waivers and grants</p>
        </div>
        <button class="fab-add" onclick="openAddModal()">
            <i data-lucide="plus" size="18"></i> ISSUE NEW GRANT
        </button>
    </div>

    <div class="list-card">
        <table>
            <thead>
                <tr>
                    <th>Student Info</th>
                    <th>Grant Logic / Reason</th>
                    <th>Status</th>
                    <th style="text-align:right">Control</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($scholarships as $s): ?>
                <tr class="row-item">
                    <td>
                        <div style="font-weight: 600; color: #fff; font-size: 14px;"><?php echo $s['stu_name']; ?></div>
                        <div style="font-size: 11px; color: #555;"><?php echo $s['stu_enrollment_number']; ?></div>
                    </td>
                    <td style="color:#777; font-size: 13px;"><?php echo $s['reason']; ?></td>
                    <td><span class="discount-tag"><?php echo $s['discount_percentage']; ?>% OFF</span></td>
                    <td style="text-align:right">
                        <span class="action-btn" onclick='openEditModal(<?php echo json_encode($s); ?>)'>
                            <i data-lucide="pencil-line" size="18"></i>
                        </span>
                        <a href="?discard=<?php echo $s['id']; ?>" class="action-btn btn-del" onclick="return confirm('Revoke this grant?')">
                            <i data-lucide="trash-2" size="18"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="grantModal" class="modal">
    <div class="modal-content">
        <i data-lucide="x" style="position:absolute; top:20px; right:20px; cursor:pointer; color:#444;" onclick="closeModal()"></i>
        <h3 id="modalTitle" style="margin-top:0; color:var(--primary); font-size: 20px;">Issue Grant</h3>
        
        <form method="POST">
            <input type="hidden" name="edit_id" id="edit_id">
            
            <div id="id_field_group">
                <label style="font-size: 10px; color:#444; font-weight:700;">ENROLLMENT ID</label>
                <input type="text" name="stu_id" id="stu_id_input" placeholder="BCI-XXX" required autocomplete="off">
            </div>

            <label style="font-size: 10px; color:#444; font-weight:700;">REASON</label>
            <textarea name="reason" id="reason_input" rows="2" placeholder="e.g. Merit-based" required></textarea>

            <label style="font-size: 10px; color:#444; font-weight:700;">WAIVER %</label>
            <input type="number" name="discount" id="discount_input" min="1" max="100" placeholder="0" required>

            <button type="submit" name="grant" class="fab-add" style="width:100%; justify-content:center; background:var(--primary); color:#000;">SAVE CHANGES</button>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('grantModal');
    
    function openAddModal() {
        document.getElementById('modalTitle').innerText = "Issue New Grant";
        document.getElementById('edit_id').value = "";
        document.getElementById('id_field_group').style.display = "block";
        document.getElementById('stu_id_input').required = true;
        modal.style.display = 'flex';
    }

    function openEditModal(data) {
        document.getElementById('modalTitle').innerText = "Modify Grant";
        document.getElementById('edit_id').value = data.id;
        document.getElementById('reason_input').value = data.reason;
        document.getElementById('discount_input').value = data.discount_percentage;
        document.getElementById('id_field_group').style.display = "none";
        document.getElementById('stu_id_input').required = false;
        modal.style.display = 'flex';
    }

    function closeModal() { modal.style.display = 'none'; }

    lucide.createIcons();
</script>
</body>
</html>