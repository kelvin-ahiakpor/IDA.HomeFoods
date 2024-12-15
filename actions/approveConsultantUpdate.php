<?php
session_start();
require_once '../db/config.php';
require_once '../middleware/checkUserAccess.php';
checkUserAccess('Admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $type = $_POST['type']; // 'certification' or 'specialization'
        $userId = $_POST['user_id'];
        $name = $_POST['name'];
        
        $conn->begin_transaction();

        if ($type === 'certification') {
            $stmt = $conn->prepare("
                UPDATE ida_consultant_certifications 
                SET status = 'Active',
                    reviewed_at = CURRENT_TIMESTAMP,
                    reviewed_by = ?
                WHERE consultant_id = ? AND name = ? AND status = 'Pending'
            ");
        } else {
            $stmt = $conn->prepare("
                UPDATE ida_consultant_specializations 
                SET status = 'Active',
                    reviewed_at = CURRENT_TIMESTAMP,
                    reviewed_by = ?
                WHERE consultant_id = ? AND name = ? AND status = 'Pending'
            ");
        }

        $stmt->bind_param("iis", $_SESSION['user_id'], $userId, $name);
        $stmt->execute();

        // Log the action
        $stmt = $conn->prepare("
            INSERT INTO ida_admin_dashboard_logs 
            (admin_id, action, affected_user_id, action_type, details) 
            VALUES (?, ?, ?, 'Update_Approval', ?)
        ");
        $action = "Approved consultant " . $type;
        $details = json_encode(['type' => $type, 'name' => $name]);
        $stmt->bind_param("isis", $_SESSION['user_id'], $action, $userId, $details);
        $stmt->execute();

        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error approving consultant update: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} 