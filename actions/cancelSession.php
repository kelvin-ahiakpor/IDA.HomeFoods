<?php
session_start();
require_once '../db/config.php';
require_once '../middleware/checkUserAccess.php';
checkUserAccess('Consultant');

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $bookingId = $data['booking_id'] ?? null;

    if (!$bookingId) {
        throw new Exception('Booking ID is required');
    }

    $query = "UPDATE ida_bookings 
              SET is_cancelled = TRUE 
              WHERE booking_id = ? 
              AND consultant_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $bookingId, $_SESSION['user_id']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Unable to cancel session or session not found');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 

