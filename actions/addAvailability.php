<?php
session_start();
require_once '../db/config.php';
require_once '../middleware/checkUserAccess.php';
checkUserAccess('Consultant');

header('Content-Type: application/json');

try {
    // Validate inputs
    $date = $_POST['date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';

    if (empty($date) || empty($startTime)) {
        throw new Exception('Date and start time are required');
    }

    // Validate date is not in the past
    if (strtotime($date) < strtotime(date('Y-m-d'))) {
        throw new Exception('Cannot set availability for past dates');
    }

    // Calculate end time (1 hour after start time)
    $endTime = date('H:i:s', strtotime($startTime) + 3600);

    // Define office hours
    $officeStart = '09:00:00';
    $officeEnd = '21:00:00';  // 9:00 PM

    // Validate time is within office hours
    if (strtotime($startTime) < strtotime($officeStart) || 
        strtotime($endTime) > strtotime($officeEnd)) {
        throw new Exception('Availability must be set between 9:00 AM and 9:00 PM');
    }

    // Check for overlapping availability
    $checkQuery = "SELECT * FROM ida_availability 
                  WHERE consultant_id = ? 
                  AND date = ? 
                  AND (
                      (start_time <= ? AND end_time > ?) OR
                      (start_time < ? AND end_time >= ?)
                  )";
    
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param('isssss', 
        $_SESSION['user_id'], 
        $date, 
        $startTime, 
        $startTime,
        $endTime, 
        $endTime
    );
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('This time slot overlaps with existing availability');
    }

    // Insert new availability
    $query = "INSERT INTO ida_availability (consultant_id, date, start_time, end_time) 
              VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('isss', 
        $_SESSION['user_id'], 
        $date, 
        $startTime, 
        $endTime
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Error adding availability');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 