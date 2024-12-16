<?php
require_once '../db/config.php';
require_once '../middleware/checkUserAccess.php';

header('Content-Type: application/json');

// Only allow consultants to update their last_active
if ($_SESSION['role'] !== 'Consultant') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    updateConsultantLastActive($_SESSION['user_id']);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error updating last active status']);
} 