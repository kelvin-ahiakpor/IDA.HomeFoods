<?php
session_start();
require_once '../db/config.php';
require_once '../middleware/checkUserAccess.php';
checkUserAccess('Admin');

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['user_id'] ?? null;
    $newStatus = $data['active'] ?? null;

    if (!$userId) {
        throw new Exception('User ID is required');
    }

    $stmt = $conn->prepare("UPDATE ida_users 
                           SET is_active = ?
                           WHERE user_id = ? AND role = 'Client'");
    
    $stmt->bind_param('ii', $newStatus, $userId);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('Client not found');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 