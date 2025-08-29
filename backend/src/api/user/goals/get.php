<?php
require_once '../../../config/session.php';
require_once '../../../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];
$keys = ['weight_goal','bp_goal_sys','bp_goal_dia'];
$place = implode(',', array_fill(0, count($keys), '?'));

$types = "i" . str_repeat("s", count($keys));
$params = array_merge([$user_id], $keys);

$sql = "SELECT setting_key, setting_value FROM user_settings WHERE user_id = ? AND setting_key IN ($place)";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$data = ['weight_goal'=>null,'bp_goal_sys'=>null,'bp_goal_dia'=>null];
while ($row = $res->fetch_assoc()) $data[$row['setting_key']] = $row['setting_value'];

echo json_encode($data);
