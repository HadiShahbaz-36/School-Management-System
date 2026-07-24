<?php
session_start();
$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_db";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Database connection failed."); }

if (!isset($_SESSION['username'])) { header('Location: index.php'); exit(); }

$wings = ['Lower Primary', 'Upper Primary', 'Boys wing', 'Girls wing', 'Cambridge', 'Special education'];
$categories = ['CIV', 'PN CIV', 'PN SAILOR', 'ARMY', 'STAFF', 'PNET', 'PAF', 'SPD', 'PN', 'Faculty'];

$msg = "";

// 1. UPDATE MASTER RATES 
if (isset($_POST['update_master_rates'])) {
    foreach ($_POST['fee'] as $wing => $cats) {
        foreach ($cats as $cat => $vals) {
            $stmt = $conn->prepare("INSERT INTO fee_structure (wing_name, category_name, amount, allied_charges, annual_charges) 
                                   VALUES (?, ?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE amount = ?, allied_charges = ?, annual_charges = ?");
            $stmt->execute([
                $wing, $cat, $vals['tuition'], $vals['allied'], $vals['annual'],
                $vals['tuition'], $vals['allied'], $vals['annual']
            ]);
        }
    }
    $msg = "Master Rates Synchronized!";
}

// 2. GENERATE INVOICES (Scholarship on Tuition Only Logic)
if (isset($_POST['generate_invoices'])) {
    $month = date('F');
    try {
        $conn->beginTransaction();
        $conn->exec("UPDATE students SET stu_fee_status = 'Unpaid'");

        $sql = "INSERT INTO fees (student_enrollment, amount_paid, month_for, status)
                SELECT 
                    s.stu_enrollment_number, 
                    (
                      (fs.amount - (fs.amount * IFNULL(sc.discount_percentage, 0) / 100)) 
                      + fs.allied_charges 
                      + fs.annual_charges
                      + IFNULL((SELECT SUM(amount) FROM fee_adjustments WHERE stu_enrollment_number = s.stu_enrollment_number AND adj_type = 'Fine' AND (status = 'Pending' OR status = 'Applied')), 0)
                      - IFNULL((SELECT SUM(amount) FROM fee_adjustments WHERE stu_enrollment_number = s.stu_enrollment_number AND adj_type = 'Concession' AND (status = 'Pending' OR status = 'Applied')), 0)
                    ) as final_amount, 
                    '$month', 
                    'Pending'
                FROM students s
                JOIN fee_structure fs ON s.stu_wing = fs.wing_name AND s.fee_category = fs.category_name
                LEFT JOIN scholarships sc ON s.stu_enrollment_number = sc.stu_enrollment_number
                ON DUPLICATE KEY UPDATE amount_paid = VALUES(amount_paid)";
        
        $conn->exec($sql);
        $conn->exec("UPDATE fee_adjustments SET status = 'Applied' WHERE status = 'Pending'");
        $conn->commit();
        $msg = "Invoices Generated for $month Successfully!";
    } catch (Exception $e) { $conn->rollBack(); $msg = "Error: " . $e->getMessage(); }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fee Control Center | BCI</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { 
            --primary: #00d4ff; 
            --secondary: #0055ff;
            --bg: #05070a; 
            --card: rgba(255, 255, 255, 0.03); 
            --border: rgba(255, 255, 255, 0.08); 
        }

        body { 
            background: var(--bg); 
            background-image: radial-gradient(circle at 50% -20%, #003366 0%, transparent 50%);
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: white; margin: 0; padding: 0; 
            min-height: 100vh;
        }

        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }

        /* Header Styling */
        .header-section { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 50px; }
        .header-section h1 { font-size: 38px; font-weight: 800; margin: 0; letter-spacing: -1px; }
        .header-section span { color: var(--primary); font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 4px; }

        /* Accordion Effects */
        .wing-card { 
            background: var(--card); 
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 24px; margin-bottom: 20px; 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .wing-card:hover { border-color: rgba(0, 212, 255, 0.3); box-shadow: 0 20px 40px rgba(0,0,0,0.4); }

        .acc-trigger { 
            padding: 25px 30px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; 
        }
        .acc-trigger h3 { margin: 0; font-size: 18px; display: flex; align-items: center; gap: 12px; }

        .acc-content { 
            max-height: 0; overflow: hidden; transition: max-height 0.5s ease-out; padding: 0 30px; 
        }
        .wing-card.active .acc-content { max-height: 2000px; padding-bottom: 30px; }
        .wing-card.active .chevron { transform: rotate(180deg); color: var(--primary); }

        /* Grid & Inputs */
        .fee-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .fee-item { 
            background: rgba(255,255,255,0.02); padding: 20px; border-radius: 18px; 
            border: 1px solid rgba(255,255,255,0.05); transition: 0.3s;
        }
        .fee-item:focus-within { background: rgba(0, 212, 255, 0.05); border-color: var(--primary); }
        
        .cat-label { font-weight: 700; font-size: 14px; margin-bottom: 15px; display: block; color: #fff; }
        
        .fields { display: flex; gap: 10px; }
        .field-box { flex: 1; }
        .field-box label { font-size: 10px; color: #666; font-weight: 600; text-transform: uppercase; margin-bottom: 5px; display: block; }
        .field-box input { 
            width: 100%; background: #000; border: 1px solid #222; color: var(--primary); 
            padding: 10px; border-radius: 10px; font-weight: 700; outline: none; transition: 0.3s;
        }
        .field-box input:focus { border-color: var(--primary); box-shadow: 0 0 10px rgba(0,212,255,0.2); }

        /* Buttons */
        .btn-group { display: flex; gap: 15px; margin-top: 40px; }
        .btn { 
            padding: 18px 35px; border-radius: 16px; font-weight: 800; cursor: pointer; 
            transition: all 0.3s; display: flex; align-items: center; gap: 10px; font-size: 14px;
        }
        .btn-save { background: transparent; border: 1px solid var(--primary); color: var(--primary); }
        .btn-save:hover { background: var(--primary); color: #000; }

        .btn-gen { 
            background: linear-gradient(135deg, var(--primary), var(--secondary)); 
            border: none; color: #000; flex: 1; justify-content: center;
            box-shadow: 0 10px 20px rgba(0, 212, 255, 0.2);
        }
        .btn-gen:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(0, 212, 255, 0.4); }

        .status-msg { 
            background: rgba(0, 212, 255, 0.1); border-left: 4px solid var(--primary); 
            padding: 15px 25px; border-radius: 12px; margin-bottom: 30px; animation: fadeIn 0.5s;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="container">
    <div class="header-section">
        <div>
            <span>Financial Management</span>
            <h1>FEE STRUCTURE <span style="font-weight:300">HUB</span></h1>
        </div>
        <a href="fee_dashboard.php" style="color:#666; text-decoration:none; font-weight:700; font-size:12px;">EXIT TERMINAL</a>
    </div>

    <?php if($msg): ?>
        <div class="status-msg"><i data-lucide="info" size="18" style="vertical-align:middle; margin-right:10px;"></i> <?php echo $msg; ?></div>
    <?php endif; ?>

    <form method="POST">
        <?php foreach ($wings as $wing): ?>
        <div class="wing-card" id="card-<?php echo md5($wing); ?>">
            <div class="acc-trigger" onclick="toggleCard('card-<?php echo md5($wing); ?>')">
                <h3><i data-lucide="component" size="20" style="color:var(--primary)"></i> <?php echo $wing; ?></h3>
                <i data-lucide="chevron-down" class="chevron" style="transition:0.4s"></i>
            </div>
            <div class="acc-content">
                <div class="fee-grid">
                    <?php foreach ($categories as $cat): 
                        $st = $conn->prepare("SELECT amount, allied_charges, annual_charges FROM fee_structure WHERE wing_name = ? AND category_name = ?");
                        $st->execute([$wing, $cat]);
                        $row = $st->fetch(PDO::FETCH_ASSOC) ?: ['amount'=>0, 'allied_charges'=>0, 'annual_charges'=>0];
                    ?>
                    <div class="fee-item">
                        <span class="cat-label"><?php echo $cat; ?></span>
                        <div class="fields">
                            <div class="field-box">
                                <label>Tuition</label>
                                <input type="number" name="fee[<?php echo $wing; ?>][<?php echo $cat; ?>][tuition]" value="<?php echo $row['amount']; ?>">
                            </div>
                            <div class="field-box">
                                <label>Allied</label>
                                <input type="number" name="fee[<?php echo $wing; ?>][<?php echo $cat; ?>][allied]" value="<?php echo $row['allied_charges']; ?>">
                            </div>
                            <div class="field-box">
                                <label>Annual</label>
                                <input type="number" name="fee[<?php echo $wing; ?>][<?php echo $cat; ?>][annual]" value="<?php echo $row['annual_charges']; ?>">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="btn-group">
            <button type="submit" name="update_master_rates" class="btn btn-save">
                <i data-lucide="refresh-cw" size="18"></i> SYNC MASTER RATES
            </button>
            <button type="submit" name="generate_invoices" class="btn btn-gen">
                <i data-lucide="zap" size="20"></i> DEPLOY MONTHLY INVOICES
            </button>
        </div>
    </form>
</div>

<script>
    lucide.createIcons();
    function toggleCard(id) {
        const el = document.getElementById(id);
        el.classList.toggle('active');
    }
</script>
</body>
</html>