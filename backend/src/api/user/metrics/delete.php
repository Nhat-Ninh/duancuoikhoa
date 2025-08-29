<?php
require_once '../../../config/session.php';
require_once '../../../config/db.php';
require_login();

header('Content-Type: application/json');

$inputRaw = file_get_contents('php://input');
$input = json_decode($inputRaw, true);
if (!is_array($input)) $input = [];

$id = 0;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
} elseif (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
} elseif (isset($input['id'])) {
    $id = (int)$input['id'];
}

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu id.']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM health_metrics WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Không tìm thấy bản ghi hoặc không có quyền.']);
}
$stmt->close();
$conn->close();
