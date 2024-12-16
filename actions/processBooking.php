<?php
session_start();
require_once '../db/config.php';
require_once '../middleware/checkUserAccess.php';
checkUserAccess('Client');

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $availabilityId = $data['availability_id'] ?? null;
    $notes = $data['notes'] ?? '';

    if (!$availabilityId) {
        throw new Exception('Availability ID is required');
    }

    // Get availability details
    $query = "SELECT a.*, u.email as consultant_email, u.first_name as consultant_name,
                     c.hourly_rate
              FROM ida_availability a
              JOIN ida_users u ON a.consultant_id = u.user_id
              JOIN ida_consultants c ON a.consultant_id = c.consultant_id
              WHERE a.availability_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $availabilityId);
    $stmt->execute();
    $availability = $stmt->get_result()->fetch_assoc();

    if (!$availability) {
        throw new Exception('Invalid availability slot');
    }

    // Start transaction
    $conn->begin_transaction();

    // Create booking
    $bookingQuery = "INSERT INTO ida_bookings (client_id, consultant_id, booking_date, time_slot, status, created_at, notes)
                     VALUES (?, ?, ?, ?, 'Approved', NOW(), ?)";
    
    $stmt = $conn->prepare($bookingQuery);
    $stmt->bind_param('iisss', 
        $_SESSION['user_id'],
        $availability['consultant_id'],
        $availability['date'],
        $availability['start_time'],
        $notes
    );
    $stmt->execute();
    $bookingId = $stmt->insert_id;

    // Send emails
    sendBookingEmails($conn, $bookingId, $availability, $_SESSION['email'], $_SESSION['first_name'], $notes);

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function sendBookingEmails($conn, $bookingId, $availability, $clientEmail, $clientName, $notes) {
    // Send email to client
    $clientSubject = "Booking Confirmation - Idafü Consultation";
    $clientMessage = "
        <!DOCTYPE html>
        <html>
        <head>
        <style>
            @import url('https://use.typekit.net/yoa3zvz.css');
            
            h2 {
                font-size: 1.8em;
                margin: 0 0 1rem;
            }
            
            h3 {
                font-family: superclarendon, serif;
                font-weight: 400;
                font-style: normal;
                color: #67A0B9;
                font-size: 1.2em;
                margin: 1.5rem 0 0.5rem;
            }
            
            ul {
                padding-left: 20px;
                margin: 1rem 0;
            }
            
            li {
                margin: 0.5rem 0;
            }
        </style>
        </head>
        <body>
            <h2>Booking Confirmed!</h2>
            <p>Hello $clientName,</p>
            <p>Your consultation has been booked successfully.</p>
            <p>Details:</p>
            <ul>
                <li>Date: " . date('l, F j, Y', strtotime($availability['date'])) . "</li>
                <li>Time: " . date('g:i A', strtotime($availability['start_time'])) . "</li>
                <li>Consultant: " . $availability['consultant_name'] . "</li>
                <li>Rate: $" . number_format($availability['hourly_rate'], 2) . "/hour</li>
                " . ($notes ? "<li>Meeting Notes: " . htmlspecialchars($notes) . "</li>" : "") . "
            </ul>
            <br>
            <p>If you need to make any changes to your booking, please contact us.</p>
            <br>
            <p>Best regards,</p>
            <h3>The Idafü Team</h3>
        </body>
        </html>
    ";

    // Send email to consultant
    $consultantSubject = "New Booking - Idafü Consultation";
    $consultantMessage = "
        <!DOCTYPE html>
        <html>
        <head>
        <style>
            @import url('https://use.typekit.net/yoa3zvz.css');
            
            h2 {
                font-size: 1.8em;
                margin: 0 0 1rem;
            }
            
            h3 {
                font-family: superclarendon, serif;
                font-weight: 400;
                font-style: normal;
                color: #67A0B9;
                font-size: 1.2em;
                margin: 1.5rem 0 0.5rem;
            }
            
            ul {
                padding-left: 20px;
                margin: 1rem 0;
            }
            
            li {
                margin: 0.5rem 0;
            }
        </style>
        </head>
        <body>
            <h2>New Booking Alert!</h2>
            <p>Hello " . $availability['consultant_name'] . ",</p>
            <p>You have a new consultation booking.</p>
            <p>Details:</p>
            <ul>
                <li>Date: " . date('l, F j, Y', strtotime($availability['date'])) . "</li>
                <li>Time: " . date('g:i A', strtotime($availability['start_time'])) . "</li>
                <li>Client: $clientName</li>
                " . ($notes ? "<li>Meeting Notes: " . htmlspecialchars($notes) . "</li>" : "") . "
            </ul>
            <br>
            <p>Please ensure you're available at the scheduled time.</p>
            <br>
            <p>Best regards,</p>
            <h3>The Idafü Team</h3>
        </body>
        </html>
    ";

    // Send emails
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: no-reply@idafu.com\r\n";

    mail($clientEmail, $clientSubject, $clientMessage, $headers);
    mail($availability['consultant_email'], $consultantSubject, $consultantMessage, $headers);

    // Log emails in database
    $stmt = $conn->prepare("INSERT INTO ida_emails (user_id, email_subject, email_body, sent_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['user_id'], $clientSubject, $clientMessage]);
    $stmt->execute([$availability['consultant_id'], $consultantSubject, $consultantMessage]);
} 