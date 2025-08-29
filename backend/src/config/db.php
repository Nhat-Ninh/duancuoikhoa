<?php
header("Content-Type: application/json");

$host = 'db'; // Tên service của container database trong docker-compose
$dbname = getenv('MYSQL_DATABASE') ?: 'health_db';
$username = getenv('MYSQL_USER') ?: 'user';
$password = getenv('MYSQL_PASSWORD') ?: 'userpassword';

// Tạo kết nối
$conn = @new mysqli($host, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    exit();
}
?>