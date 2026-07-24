<?php
session_start();
if (!isset($_SESSION['username'])) { header('Location: index.php'); exit(); }

$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_DB";
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Connection failed: " . $e->getMessage()); }

// Handle Success Messages from redirects
$msg = isset($_GET['msg']) ? $_GET['msg'] : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BCI Management | Admin Dashboard</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap");

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: linear-gradient(rgba(0,0,0,0.85), rgba(0,0,0,0.85)), url('./assets/img/bg_2.png');
            background-size: cover; background-attachment: fixed;
            font-family: "Poppins", sans-serif; color: #fff;
        }

        header { width: 90%; display: flex; justify-content: space-between; align-items: center; padding: 2em 0; margin: 0 auto; }
        .brand-section { display: flex; align-items: center; gap: 20px; }
        .logo-display { height: 80px; filter: drop-shadow(0 0 10px rgba(0, 212, 255, 0.5)); }
        
        /* Welcome Tag Style */
        .welcome-tag { 
            background: rgba(255, 255, 255, 0.05); 
            padding: 8px 20px; 
            border-radius: 50px; 
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.85rem;
        }
        .welcome-tag span { color: #00d4ff; font-weight: 700; }

        .nav-wrapper { width: 90%; margin: 0 auto 30px auto; }
        #navbar {
            display: flex; list-style: none; background: rgba(255, 255, 255, 0.1);
            border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px);
        }
        #navbar li { flex: 1; text-align: center; }
        #navbar li a { display: block; color: #fff; padding: 1rem; text-decoration: none; transition: 0.3s; font-weight: 600; font-size: 0.9rem; }
        #navbar li a:hover { background: rgba(0, 212, 255, 0.2); }

        .content-area { width: 90%; margin: 0 auto; animation: fadeIn 0.5s ease-in; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .msg-box { 
            background: rgba(0, 212, 255, 0.1); border: 1px solid #00d4ff; 
            color: #00d4ff; padding: 15px; border-radius: 12px; text-align: center; 
            margin-bottom: 25px; font-weight: 600; font-size: 0.9rem;
        }

        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 20px; }
        .mode-title { font-size: 1.5rem; font-weight: 700; color: #00d4ff; min-width: 250px; text-transform: uppercase; letter-spacing: 2px;}
        
        .btn-group { display: flex; gap: 10px; }
        .primary-btn {
            background: #00d4ff; color: #000; text-decoration: none; padding: 12px 24px;
            border-radius: 10px; font-weight: 700; font-size: 0.8rem; transition: 0.3s; border: none; cursor: pointer;
        }
        .switch-btn { background: #ffcc00; color: #000; }
        .primary-btn:hover { background: #fff; box-shadow: 0 0 15px #00d4ff; transform: translateY(-2px); }

        .filters { display: flex; gap: 10px; margin-bottom: 25px; overflow-x: auto; padding-bottom: 5px; }
        .pill {
            background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ccc; padding: 8px 18px; border-radius: 25px; cursor: pointer; font-size: 0.75rem; transition: 0.3s; white-space: nowrap;
        }
        .pill.active, .pill:hover { background: #fff; color: #000; border-color: #fff; box-shadow: 0 0 15px rgba(255,255,255,0.2); }

        .glass-table {
            width: 100%; border-collapse: collapse; background: rgba(0, 0, 0, 0.4);
            border-radius: 15px; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .glass-table th { background: rgba(255, 255, 255, 0.05); padding: 18px 15px; text-align: left; color: #00d4ff; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;}
        .glass-table td { padding: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.03); font-size: 0.85rem; }
        
        .btn-op { text-decoration: none; font-size: 0.65rem; font-weight: 800; padding: 6px 12px; border-radius: 6px; margin-left: 5px; transition: 0.2s; border: 1px solid; display: inline-block; }
        .v-btn { border-color: #00ff88; color: #00ff88; } 
        .e-btn { border-color: #ffcc00; color: #ffcc00; } 
        .d-btn { border-color: #ff4d4d; color: #ff4d4d; } 
        .btn-op:hover { background: #fff; color: #000; border-color: #fff; transform: scale(1.05); }

        .hidden { display: none; }
    </style>
</head>
<body>

<header>
    <div class="brand-section">
        <img src="./assets/img/logo.png" class="logo-display" alt="BCI Logo">
        <div>
            <h1 style="font-size: 1.5rem; letter-spacing: 2px;">BAHRIA COLLEGE</h1>
            <p style="font-size: 0.8rem; color: #00d4ff; letter-spacing: 5px;">ISLAMABAD</p>
        </div>
    </div>
    <div class="welcome-tag">
        Welcome, <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
    </div>
</header>

<div class="nav-wrapper">
    <ul id="navbar">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="manage_users.php">Manage Users</a></li>
        <li><a href="logout.php" style="color: #ff4d4d;">Logout</a></li>
    </ul>
</div>

<div class="content-area">
    <?php if($msg): ?>
        <div class="msg-box"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <div class="action-bar">
        <div class="mode-title" id="dashboardTitle">STUDENT DIRECTORY</div>
        <div class="btn-group">
            <button class="primary-btn switch-btn" id="switchBtn" onclick="toggleMode()">SWITCH TO TEACHERS</button>
            <a href="add_student.php" id="addBtn" class="primary-btn">+ NEW ENROLLMENT</a>
        </div>
    </div>

    <div class="filters">
        <button class="pill active" onclick="filterData('all', this)">All Wings</button>
        <button class="pill" onclick="filterData('Lower Primary', this)">Lower Primary</button>
        <button class="pill" onclick="filterData('Upper Primary', this)">Upper Primary</button>
        <button class="pill" onclick="filterData('Boys', this)">Boys</button>
        <button class="pill" onclick="filterData('Girls', this)">Girls</button>
        <button class="pill" onclick="filterData('Cambridge', this)">Cambridge</button>
    </div>

    <div id="studentsSection">
        <table class="glass-table">
            <thead>
                <tr>
                    <th>Enrollment</th>
                    <th>Full Name</th>
                    <th>Class</th>
                    <th>Wing</th>
                    <th style="text-align:right">Management</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("SELECT stu_enrollment_number, stu_name, stu_class, stu_wing FROM students ORDER BY stu_enrollment_number DESC");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                    <tr class="data-row" data-wing="<?php echo htmlspecialchars($row['stu_wing']); ?>">
                        <td style="color:#00d4ff; font-weight:700;"><?php echo $row['stu_enrollment_number']; ?></td>
                        <td><?php echo htmlspecialchars($row['stu_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['stu_class']); ?></td>
                        <td><span style="opacity:0.6;"><?php echo htmlspecialchars($row['stu_wing']); ?></span></td>
                        <td style="text-align:right">
                            <a href="view_student.php?id=<?php echo $row['stu_enrollment_number']; ?>" class="btn-op v-btn">VIEW</a>
                            <a href="edit_student.php?id=<?php echo $row['stu_enrollment_number']; ?>" class="btn-op e-btn">EDIT</a>
                            <a href="delete_student.php?id=<?php echo $row['stu_enrollment_number']; ?>" class="btn-op d-btn" onclick="return confirm('Delete this student record?')">DELETE</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div id="teachersSection" class="hidden">
        <table class="glass-table">
            <thead>
                <tr>
                    <th>Teacher ID</th>
                    <th>Full Name</th>
                    <th>Wing</th>
                    <th>Contact</th>
                    <th style="text-align:right">Management</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("SELECT teacher_id, teacher_id_official, teacher_name, teacher_wing, contact_no FROM teachers ORDER BY teacher_id DESC");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                    <tr class="data-row" data-wing="<?php echo htmlspecialchars($row['teacher_wing']); ?>">
                        <td style="color:#ffcc00; font-weight:700;"><?php echo htmlspecialchars($row['teacher_id_official']); ?></td>
                        <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['teacher_wing']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                        <td style="text-align:right">
                            <a href="view_teacher.php?id=<?php echo $row['teacher_id']; ?>" class="btn-op v-btn">VIEW</a>
                            <a href="edit_teacher.php?id=<?php echo $row['teacher_id']; ?>" class="btn-op e-btn">EDIT</a>
                            <a href="delete_teacher.php?id=<?php echo $row['teacher_id']; ?>" class="btn-op d-btn" onclick="return confirm('Remove this teacher profile?')">DELETE</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
let currentMode = 'students';

function toggleMode() {
    const sSection = document.getElementById('studentsSection');
    const tSection = document.getElementById('teachersSection');
    const title = document.getElementById('dashboardTitle');
    const btn = document.getElementById('switchBtn');
    const addBtn = document.getElementById('addBtn');

    if (currentMode === 'students') {
        sSection.classList.add('hidden');
        tSection.classList.remove('hidden');
        title.innerText = 'TEACHER DIRECTORY';
        title.style.color = '#ffcc00';
        btn.innerText = 'SWITCH TO STUDENTS';
        addBtn.innerText = '+ ADD NEW TEACHER';
        addBtn.href = 'add_teacher.php';
        currentMode = 'teachers';
    } else {
        sSection.classList.remove('hidden');
        tSection.classList.add('hidden');
        title.innerText = 'STUDENT DIRECTORY';
        title.style.color = '#00d4ff';
        btn.innerText = 'SWITCH TO TEACHERS';
        addBtn.innerText = '+ NEW ENROLLMENT';
        addBtn.href = 'add_student.php';
        currentMode = 'students';
    }
    filterData('all', document.querySelector('.pill'));
}

function filterData(wing, element) {
    document.querySelectorAll('.pill').forEach(btn => btn.classList.remove('active'));
    element.classList.add('active');
    const activeSectionId = currentMode === 'students' ? 'studentsSection' : 'teachersSection';
    const rows = document.querySelectorAll(`#${activeSectionId} .data-row`);
    rows.forEach(row => {
        if (wing === 'all' || row.dataset.wing === wing) { row.style.display = ''; } 
        else { row.style.display = 'none'; }
    });
}
</script>
</body>
</html>