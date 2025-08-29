<?php
require_once '../../../config/session.php';
require_once '../../../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];
$sql = "SELECT id, metric_date, weight_kg, systolic_bp, diastolic_bp, heart_rate, user_height_cm
        FROM health_metrics
        WHERE user_id = ?
        ORDER BY metric_date DESC, id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;

echo json_encode($rows);
$stmt->close();
$conn->close();
