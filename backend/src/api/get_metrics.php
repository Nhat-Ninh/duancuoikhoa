<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';

$sql = "SELECT id, metric_date, weight_kg, systolic_bp, diastolic_bp, heart_rate, user_height_cm FROM health_metrics ORDER BY metric_date DESC, id DESC";
$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed', 'details' => $conn->error]);
    exit();
}

$metrics = [];
while ($row = $result->fetch_assoc()) {
    $metrics[] = $row;
}

echo json_encode($metrics);
$conn->close();
?>
