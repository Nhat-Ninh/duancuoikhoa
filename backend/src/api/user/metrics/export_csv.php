<?php
require_once '../../../config/session.php';
require_once '../../../config/db.php';
require_login();

$user_id = $_SESSION['user_id'];
$from = $_GET['from'] ?? null; // YYYY-MM-DD
$to   = $_GET['to']   ?? null; // YYYY-MM-DD

$cond = "WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if ($from) { $cond .= " AND metric_date >= ?"; $params[] = $from; $types .= "s"; }
if ($to)   { $cond .= " AND metric_date <= ?"; $params[] = $to;   $types .= "s"; }

$sql = "SELECT metric_date, weight_kg, systolic_bp, diastolic_bp, heart_rate, user_height_cm
        FROM health_metrics $cond ORDER BY metric_date ASC, id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="health_metrics.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['metric_date','weight_kg','systolic_bp','diastolic_bp','heart_rate','user_height_cm']);
while ($row = $res->fetch_assoc()) fputcsv($out, $row);
fclose($out);

$stmt->close();
$conn->close();
