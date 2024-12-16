<?php
session_start();
require_once '../db/config.php';
require_once '../middleware/checkUserAccess.php';
checkUserAccess('Admin');

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['user_id'] ?? null;
    $newStatus = $data['active'] ? 'Active' : 'Inactive';

    if (!$userId) {
        throw new Exception('User ID is required');
    }

    $stmt = $conn->prepare("UPDATE ida_consultants 
                           SET status = ?
                           WHERE consultant_id = ?");
    
    $stmt->bind_param('si', $newStatus, $userId);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('Consultant not found');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
