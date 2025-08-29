<?php
require_once '../config/db.php';
header('Content-Type: application/json');

// Chỉ cho phép phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

// Lấy dữ liệu JSON từ body của request
$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit();
}

// Lấy và kiểm tra dữ liệu
$metric_date = $data['metric_date'] ?? null;
$weight_kg = $data['weight_kg'] ?? null;
$systolic_bp = $data['systolic_bp'] ?? null;
$diastolic_bp = $data['diastolic_bp'] ?? null;
$heart_rate = $data['heart_rate'] ?? null;
$user_height_cm = $data['user_height_cm'] ?? null;

if (!$metric_date || !$weight_kg || !$systolic_bp || !$diastolic_bp || !$heart_rate) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO health_metrics (metric_date, weight_kg, systolic_bp, diastolic_bp, heart_rate, user_height_cm) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sdiiid", $metric_date, $weight_kg, $systolic_bp, $diastolic_bp, $heart_rate, $user_height_cm);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode([
        'success' => 'Metric added successfully',
        'id' => $conn->insert_id   // ➜ THÊM DÒNG NÀY
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>