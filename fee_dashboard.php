<?php
session_start();

// Security Check: Sirf Fee Manager hi is page ko khol sake
if (!isset($_SESSION['username']) || $_SESSION['user_type'] !== 'fee_manager') {
    header('Location: index.php');
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Management Portal | BCI</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary: #00d4ff;
            --accent: #ffcc00;
            --scholarship: #a855f7;
            --adjust: #ff9f43;
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        body {
            background: linear-gradient(rgba(0,0,0,0.85), rgba(0,0,0,0.85)), url('./assets/img/bg_6.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            color: white;
            min-height: 100vh;
            padding: 40px;
            overflow-x: hidden;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 50px;
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .profile-icon {
            width: 55px;
            height: 55px;
            background: var(--primary);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: black;
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.3);
            position: relative;
        }

        .profile-icon::after {
            content: '';
            position: absolute;
            width: 100%; height: 100%;
            border-radius: 15px;
            border: 2px solid var(--primary);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.4); opacity: 0; }
        }

        .welcome-text h1 { font-size: 24px; margin: 0; }
        .welcome-text span { color: var(--primary); font-size: 12px; text-transform: uppercase; letter-spacing: 2px; }

        .admin-mgr-link {
            display: flex;
            align-items: center;
            background: rgba(0, 212, 255, 0.08);
            color: var(--primary);
            border: 1px solid rgba(0, 212, 255, 0.2);
            padding: 10px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
        }

        .admin-mgr-link:hover {
            background: var(--primary);
            color: black;
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.4);
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-top: 30px;
        }

        .card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 25px;
            padding: 30px;
            text-align: center;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .card:hover {
            transform: translateY(-12px) scale(1.02);
            background: rgba(255,255,255,0.12);
            border-color: var(--primary);
            box-shadow: 0 15px 35px rgba(0, 212, 255, 0.2);
        }

        .card i {
            margin-bottom: 20px;
            color: var(--primary);
            opacity: 0.8;
            margin-left: auto;
            margin-right: auto;
            transition: 0.3s;
        }

        .card:hover i { transform: scale(1.1) rotate(5deg); opacity: 1; }
        .card h3 { font-size: 20px; margin: 10px 0; }
        .card p { font-size: 13px; color: #aaa; flex-grow: 1; margin-bottom: 20px; }

        .btn-action {
            background: var(--primary);
            color: black;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 700;
            width: 100%;
            cursor: pointer;
            text-transform: uppercase;
            font-size: 11px;
            transition: 0.3s;
        }

        .btn-action:hover { box-shadow: 0 0 15px var(--primary); }

        .logout-btn {
            background: rgba(255, 77, 77, 0.1);
            color: #ff4d4d;
            border: 1px solid rgba(255, 77, 77, 0.2);
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: 0.3s;
        }

        .logout-btn:hover { background: #ff4d4d; color: white; }

        .badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--accent);
            color: black;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 800;
            animation: flash 1.5s infinite;
        }

        @keyframes flash {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        @media (max-width: 1024px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 700px) { .stats-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="header">
    <div class="user-profile">
        <div class="profile-icon">
            <i data-lucide="wallet"></i>
        </div>
        <div class="welcome-text">
            <span>Account Department</span>
            <h1>Welcome back, <?php echo htmlspecialchars($username); ?></h1>
        </div>
    </div>
    
    <div style="display: flex; gap: 15px; align-items: center;">
        <a href="manage_users.php" class="admin-mgr-link" title="Manage Staff & Passwords">
            <i data-lucide="shield-check" style="width:18px; margin-right:5px;"></i>
            <span>Admin Manager</span>
        </a>

        <a href="logout.php" class="logout-btn">
            <i data-lucide="log-out" style="width:16px; vertical-align:middle;"></i> Logout
        </a>
    </div>
</div>

<div class="stats-grid">
    <div class="card" onclick="location.href='collect_fee.php'">
        <i data-lucide="plus-circle" size="48"></i>
        <h3>Collect Fees</h3>
        <p>Generate new invoice and collect student monthly fees.</p>
        <button class="btn-action">New Payment</button>
    </div>

    <div class="card" onclick="location.href='view_all_details.php'">
        <i data-lucide="eye" size="48" style="color: #00ff88;"></i>
        <h3>Master Ledger</h3>
        <p>View detailed payment status of all students for any specific month.</p>
        <button class="btn-action" style="background: #00ff88; color: black;">Explore Details</button>
    </div>

    <div class="card" onclick="location.href='fee_correction.php'" style="border: 1px solid var(--primary); background: rgba(0, 212, 255, 0.05);">
        <i data-lucide="edit-3" size="48" style="color: var(--primary);"></i>
        <h3>Universal Correction</h3>
        <p>All-in-one: Update fees, scholarships, or adjustments by Enrollment ID.</p>
        <button class="btn-action">Modify Record</button>
    </div>

    <div class="card" onclick="location.href='manage_scholarships.php'">
        <i data-lucide="graduation-cap" size="48" style="color: var(--scholarship);"></i>
        <h3>Scholarships</h3>
        <p>Assign merit or need-based percentage discounts to student records.</p>
        <button class="btn-action" style="background: var(--scholarship); color: white;">Manage Grants</button>
    </div>

    <div class="card" style="cursor: default;">
        <i data-lucide="search" size="48" style="color: #ffcc00;"></i>
        <h3>Quick Download</h3>
        <p>Enter Enrollment ID to quickly download a student's current challan.</p>
        <form action="quick_download.php" method="GET" style="margin-top: 10px; display: flex; gap: 5px;">
            <input type="text" name="enroll_id" placeholder="ID (e.g. 1024)" required 
                   style="width: 70%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: #000; color: #fff;">
            <button type="submit" class="btn-action" style="width: 30%; padding: 10px; background: #ffcc00; color: black;">GO</button>
        </form>
    </div>

    <div class="card" onclick="location.href='manage_adjustments.php'">
        <i data-lucide="calculator" size="48" style="color: var(--adjust);"></i>
        <h3>Fines & Concessions</h3>
        <p>Apply one-time fines or manual fixed-amount fee reductions.</p>
        <button class="btn-action" style="background: var(--adjust); color: black;">Adjust Dues</button>
    </div>

    <div class="card" onclick="location.href='fee_history.php'">
        <i data-lucide="history" size="48"></i>
        <h3>Fee History</h3>
        <p>View all previous transactions and paid invoices.</p>
        <button class="btn-action">View Records</button>
    </div>

    <div class="card" onclick="location.href='defaulters.php'">
        <span class="badge">ALERT</span>
        <i data-lucide="alert-triangle" size="48" style="color: var(--accent);"></i>
        <h3>Defaulters List</h3>
        <p>Check list of students with pending dues or late fees.</p>
        <button class="btn-action" style="background: var(--accent); color: black;">Check Dues</button>
    </div>

    <div class="card" onclick="location.href='fee_reports.php'">
        <i data-lucide="file-bar-chart" size="48"></i>
        <h3>Monthly Report</h3>
        <p>Download summary of total collections for this month.</p>
        <button class="btn-action">Download PDF</button>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
</body>
</html>