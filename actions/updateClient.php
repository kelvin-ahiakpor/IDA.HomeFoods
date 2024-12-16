<?php
session_start();
require_once '../db/config.php';
require_once '../middleware/checkUserAccess.php';
checkUserAccess('Admin');

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($data['user_id'], $data['first_name'], $data['last_name'], $data['email'])) {
        throw new Exception('All fields are required');
    }

    // Check if email is already taken by another user
    $stmt = $conn->prepare("SELECT user_id FROM ida_users WHERE email = ? AND user_id != ?");
    $stmt->bind_param('si', $data['email'], $data['user_id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Email is already taken');
    }

    // Update user
    $stmt = $conn->prepare("UPDATE ida_users 
                           SET first_name = ?, 
                               last_name = ?, 
                               email = ?
                           WHERE user_id = ? AND role = 'Client'");
    
    $stmt->bind_param('sssi', 
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['user_id']
    );
    
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('No changes made or client not found');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 