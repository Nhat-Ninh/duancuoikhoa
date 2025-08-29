<?php
// File: backend/src/api/update_settings.php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$height = $data['height'] ?? null;

if (!$height || !is_numeric($height)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid height value']);
    exit();
}

$key = 'user_height_cm';
// Sử dụng ON DUPLICATE KEY UPDATE để vừa INSERT (nếu chưa có) vừa UPDATE (nếu đã có)
$stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
$stmt->bind_param("sss", $key, $height, $height);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Settings updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>