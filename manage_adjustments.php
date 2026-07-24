<?php
session_start();
if (!isset($_SESSION['username'])) { header('Location: index.php'); exit(); }

$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_db";
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Database error."); }

// --- 1. BULK FINE LOGIC ---
if (isset($_POST['apply_bulk'])) {
    $target = $_POST['bulk_target'];
    $target_val = $_POST['target_value'];
    $amount = $_POST['amount'];
    $reason = $_POST['reason'];
    $months = $_POST['total_months'];

    $sql = "INSERT INTO fee_adjustments (stu_enrollment_number, adj_type, amount, reason, total_months, months_applied) 
            SELECT stu_enrollment_number, 'Fine', ?, ?, ?, 0 FROM students WHERE stu_status = 'Active'";

    if ($target == 'class') {
        $sql .= " AND stu_class = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$amount, $reason, $months, $target_val]);
    } elseif ($target == 'wing') {
        $sql .= " AND stu_wing = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$amount, $reason, $months, $target_val]);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$amount, $reason, $months]);
    }
    header("Location: manage_adjustments.php?bulk=success"); exit();
}

// --- 2. AJAX LIVE SEARCH ---
if (isset($_GET['search_id'])) {
    $stmt = $conn->prepare("SELECT stu_name, stu_photo, stu_class FROM students WHERE stu_enrollment_number = ?");
    $stmt->execute([$_GET['search_id']]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $res ? json_encode($res) : json_encode(['error' => 'Not Found']);
    exit();
}

// --- 3. SAVE / UPDATE ---
if (isset($_POST['save_adjustment'])) {
    if (!empty($_POST['adj_id'])) {
        $stmt = $conn->prepare("UPDATE fee_adjustments SET adj_type=?, amount=?, reason=?, total_months=? WHERE id=?");
        $stmt->execute([$_POST['adj_type'], $_POST['amount'], $_POST['reason'], $_POST['total_months'], $_POST['adj_id']]);
    } else {
        $stmt = $conn->prepare("INSERT INTO fee_adjustments (stu_enrollment_number, adj_type, amount, reason, total_months, months_applied) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->execute([$_POST['stu_id'], $_POST['adj_type'], $_POST['amount'], $_POST['reason'], $_POST['total_months']]);
    }
    header("Location: manage_adjustments.php?status=success"); exit();
}

// --- 4. DELETE ---
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM fee_adjustments WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: manage_adjustments.php"); exit();
}

$adjustments = $conn->query("SELECT a.*, s.stu_name, s.stu_class FROM fee_adjustments a JOIN students s ON a.stu_enrollment_number = s.stu_enrollment_number ORDER BY a.id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial Intel | BCI Terminal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary: #00d4ff; --fine: #ff4d4d; --con: #00ff88;
            --bg: #050505; --panel: rgba(20, 20, 20, 0.7); --border: rgba(255, 255, 255, 0.08);
        }
        body {
            background: var(--bg) radial-gradient(circle at 50% -20%, #003366 0%, transparent 50%);
            font-family: 'Plus Jakarta Sans', sans-serif; color: white; margin: 0; padding: 40px;
        }
        .terminal-grid { display: grid; grid-template-columns: 1fr 380px; gap: 30px; max-width: 1400px; margin: auto; }
        .glass-panel { background: var(--panel); backdrop-filter: blur(30px); border: 1px solid var(--border); border-radius: 24px; padding: 25px; box-shadow: 0 30px 60px rgba(0,0,0,0.4); margin-bottom: 20px; }
        
        /* Bulk Section Styling */
        .bulk-banner { background: linear-gradient(90deg, rgba(0,212,255,0.1), transparent); border-left: 4px solid var(--primary); padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; }

        .adj-card { background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border); border-radius: 15px; padding: 15px; margin-bottom: 12px; display: flex; align-items: center; gap: 15px; transition: 0.3s; }
        .adj-card:hover { transform: translateX(10px); border-color: var(--primary); }
        .icon-box { width: 45px; height: 45px; background: rgba(0,0,0,0.4); border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .info-group { flex: 1; }
        .stu-name { font-weight: 700; font-size: 14px; }
        .progress-group { width: 120px; }
        .progress-bg { height: 4px; background: #111; border-radius: 10px; margin-top: 5px; overflow: hidden; }
        .progress-fill { height: 100%; background: var(--primary); }

        input, select, textarea { width: 100%; padding: 12px; background: rgba(0,0,0,0.5); border: 1px solid var(--border); border-radius: 10px; color: white; margin-top: 5px; box-sizing: border-box; }
        .btn-glow { background: var(--primary); color: black; border: none; padding: 14px; border-radius: 12px; font-weight: 800; cursor: pointer; width: 100%; margin-top: 15px; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-bulk { background: #fff; color: #000; }
        
        .dropdown-menu { position: absolute; right: 0; background: #111; border: 1px solid var(--border); border-radius: 8px; display: none; z-index: 100; }
        .dropdown-menu a { display: block; padding: 10px 20px; color: #ccc; text-decoration: none; font-size: 12px; }
        .dropdown-menu a:hover { background: var(--primary); color: #000; }
    </style>
</head>
<body>

<div class="terminal-grid">
    <div class="left-side">
        <div class="glass-panel">
            <div class="bulk-banner">
                <h2 style="margin:0; font-size:18px;"><i data-lucide="layers" style="width:18px; vertical-align:middle;"></i> BULK TERMINAL DEPLOYMENT</h2>
            </div>
            <form method="POST">
                <div class="grid-3">
                    <div>
                        <label style="font-size:10px; color:var(--primary)">TARGET SCOPE</label>
                        <select name="bulk_target" id="bt" onchange="toggleVal()">
                            <option value="all">Full School</option>
                            <option value="wing">By Wing</option>
                            <option value="class">By Class</option>
                        </select>
                    </div>
                    <div id="val_box" style="display:none;">
                        <label style="font-size:10px; color:var(--primary)">WING/CLASS NAME</label>
                        <input type="text" name="target_value" placeholder="e.g. Boys Wing / 10-A">
                    </div>
                    <div>
                        <label style="font-size:10px; color:var(--primary)">FINE AMOUNT</label>
                        <input type="number" name="amount" placeholder="Rs.">
                    </div>
                </div>
                <div class="grid-3" style="margin-top:10px;">
                    <div>
                        <label style="font-size:10px; color:var(--primary)">CYCLE (MONTHS)</label>
                        <input type="number" name="total_months" value="1">
                    </div>
                    <div style="grid-column: span 2;">
                        <label style="font-size:10px; color:var(--primary)">REASON</label>
                        <input type="text" name="reason" placeholder="e.g. Late Fee / Damage Fine">
                    </div>
                </div>
                <button type="submit" name="apply_bulk" class="btn-glow btn-bulk" onclick="return confirm('Apply fine to all targeted students?')">
                    <i data-lucide="zap"></i> EXECUTE BULK AUTHORIZATION
                </button>
            </form>
        </div>

        <div class="glass-panel">
            <h3 style="margin-bottom:20px; font-size:16px;">LIVE VAULT <small style="opacity:0.5">(Recent 50)</small></h3>
            <div id="adj-list">
                <?php foreach($adjustments as $adj): 
                    $m_applied = (int)($adj['months_applied'] ?? 0);
                    $m_total = (int)($adj['total_months'] ?: 1);
                    $perc = ($m_applied / $m_total) * 100;
                ?>
                <div class="adj-card">
                    <div class="icon-box">
                        <i data-lucide="<?php echo ($adj['adj_type']=='Fine')?'alert-triangle':'sparkles'; ?>" 
                           style="color:<?php echo ($adj['adj_type']=='Fine')?'var(--fine)':'var(--con)'; ?>"></i>
                    </div>
                    <div class="info-group">
                        <div class="stu-name"><?php echo $adj['stu_name']; ?></div>
                        <div style="font-size:10px; color:#666;"><?php echo $adj['stu_enrollment_number']; ?> • <?php echo $adj['stu_class']; ?></div>
                    </div>
                    <div style="text-align:right; margin-right:20px;">
                        <div style="font-weight:900; color:<?php echo ($adj['adj_type']=='Fine')?'var(--fine)':'var(--con)'; ?>">
                            <?php echo ($adj['adj_type']=='Fine')?'+':'-'; ?> Rs. <?php echo number_format($adj['amount']); ?>
                        </div>
                    </div>
                    <div class="progress-group">
                        <div style="display:flex; justify-content:space-between; font-size:9px;"><span>Cycle</span><span><?php echo $m_applied; ?>/<?php echo $m_total; ?></span></div>
                        <div class="progress-bg"><div class="progress-fill" style="width:<?php echo $perc; ?>%"></div></div>
                    </div>
                    <div style="position:relative;">
                        <button onclick="toggleMenu(event, 'm-<?php echo $adj['id']; ?>')" style="background:none; border:none; color:#444; cursor:pointer;"><i data-lucide="more-vertical"></i></button>
                        <div class="dropdown-menu" id="m-<?php echo $adj['id']; ?>">
                            <a href="javascript:void(0)" onclick='editAdj(<?php echo json_encode($adj); ?>)'>Edit</a>
                            <a href="?delete=<?php echo $adj['id']; ?>" style="color:var(--fine)" onclick="return confirm('Delete?')">Delete</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="right-side">
        <div class="glass-panel" style="position: sticky; top: 40px;">
            <h3 id="form_title" style="margin:0 0 20px 0;">New Authorization</h3>
            <form method="POST">
                <input type="hidden" name="adj_id" id="adj_id">
                <div id="id_input_sec">
                    <label style="font-size:10px;">STUDENT ENROLLMENT</label>
                    <input type="text" name="stu_id" id="stu_id_input" placeholder="BCI-202X-01">
                </div>

                <div id="live_profile" style="display:none; margin:15px 0; background:rgba(255,255,255,0.03); padding:10px; border-radius:12px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <img id="p_img" src="" style="width:35px; height:35px; border-radius:8px; object-fit:cover;">
                        <div><b id="p_name" style="font-size:12px; color:var(--primary);"></b><br><small id="p_class" style="color:#666; font-size:10px;"></small></div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:15px;">
                    <select name="adj_type" id="live_type">
                        <option value="Fine">Fine (+)</option><option value="Concession">Concession (-)</option>
                    </select>
                    <input type="number" name="total_months" id="live_months" value="1" min="1">
                </div>

                <input type="number" name="amount" id="live_amount" placeholder="Monthly Amount" required>

                <div class="forecast-box">
                    <div style="font-size:10px; color:var(--primary); font-weight:800; margin-bottom:5px;">FORECAST</div>
                    <b id="total_val" style="font-size:15px;">Rs. 0</b>
                </div>

                <textarea name="reason" id="live_reason" placeholder="Reason..." style="height:60px; margin-top:10px;"></textarea>

                <button type="submit" name="save_adjustment" class="btn-glow"><i data-lucide="shield-check"></i> <span id="btn_text">AUTHORIZE</span></button>
                <button type="button" onclick="location.reload()" id="cancel_btn" style="display:none; background:none; border:none; color:#666; width:100%; margin-top:10px; cursor:pointer;">Cancel</button>
            </form>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
    
    function toggleVal() {
        const t = document.getElementById('bt').value;
        document.getElementById('val_box').style.display = (t === 'all') ? 'none' : 'block';
    }

    function toggleMenu(e, id) {
        e.stopPropagation();
        document.querySelectorAll('.dropdown-menu').forEach(m => { if(m.id !== id) m.style.display='none'; });
        const m = document.getElementById(id); m.style.display = (m.style.display==='block')?'none':'block';
    }

    function editAdj(data) {
        document.getElementById('adj_id').value = data.id;
        document.getElementById('live_type').value = data.adj_type;
        document.getElementById('live_amount').value = data.amount;
        document.getElementById('live_months').value = data.total_months;
        document.getElementById('live_reason').value = data.reason;
        document.getElementById('id_input_sec').style.display = 'none';
        document.getElementById('form_title').innerText = 'Update Record';
        document.getElementById('btn_text').innerText = 'SAVE';
        document.getElementById('cancel_btn').style.display = 'block';
        updateForecast();
    }

    document.getElementById('stu_id_input').addEventListener('input', function() {
        if (this.value.length >= 3) {
            fetch(`manage_adjustments.php?search_id=${this.value}`).then(r => r.json()).then(d => {
                if (!d.error) {
                    document.getElementById('p_name').innerText = d.stu_name;
                    document.getElementById('p_class').innerText = d.stu_class;
                    document.getElementById('p_img').src = "uploads/students/" + (d.stu_photo || 'default.png');
                    document.getElementById('live_profile').style.display = 'block';
                }
            });
        }
    });

    const updateForecast = () => {
        const total = (document.getElementById('live_amount').value || 0) * (document.getElementById('live_months').value || 1);
        document.getElementById('total_val').innerText = `Rs. ${total.toLocaleString()}`;
    }
    ['live_amount', 'live_months'].forEach(id => document.getElementById(id).addEventListener('input', updateForecast));
</script>
</body>
</html>