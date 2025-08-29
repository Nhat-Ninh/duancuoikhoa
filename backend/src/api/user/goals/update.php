<?php
require_once '../../../config/session.php';
require_once '../../../config/db.php';
require_login();

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

$allowed = ['weight_goal','bp_goal_sys','bp_goal_dia'];
$updates = [];
foreach ($allowed as $k) {
    if (isset($input[$k]) && $input[$k] !== '') {
        $val = (string)$input[$k];
        $updates[] = [$k, $val];
    }
}
if (empty($updates)) { echo json_encode(['success'=>true]); exit; }

$stmt = $conn->prepare("INSERT INTO user_settings (user_id, setting_key, setting_value)
                        VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
foreach ($updates as [$k, $v]) {
    $stmt->bind_param("iss", $user_id, $k, $v);
    $stmt->execute();
}
$stmt->close();
echo json_encode(['success'=>true]);
