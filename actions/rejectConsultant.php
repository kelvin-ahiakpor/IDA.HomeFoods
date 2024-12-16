<?php
session_start();
require_once '../db/config.php';
require_once '../middleware/checkUserAccess.php';
checkUserAccess('Admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $applicationId = $_POST['application_id'];
        $userId = $_POST['user_id'];
        $reason = $_POST['reason'];
        
        if (empty($reason)) {
            throw new Exception('Rejection reason is required');
        }

        $conn->begin_transaction();

        // 1. Update application status
        $stmt = $conn->prepare("
            UPDATE ida_consultant_applications 
            SET status = 'Rejected',
                rejection_reason = ?,
                reviewed_at = CURRENT_TIMESTAMP,
                reviewed_by = ?
            WHERE application_id = ?
        ");
        $stmt->bind_param("sii", $reason, $_SESSION['user_id'], $applicationId);
        $stmt->execute();

        // 2. Update consultant status
        $stmt = $conn->prepare("
            UPDATE ida_consultants 
            SET status = 'Rejected' 
            WHERE consultant_id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        // 3. Update certifications status
        $stmt = $conn->prepare("
            UPDATE ida_consultant_certifications 
            SET status = 'Rejected' 
            WHERE consultant_id = ? AND status = 'Pending'
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        // 4. Log the action
        $stmt = $conn->prepare("
            INSERT INTO ida_admin_dashboard_logs 
            (admin_id, action, affected_user_id, action_type, details) 
            VALUES (?, 'Rejected consultant application', ?, 'Status_Change', ?)
        ");
        $details = json_encode([
            'application_id' => $applicationId,
            'reason' => $reason
        ]);
        $stmt->bind_param("iis", $_SESSION['user_id'], $userId, $details);
        $stmt->execute();

        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error rejecting consultant: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} 