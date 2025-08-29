<?php
require_once '../../config/session.php';
require_once '../../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
$full_name = trim($input['full_name'] ?? '');
$password = $input['password'] ?? '';

if (!$email || !$full_name || strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'Thông tin không hợp lệ (email/pass >= 8 ký tự).']);
    exit;
}

$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'Email đã tồn tại.']);
    exit;
}
$check->close();

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (email, full_name, password_hash) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $full_name, $hash);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Không thể tạo tài khoản.']);
}
$stmt->close();
$conn->close();
