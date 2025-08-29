<?php
require_once '../../../config/session.php';
require_once '../../../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];
$sql = "SELECT DISTINCT metric_date FROM health_metrics WHERE user_id = ? ORDER BY metric_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$dates = [];
while ($r = $res->fetch_assoc()) $dates[] = $r['metric_date'];

$streak = 0;
$today = new DateTime('today');
foreach ($dates as $idx => $d) {
    $dt = new DateTime($d);
    $diff = (int)$today->diff($dt)->format('%a');
    if ($idx === 0) {
        if ($diff === 0) { $streak = 1; } 
        elseif ($diff === 1) { $streak = 0; break; } 
        else { $streak = 0; break; }
    }
}

if (!empty($dates)) {
    // Xây liên tiếp từ hôm nay lùi
    $streak = 0;
    $cur = new DateTime('today');
    $set = array_flip($dates); // map nhanh
    while (true) {
        $key = $cur->format('Y-m-d');
        if (isset($set[$key])) { $streak++; $cur->modify('-1 day'); }
        else break;
    }
}

echo json_encode(['streak'=> $streak]);
