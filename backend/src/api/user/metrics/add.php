<?php
require_once '../../../config/session.php';
require_once '../../../config/db.php';
require_login();

$input = json_decode(file_get_contents('php://input'), true);

$metric_date  = $input['metric_date']  ?? null;
$weight_kg    = $input['weight_kg']    ?? null;
$systolic_bp  = $input['systolic_bp']  ?? null;
$diastolic_bp = $input['diastolic_bp'] ?? null;
$heart_rate   = $input['heart_rate']   ?? null;
$height_cm    = $input['user_height_cm'] ?? null;

if (!$metric_date || !$weight_kg || !$systolic_bp || !$diastolic_bp || !$heart_rate) {
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu dữ liệu chỉ số.']);
    exit;
}

if ($height_cm !== null && $height_cm !== '') {
    // Có chiều cao -> ghi vào cột user_height_cm
    $stmt = $conn->prepare("INSERT INTO health_metrics 
        (metric_date, weight_kg, systolic_bp, diastolic_bp, heart_rate, user_id, user_height_cm)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdiiiid", $metric_date, $weight_kg, $systolic_bp, $diastolic_bp, $heart_rate, $_SESSION['user_id'], $height_cm);
} else {
    // Không có chiều cao -> giữ NULL
    $stmt = $conn->prepare("INSERT INTO health_metrics 
        (metric_date, weight_kg, systolic_bp, diastolic_bp, heart_rate, user_id)
        VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdiiii", $metric_date, $weight_kg, $systolic_bp, $diastolic_bp, $heart_rate, $_SESSION['user_id']);
}

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(['success' => true, 'id' => $conn->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Không thể thêm chỉ số.']);
}
$stmt->close();
$conn->close();
