<?php
require_once '../../../config/session.php';
require_once '../../../config/db.php';
require_login();

$input = json_decode(file_get_contents('php://input'), true);
$height = trim($input['user_height_cm'] ?? '');

if ($height === '' || !is_numeric($height)) {
    http_response_code(400);
    echo json_encode(['error' => 'Chiều cao không hợp lệ.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare(
    "INSERT INTO user_settings (user_id, setting_key, setting_value)
     VALUES (?, 'user_height_cm', ?)
     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
);
$stmt->bind_param("is", $user_id, $height);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Không thể cập nhật.']);
}
$stmt->close();
$conn->close();
