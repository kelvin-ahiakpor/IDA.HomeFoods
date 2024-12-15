<?php
session_start();

require_once '../db/config.php';
require_once '../functions/session_check.php';
require_once '../middleware/checkUserAccess.php';

// Check if user is logged in and is a Client
checkUserAccess('Client');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $userId = $_SESSION['user_id'];

        // Check if user already has a pending application
        $stmt = $conn->prepare("
            SELECT status 
            FROM ida_consultant_applications 
            WHERE user_id = ? 
            AND status = 'Pending'
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'You already have a pending application'
            ]);
            exit;
        }

        // Start transaction
        $conn->begin_transaction();

        // 1. Create application entry with all details
        $stmt = $conn->prepare("
            INSERT INTO ida_consultant_applications 
            (user_id, status, background, hourly_rate, expertise) 
            VALUES (?, 'Pending', ?, ?, ?)
        ");
        $expertise = json_encode($_POST['expertise']);
        $stmt->bind_param("isds", $userId, $_POST['background'], $_POST['hourlyRate'], $expertise);
        $stmt->execute();
        $applicationId = $conn->insert_id;

        // 2. Store in consultant table with pending status
        $stmt = $conn->prepare("
            INSERT INTO ida_consultants 
            (consultant_id, expertise, background, hourly_rate, availability, bio, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pending')
        ");
        $availability = json_encode([]);
        $stmt->bind_param("issdss", 
            $userId, 
            $expertise,
            $_POST['background'],
            $_POST['hourlyRate'],
            $availability,
            $_POST['bio']
        );
        $stmt->execute();

        // 3. Store certifications
        $stmt = $conn->prepare("
            INSERT INTO ida_consultant_certifications 
            (consultant_id, name, status) 
            VALUES (?, ?, 'Pending')
        ");
        foreach ($_POST['certifications'] as $certification) {
            $stmt->bind_param("is", $userId, $certification);
            $stmt->execute();
        }

        // 4. Log the application
        // First get user's name
        $stmt = $conn->prepare("SELECT first_name, last_name FROM ida_users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        // Now create the log entry
        $stmt = $conn->prepare("
            INSERT INTO ida_admin_dashboard_logs 
            (admin_id, action, affected_user_id, action_type, details) 
            VALUES (?, ?, ?, 'Status_Change', ?)
        ");
        $action = "Client {$user['first_name']} {$user['last_name']} submitted a consultant application";
        $details = json_encode([
            'application_id' => $applicationId,
            'expertise' => $_POST['expertise'],
            'hourly_rate' => $_POST['hourlyRate'],
            'background' => $_POST['background'],
            'bio' => $_POST['bio']
        ]);
        $adminId = 1; // System admin ID
        $stmt->bind_param("isis", $adminId, $action, $userId, $details);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Application submitted successfully'
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in consultant application: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}
?> 