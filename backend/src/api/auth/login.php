<?php
require_once '../../config/session.php';
require_once '../../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
$password = $input['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu email hoặc mật khẩu.']);
    exit;
}

$stmt = $conn->prepare("SELECT id, full_name, password_hash FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($id, $full_name, $hash);

if ($stmt->fetch() && password_verify($password, $hash)) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
    $_SESSION['email'] = $email;
    $_SESSION['full_name'] = $full_name;
    echo json_encode(['success' => true, 'user' => ['id' => $id, 'email' => $email, 'full_name' => $full_name]]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Sai email hoặc mật khẩu.']);
}
$stmt->close();
$conn->close();
