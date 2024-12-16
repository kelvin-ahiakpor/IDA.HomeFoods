<?php
session_start();
require_once '../db/config.php';
require_once '../middleware/checkUserAccess.php';
checkUserAccess('Admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        error_log("Received approval request: " . print_r($_POST, true));
        
        if (!isset($_POST['application_id']) || !isset($_POST['user_id'])) {
            throw new Exception('Missing required parameters');
        }

        $applicationId = $_POST['application_id'];
        $userId = $_POST['user_id'];
        
        error_log("Processing approval for application_id: $applicationId, user_id: $userId");
        
        $conn->begin_transaction();

        // 1. Update application status
        $stmt = $conn->prepare("
            UPDATE ida_consultant_applications 
            SET status = 'Approved', 
                reviewed_at = CURRENT_TIMESTAMP, 
                reviewed_by = ? 
            WHERE application_id = ?
        ");
        $stmt->bind_param("ii", $_SESSION['user_id'], $applicationId);
        $stmt->execute();

        // 2. Update user role
        $stmt = $conn->prepare("UPDATE ida_users SET role = 'Consultant' WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        // 3. Update consultant status
        $stmt = $conn->prepare("UPDATE ida_consultants SET status = 'Active' WHERE consultant_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        // 4. Update certifications status
        $stmt = $conn->prepare("
            UPDATE ida_consultant_certifications 
            SET status = 'Active' 
            WHERE consultant_id = ? AND status = 'Pending'
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        // 5. Log the action
        $stmt = $conn->prepare("
            INSERT INTO ida_admin_dashboard_logs 
            (admin_id, action, affected_user_id, action_type, details) 
            VALUES (?, 'Approved consultant application', ?, 'Consultant_Approval', ?)
        ");
        $details = json_encode(['application_id' => $applicationId]);
        $stmt->bind_param("iis", $_SESSION['user_id'], $userId, $details);
        $stmt->execute();

        $conn->commit();
        error_log("Approval successful");
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error approving consultant: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 