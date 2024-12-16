<?php
require_once '../db/config.php';
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON data from request body
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['booking_id']) || !isset($data['rating'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Get booking details to verify client and consultant
    $bookingQuery = "SELECT client_id, consultant_id FROM ida_bookings WHERE booking_id = ?";
    $stmt = $conn->prepare($bookingQuery);
    $stmt->bind_param('i', $data['booking_id']);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();

    if (!$booking) {
        throw new Exception('Booking not found');
    }

    // Insert rating
    $insertQuery = "INSERT INTO ida_session_ratings (booking_id, client_id, consultant_id, rating, feedback) 
                   VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param('iiids', 
        $data['booking_id'],
        $booking['client_id'],
        $booking['consultant_id'],
        $data['rating'],
        $data['feedback']
    );
    $stmt->execute();

    // Update consultant's average rating
    $updateRatingQuery = "UPDATE ida_consultants SET 
                         rating = (
                             SELECT AVG(rating) 
                             FROM ida_session_ratings 
                             WHERE consultant_id = ?
                         )
                         WHERE consultant_id = ?";
    $stmt = $conn->prepare($updateRatingQuery);
    $stmt->bind_param('ii', 
        $booking['consultant_id'],
        $booking['consultant_id']
    );
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Rating submitted successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
