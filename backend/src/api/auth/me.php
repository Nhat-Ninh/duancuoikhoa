<?php
require_once '../../config/session.php';
if (!empty($_SESSION['user_id'])) {
    echo json_encode([
        'authenticated' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['email'] ?? null,
            'full_name' => $_SESSION['full_name'] ?? null
        ]
    ]);
} else {
    echo json_encode(['authenticated' => false]);
}
