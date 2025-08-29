<?php
require_once '../../../config/session.php';
require_once '../../../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT setting_value FROM user_settings WHERE user_id = ? AND setting_key = 'user_height_cm'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($height);
if ($stmt->fetch()) {
    echo json_encode(['user_height_cm' => $height]);
} else {
    echo json_encode(['user_height_cm' => null]);
}
$stmt->close();
$conn->close();
