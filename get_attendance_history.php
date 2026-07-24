<?php
$host = "localhost"; $user = "root"; $password = ""; $dbname = "students_DB";
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
} catch(PDOException $e) { die("Error"); }

$enroll = $_GET['enroll'] ?? '';

// LIMIT hata diya taake saara record aaye
$stmt = $conn->prepare("SELECT attendance_date, status FROM attendance WHERE student_id = ? ORDER BY attendance_date DESC");
$stmt->execute([$enroll]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$records) {
    echo "<p style='color:#888; text-align:center; padding:40px;'>No lifetime records found for this student.</p>";
} else {
    $current_month = "";
    echo "<div style='padding:10px;'>";
    
    foreach ($records as $r) {
        $date_val = strtotime($r['attendance_date']);
        $month_year = date('F Y', $date_val); // Example: "January 2024"
        
        // Month Header (Jab mahina change ho toh header dikhao)
        if ($current_month !== $month_year) {
            $current_month = $month_year;
            echo "<h4 style='color:#00d4ff; border-bottom:1px solid rgba(0,212,255,0.2); margin:20px 0 10px 0; padding-bottom:5px; font-size:12px; letter-spacing:1px;'>$current_month</h4>";
            echo "<div style='display:grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap:10px;'>";
        }

        $status = $r['status'];
        $color = ($status == 'Present') ? '#00ff88' : (($status == 'Absent') ? '#ff4d4d' : '#f39c12');
        $bg_alpha = ($status == 'Present') ? 'rgba(0,255,136,0.1)' : (($status == 'Absent') ? 'rgba(255,77,77,0.1)' : 'rgba(243,156,18,0.1)');
        $date_str = date('d M (D)', $date_val);
        
        echo "<div style='background:$bg_alpha; padding:10px; border-radius:10px; border: 1px solid rgba(255,255,255,0.05); text-align:center;'>
                <div style='font-size:10px; color:#aaa; margin-bottom:2px;'>$date_str</div>
                <div style='font-weight:800; color:$color; font-size:12px;'>$status</div>
              </div>";
              
        // Closing grid div will be handled by the next header or end of loop
    }
    echo "</div></div>";
}
?>