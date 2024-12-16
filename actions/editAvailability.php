<?php
require_once '../db/config.php';
require_once '../middleware/checkUserAccess.php';
checkUserAccess('Consultant');

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $availabilityId = $data['availability_id'] ?? null;
    $startTime = $data['start_time'] ?? null;

    if (!$availabilityId || !$startTime) {
        throw new Exception('Missing required fields');
    }

    // Calculate end time (1 hour after start time)
    $endTime = date('H:i:s', strtotime($startTime) + 3600);

    // Define office hours
    $officeStart = '09:00:00';
    $officeEnd = '21:00:00';

    // Validate time is within office hours
    if (strtotime($startTime) < strtotime($officeStart) || 
        strtotime($endTime) > strtotime($officeEnd)) {
        throw new Exception('Availability must be set between 9:00 AM and 9:00 PM');
    }

    // Update the availability
    $query = "UPDATE ida_availability 
              SET start_time = ?, end_time = ? 
              WHERE availability_id = ? AND consultant_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssii', $startTime, $endTime, $availabilityId, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('No availability found or unauthorized');
        }
    } else {
        throw new Exception('Error updating availability');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 