<?php
session_start();
require_once '../db/config.php';
require_once '../middleware/checkUserAccess.php';
checkUserAccess('Consultant');

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $bookingId = $data['booking_id'] ?? null;
    $hourlyRate = $data['hourly_rate'] ?? 0;

    if (!$bookingId) {
        throw new Exception('Booking ID is required');
    }

    // Start transaction
    $conn->begin_transaction();

    // Get booking details for logging
    $query = "SELECT b.client_id, CONCAT(u.first_name, ' ', u.last_name) as client_name 
              FROM ida_bookings b
              JOIN ida_users u ON b.client_id = u.user_id
              WHERE b.booking_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $bookingDetails = $stmt->get_result()->fetch_assoc();

    // Update booking status
    $query = "UPDATE ida_bookings 
              SET completed_at = NOW()
              WHERE booking_id = ? 
              AND consultant_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $bookingId, $_SESSION['user_id']);
    $stmt->execute();

    // Add session to consultant_sessions with earnings
    $query = "INSERT INTO ida_consultant_sessions 
              (consultant_id, client_id, session_type, start_time, duration, status, price)
              SELECT b.consultant_id, b.client_id, 'Consultation', 
                     CONCAT(b.booking_date, ' ', b.time_slot), 60, 'Completed', c.hourly_rate
              FROM ida_bookings b
              JOIN ida_consultants c ON b.consultant_id = c.consultant_id
              WHERE b.booking_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $bookingId);
    $stmt->execute();

    // Log the completion
    $logDetails = [
        'booking_id' => $bookingId,
        'client_name' => $bookingDetails['client_name'],
        'completed_at' => date('Y-m-d H:i:s')
    ];

    $query = "INSERT INTO ida_admin_dashboard_logs 
              (admin_id, action, affected_user_id, action_type, details) 
              VALUES (?, 'Session completed', ?, 'Status_Change', ?)";
    
    $stmt = $conn->prepare($query);
    $jsonDetails = json_encode($logDetails);
    $stmt->bind_param('iis', $_SESSION['user_id'], $bookingDetails['client_id'], $jsonDetails);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 
