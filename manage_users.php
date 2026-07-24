<?php
session_start();
if (!isset($_SESSION['username'])) { header('Location: index.php'); exit(); }

// Database Connection
$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_DB";
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Connection failed: " . $e->getMessage()); }

// --- SMART REDIRECT LOGIC ---
// Checking the session user type to determine the back path
$current_user_type = $_SESSION['user_type'] ?? 'user';

if ($current_user_type === 'fee_manager') {
    $back_url = 'fee_dashboard.php';
    $back_label = 'Fee Dashboard';
} else {
    $back_url = 'dashboard.php';
    $back_label = 'Main Dashboard';
}

// Password Update Logic
if (isset($_POST['action']) && $_POST['action'] == 'UpdatePassword') {
    $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
    $stmt->execute([$_POST['new_password'], $_POST['id']]);
    header("Location: manage_users.php?msg=success");
    exit;
}

// Add User Logic
if (isset($_POST['submit'])) {
    $stmt = $conn->prepare('INSERT INTO admin (username, password, email, user_type) VALUES (?, ?, ?, ?)');
    $stmt->execute([$_POST['username'], $_POST['password'], $_POST['email'], $_POST['user_role']]);
    header("Location: manage_users.php?msg=added");
    exit;
}

// Delete Logic
if (isset($_POST['action']) && $_POST['action'] == 'Delete') {
    if ($_POST['username'] !== $_SESSION['username']) {
        $stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }
    header("Location: manage_users.php?msg=deleted");
    exit;
}

$users = $conn->query('SELECT * FROM admin ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Management | BCI</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap");
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(rgba(0,0,0,0.92), rgba(0,0,0,0.92)), url('./assets/img/bg_6.png');
            background-size: cover; background-attachment: fixed;
            font-family: 'Plus Jakarta Sans', sans-serif; color: white;
            min-height: 100vh; padding: 40px 20px;
        }
        .container { max-width: 1000px; margin: 0 auto; animation: fadeIn 0.8s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        
        #navbar {
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(255,255,255,0.05); padding: 15px 30px;
            border-radius: 20px; border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(15px); margin-bottom: 30px;
        }
        
        .back-btn {
            display: flex; align-items: center; gap: 8px;
            color: #00d4ff; text-decoration: none; font-weight: 700;
            background: rgba(0, 212, 255, 0.1); padding: 10px 20px;
            border-radius: 12px; border: 1px solid rgba(0, 212, 255, 0.2);
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); font-size: 14px;
        }
        .back-btn:hover { background: #00d4ff; color: black; transform: translateX(-5px); box-shadow: 0 0 15px rgba(0, 212, 255, 0.4); }

        .glass-card {
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 25px; padding: 25px; backdrop-filter: blur(10px); margin-bottom: 25px;
        }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }
        input, select {
            background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1);
            padding: 12px; border-radius: 10px; color: white; outline: none; transition: 0.3s;
        }
        input:focus { border-color: #00d4ff; background: rgba(0,0,0,0.6); }
        .btn-add { background: #00d4ff; color: #000; border: none; border-radius: 10px; font-weight: 800; cursor: pointer; transition: 0.3s; }
        .btn-add:hover { background: white; transform: scale(1.02); }
        
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .admin-table th { text-align: left; padding: 12px; color: #00d4ff; font-size: 11px; border-bottom: 2px solid rgba(255,255,255,0.05); text-transform: uppercase; letter-spacing: 1px; }
        .admin-table td { padding: 15px 12px; border-bottom: 1px solid rgba(255,255,255,0.05); }

        .badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800; }
        .badge-admin { background: rgba(0,212,255,0.1); color: #00d4ff; }
        .badge-fee_manager { background: rgba(255,204,0,0.1); color: #ffcc00; }
        
        .action-btn {
            background: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1);
            padding: 6px 12px; border-radius: 8px; cursor: pointer; font-size: 11px; transition: 0.2s;
        }
        .action-btn:hover { background: rgba(255,255,255,0.15); }
        .del-btn:hover { background: #ff4d4d; color: white; border-color: #ff4d4d; }
    </style>
</head>
<body>

<div class="container">
    <nav id="navbar">
        <a href="<?php echo $back_url; ?>" class="back-btn">
            <i data-lucide="arrow-left" size="18"></i> Back to <?php echo $back_label; ?>
        </a>
        <div style="text-align: right;">
            <span style="font-size: 10px; color: #888; letter-spacing: 1px;">SYSTEM OPERATOR</span>
            <div style="font-weight: 800; color: #00d4ff;"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
        </div>
    </nav>

    <div class="glass-card">
        <h3 style="margin-bottom: 20px; font-size: 1rem; letter-spacing: 1px;"><i data-lucide="user-plus" style="vertical-align:middle; color:#00d4ff; margin-right:10px;"></i> CREATE SYSTEM ACCESS</h3>
        <form method="post" class="form-grid">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <select name="user_role">
                <option value="admin">Main Admin</option>
                <option value="fee_manager">Fee Manager</option>
            </select>
            <button type="submit" name="submit" class="btn-add">REGISTER USER</button>
        </form>
    </div>

    <div class="glass-card">
        <h3 style="margin-bottom: 20px; font-size: 1rem; letter-spacing: 1px;"><i data-lucide="shield" style="vertical-align:middle; color:#00d4ff; margin-right:10px;"></i> MANAGE STAFF ROLES</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User Identity</th>
                    <th>Update Password</th>
                    <th>Privilege</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div style="font-weight:700; color: #eee;"><?php echo htmlspecialchars($u['username']); ?></div>
                        <div style="font-size:11px; color:#555;"><?php echo htmlspecialchars($u['email']); ?></div>
                    </td>
                    <td>
                        <form method="post" style="display:flex; gap:5px;">
                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                            <input type="text" name="new_password" value="<?php echo htmlspecialchars($u['password']); ?>" 
                                   style="padding: 6px; font-size: 12px; width: 130px; border-radius: 8px;">
                            <button type="submit" name="action" value="UpdatePassword" class="action-btn" style="color:#00ff88; border-color: rgba(0,255,136,0.2);">Save</button>
                        </form>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $u['user_type']; ?>">
                            <?php echo strtoupper(str_replace('_', ' ', $u['user_type'])); ?>
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                            <input type="hidden" name="username" value="<?php echo $u['username']; ?>">
                            <?php if ($u['username'] !== $_SESSION['username']): ?>
                                <button type="submit" name="action" value="Delete" class="action-btn del-btn" onclick="return confirm('Remove this user permanently?')">Revoke</button>
                            <?php else: ?>
                                <span style="font-size: 10px; color: #00d4ff; font-weight:800; opacity: 0.5;">CURRENTLY IN USE</span>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>