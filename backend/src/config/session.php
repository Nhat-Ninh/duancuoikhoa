<?php
// Bắt buộc gọi file này ở mọi endpoint cần session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400 * 7, // 7 ngày
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
        // 'secure' => true // Bật khi dùng HTTPS
    ]);
    session_start();
}
header('Content-Type: application/json');

function require_login() {
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'UNAUTHENTICATED']);
        exit;
    }
}
