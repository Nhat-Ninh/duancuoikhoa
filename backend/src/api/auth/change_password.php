<?php
require_once '../../config/session.php';
require_once '../../config/db.php';
require_login();

$input = json_decode(file_get_contents('php://input'), true);
$current = $input['current_password'] ?? '';
$new     = $input['new_password'] ?? '';

if (strlen($new) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'Mật khẩu mới tối thiểu 8 ký tự.']);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($hash);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['error' => 'Không tìm thấy người dùng.']);
    exit;
}
$stmt->close();

if (!password_verify($current, $hash)) {
    http_response_code(401);
    echo json_encode(['error' => 'Mật khẩu hiện tại không đúng.']);
    exit;
}

$newHash = password_hash($new, PASSWORD_DEFAULT);
$upd = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
$upd->bind_param("si", $newHash, $user_id);
if ($upd->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Không thể cập nhật mật khẩu.']);
}
$upd->close();
$conn->close();
