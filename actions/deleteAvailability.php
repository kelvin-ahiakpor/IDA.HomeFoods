<?php
require_once '../db/config.php';
require_once '../middleware/checkUserAccess.php';
checkUserAccess('Consultant');

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $availabilityId = $data['availability_id'] ?? null;

    if (!$availabilityId) {
        throw new Exception('Availability ID is required');
    }

    // Verify the availability belongs to the consultant
    $query = "DELETE FROM ida_availability 
              WHERE availability_id = ? 
              AND consultant_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $availabilityId, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('No availability found or unauthorized');
        }
    } else {
        throw new Exception('Error deleting availability');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 