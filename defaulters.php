<?php
session_start();

// Database Configuration
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

// Security Check
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

// Logic: Date and Month
$currentMonth = date('F'); // e.g., January
$currentDay = (int)date('j'); 
$prevMonth = date('F', strtotime("last month"));

// 1. Auto-Fine Logic: After 15th, add Rs. 200
$autoFine = ($currentDay > 15) ? 200 : 0;

// 2. Fetch Defaulters with Logic:
// - Jo current month mein 'Paid' nahi hain
// - Unka pichle mahine ka balance (Arrears) bhi calculate hoga
$query = "SELECT 
            s.stu_enrollment_number, 
            s.stu_name, 
            s.stu_class, 
            s.stu_wing,
            -- Current Month Fee from 'fees' table
            (SELECT amount_paid FROM fees WHERE student_enrollment = s.stu_enrollment_number AND month_for = :currentMonth LIMIT 1) as current_fee,
            -- Previous Month Pending Fee (Arrears)
            (SELECT SUM(amount_paid) FROM fees WHERE student_enrollment = s.stu_enrollment_number AND month_for != :currentMonth AND status = 'Pending') as arrears
          FROM students s
          WHERE s.stu_enrollment_number NOT IN (
              SELECT student_enrollment FROM fees 
              WHERE month_for = :currentMonth AND status = 'Paid'
          )
          ORDER BY s.stu_wing ASC, s.stu_class ASC";

$stmt = $conn->prepare($query);
$stmt->execute(['currentMonth' => $currentMonth]);
$defaulters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Defaulters & Fine Manager | BCI</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --danger: #ff4d4d;
            --warning: #ffcc00;
            --primary: #00d4ff;
            --success: #00ff88;
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        body {
            background: linear-gradient(rgba(0,0,0,0.92), rgba(0,0,0,0.92)), url('./assets/img/bg_6.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            color: white;
            padding: 40px 20px; margin: 0; min-height: 100vh;
        }

        .container { max-width: 1200px; margin: 0 auto; }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            padding: 20px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .icon-box {
            width: 50px; height: 50px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }

        .table-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 25px;
            padding: 25px;
            overflow-x: auto;
        }

        table { width: 100%; border-collapse: collapse; text-align: left; min-width: 800px; }
        th { padding: 15px; color: var(--primary); font-size: 11px; text-transform: uppercase; border-bottom: 2px solid var(--glass-border); }
        td { padding: 18px 15px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 14px; }

        .badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .badge-danger { background: rgba(255, 77, 77, 0.2); color: var(--danger); }
        .badge-warning { background: rgba(255, 204, 0, 0.2); color: var(--warning); }
        
        .btn-notify {
            background: var(--danger);
            color: white; border: none; padding: 10px 20px; border-radius: 10px;
            font-size: 12px; font-weight: 600; cursor: pointer; transition: 0.3s;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-notify:hover { transform: scale(1.05); background: white; color: black; }

        .fine-text { color: var(--danger); font-weight: bold; }
        .total-text { color: var(--success); font-weight: 800; font-size: 16px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h1 style="margin:0; letter-spacing: 2px; font-weight: 800;">DEFAULTERS CONTROL</h1>
            <p style="color: var(--primary); font-size: 13px;">System Date: <?php echo date('d M, Y'); ?> | <span class="fine-text">Fine Mode: <?php echo ($autoFine > 0) ? 'ACTIVE' : 'INACTIVE'; ?></span></p>
        </div>
        <a href="fee_dashboard.php" style="color: #888; text-decoration: none; display: flex; align-items: center; gap: 5px;">
            <i data-lucide="arrow-left-circle"></i> Back to Dashboard
        </a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon-box" style="background: var(--danger);"><i data-lucide="user-x"></i></div>
            <div>
                <h2 style="margin:0;"><?php echo count($defaulters); ?></h2>
                <p style="margin:0; font-size: 11px; opacity: 0.6;">Total Defaulters</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon-box" style="background: var(--warning);"><i data-lucide="alert-triangle"></i></div>
            <div>
                <h2 style="margin:0;">Rs. <?php echo $autoFine; ?></h2>
                <p style="margin:0; font-size: 11px; opacity: 0.6;">Current Daily Fine</p>
            </div>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Student Info</th>
                    <th>Wing / Class</th>
                    <th>Current Fee</th>
                    <th>Arrears (Prev)</th>
                    <th>Late Fine</th>
                    <th>Total Payable</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($defaulters) > 0): ?>
                    <?php foreach($defaulters as $row): 
                        $current = $row['current_fee'] ?? 0;
                        $arrears = $row['arrears'] ?? 0;
                        $total = $current + $arrears + $autoFine;
                    ?>
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: white;">#<?php echo $row['stu_enrollment_number']; ?></div>
                            <div style="font-size: 12px; opacity: 0.7;"><?php echo htmlspecialchars($row['stu_name']); ?></div>
                        </td>
                        <td>
                            <span class="badge" style="background: rgba(0, 212, 255, 0.1); color: var(--primary);"><?php echo $row['stu_wing']; ?></span>
                            <div style="margin-top:5px; font-size: 12px;">Class: <?php echo $row['stu_class']; ?></div>
                        </td>
                        <td>Rs. <?php echo number_format($current); ?></td>
                        <td style="color: var(--warning);"><?php echo ($arrears > 0) ? 'Rs. '.number_format($arrears) : 'Nil'; ?></td>
                        <td class="fine-text"><?php echo ($autoFine > 0) ? '+ Rs. '.$autoFine : 'No Fine'; ?></td>
                        <td class="total-text">Rs. <?php echo number_format($total); ?></td>
                        <td>
                            <button class="btn-notify">
                                <i data-lucide="bell-ring" style="width:14px"></i> Send Alert
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 60px;">
                            <i data-lucide="check-circle" style="width: 50px; height: 50px; color: var(--success); opacity: 0.5;"></i>
                            <p style="margin-top: 15px; font-size: 18px; color: #888;">No defaulters found! All dues are cleared.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>