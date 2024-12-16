<?php
session_start();
require_once '../db/config.php';
require_once '../middleware/checkUserAccess.php';
checkUserAccess('Admin');

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($data['user_id'], $data['name'], $data['email'], $data['expertise'])) {
        throw new Exception('All fields are required');
    }

    // Split name into first and last name
    $nameParts = explode(' ', $data['name'], 2);
    $firstName = $nameParts[0];
    $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

    // Check if email is already taken by another user
    $stmt = $conn->prepare("SELECT user_id FROM ida_users WHERE email = ? AND user_id != ?");
    $stmt->bind_param('si', $data['email'], $data['user_id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Email is already taken');
    }

    // Start transaction
    $conn->begin_transaction();

    // Update user table
    $stmt = $conn->prepare("UPDATE ida_users 
                           SET first_name = ?, 
                               last_name = ?, 
                               email = ?
                           WHERE user_id = ?");
    
    $stmt->bind_param('sssi', $firstName, $lastName, $data['email'], $data['user_id']);
    $stmt->execute();

    // Update consultant table
    $stmt = $conn->prepare("UPDATE ida_consultants 
                           SET expertise = ?
                           WHERE consultant_id = ?");
    
    $stmt->bind_param('si', $data['expertise'], $data['user_id']);
    $stmt->execute();

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