<?php
session_start();
require_once '../db/config.php';
require_once '../middleware/checkUserAccess.php';
checkUserAccess('Admin');

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['user_id'] ?? null;

    if (!$userId) {
        throw new Exception('User ID is required');
    }

    // Start transaction
    $conn->begin_transaction();

    // Delete from consultants table first (due to foreign key)
    $stmt = $conn->prepare("DELETE FROM ida_consultants WHERE consultant_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();

    // Delete from users table
    $stmt = $conn->prepare("DELETE FROM ida_users WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('Consultant not found');
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 