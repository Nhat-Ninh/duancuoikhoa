<?php
header('Content-Type: application/json'); // Đảm bảo trả về JSON
ini_set('display_errors', 1); // Hiện lỗi để debug
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// File: backend/src/api/get_settings.php
require_once '../config/db.php';

$sql = "SELECT setting_key, setting_value FROM settings";
$result = $conn->query($sql);

$settings = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

echo json_encode($settings);

$conn->close();
?>