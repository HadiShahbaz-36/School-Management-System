<?php
session_start();

// Security: Check if Fee Manager is logged in
if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'fee_manager') {
    header('Location: index.php');
    exit();
}

$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_DB";
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Database Connection Fail"); }

// 1. Get Selected Month & Year (Default to Current)
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('F');
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// 2. Logic: Future Month Check
$selected_date_str = "01-$selected_month-$selected_year";
$filter_timestamp = strtotime($selected_date_str);
$current_month_start = strtotime(date('01-m-Y')); // Aaj ke mahine ki pehli tareekh

// Agar selected month aaj ke mahine se agay hai toh block karein
$is_future = ($filter_timestamp > $current_month_start) ? true : false;

$records = [];
if (!$is_future) {
    // 3. Simple Query: Fetch all students and join with fees table
    // Removed s.created_at to fix your error
    $sql = "SELECT s.stu_enrollment_number, s.stu_name, s.stu_class, s.stu_wing,
                   f.status, f.amount_paid, f.payment_date, f.fee_id
            FROM students s
            LEFT JOIN fees f ON s.stu_enrollment_number = f.student_enrollment 
            AND f.month_for = ? 
            AND YEAR(f.payment_date) = ?
            ORDER BY s.stu_class ASC, s.stu_name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$selected_month, $selected_year]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 4. Calculations for Summary
$total_students = count($records);
$paid_count = 0;
$total_collection = 0;
foreach($records as $r) {
    if(isset($r['status']) && $r['status'] == 'Paid') {
        $paid_count++;
        $total_collection += $r['amount_paid'];
    }
}
$pending_count = $total_students - $paid_count;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Ledger | BCI</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --primary: #00d4ff; --danger: #ff4d4d; --success: #00ff88; --glass: rgba(255, 255, 255, 0.05); }
        body { background: #060606; color: white; font-family: 'Poppins', sans-serif; margin: 0; padding: 30px; }
        
        .btn-back { text-decoration: none; color: #888; display: flex; align-items: center; gap: 5px; margin-bottom: 20px; font-size: 14px; transition: 0.3s; }
        .btn-back:hover { color: var(--primary); }

        .summary-bar { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .s-card { background: var(--glass); padding: 20px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.1); text-align: center; }
        .s-card h4 { margin: 0; font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 1px; }
        .s-card p { margin: 10px 0 0; font-size: 24px; font-weight: 700; color: var(--primary); }

        .filter-bar { background: var(--glass); padding: 20px; border-radius: 15px; display: flex; gap: 15px; margin-bottom: 30px; align-items: flex-end; flex-wrap: wrap; }
        select, button { padding: 12px 20px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: #111; color: white; cursor: pointer; }
        button { background: var(--primary); color: black; font-weight: 700; border: none; }
        button:hover { opacity: 0.9; box-shadow: 0 0 15px var(--primary); }

        table { width: 100%; border-collapse: collapse; background: var(--glass); border-radius: 15px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); }
        th { background: var(--primary); color: black; font-size: 12px; text-transform: uppercase; }
        
        .status-badge { padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; }
        .status-Paid { background: rgba(0, 255, 136, 0.1); color: var(--success); }
        .status-Pending { background: rgba(255, 77, 77, 0.1); color: var(--danger); }
        
        .future-msg { background: rgba(255, 77, 77, 0.1); border: 1px solid var(--danger); color: var(--danger); padding: 40px; border-radius: 15px; text-align: center; }
    </style>
</head>
<body>

<a href="fee_dashboard.php" class="btn-back"><i data-lucide="arrow-left" size="16"></i> Dashboard</a>

<header style="margin-bottom: 30px;">
    <h1 style="margin: 0; font-size: 28px;">Master Fee Ledger</h1>
    <p style="color: #888;">Monitoring status for <strong><?php echo "$selected_month $selected_year"; ?></strong></p>
</header>

<?php if ($is_future): ?>
    <div class="future-msg">
        <i data-lucide="calendar-x" size="48"></i>
        <h3>Future Month Selected</h3>
        <p>You cannot view records for future months yet.</p>
    </div>
<?php else: ?>
    <div class="summary-bar">
        <div class="s-card"><h4>Total Students</h4><p><?php echo $total_students; ?></p></div>
        <div class="s-card"><h4>Fee Received</h4><p style="color: var(--success);"><?php echo $paid_count; ?></p></div>
        <div class="s-card"><h4>Pending Payments</h4><p style="color: var(--danger);"><?php echo $pending_count; ?></p></div>
        <div class="s-card"><h4>Total Collected</h4><p style="color: #ffcc00;">Rs. <?php echo number_format($total_collection); ?></p></div>
    </div>

    <form class="filter-bar" method="GET">
        <select name="month">
            <?php
            $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            foreach ($months as $m) {
                $sel = ($m == $selected_month) ? "selected" : "";
                echo "<option value='$m' $sel>$m</option>";
            }
            ?>
        </select>
        <select name="year">
            <?php for($y=2025; $y<=2026; $y++) { $sel=($y==$selected_year)?"selected":""; echo "<option value='$y' $sel>$y</option>"; } ?>
        </select>
        <button type="submit">FILTER LIST</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Enrollment</th>
                <th>Student Name</th>
                <th>Class/Wing</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Payment Date</th>
                <th>Invoice</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr><td colspan="7" style="text-align:center; padding:50px; color:#666;">No records found.</td></tr>
            <?php else: ?>
                <?php foreach ($records as $r): ?>
                <tr>
                    <td><?php echo $r['stu_enrollment_number']; ?></td>
                    <td><?php echo strtoupper($r['stu_name']); ?></td>
                    <td style="font-size: 12px; color: #888;"><?php echo $r['stu_class']; ?> (<?php echo $r['stu_wing']; ?>)</td>
                    <td>
                        <span class="status-badge status-<?php echo $r['status'] ?: 'Pending'; ?>">
                            <?php echo $r['status'] ?: 'UNPAID'; ?>
                        </span>
                    </td>
                    <td><?php echo $r['amount_paid'] ? "Rs. ".number_format($r['amount_paid']) : "-"; ?></td>
                    <td><?php echo $r['payment_date'] ? date('d-M-y', strtotime($r['payment_date'])) : "Not Paid"; ?></td>
                    <td>
                        <?php if($r['fee_id']): ?>
                            <a href="download_challan.php?challan_id=<?php echo $r['fee_id']; ?>" style="color: var(--primary); text-decoration:none;"><i data-lucide="download" size="14"></i> View</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>lucide.createIcons();</script>
</body>
</html>