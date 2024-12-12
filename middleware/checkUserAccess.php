<?php
// Place this file in a middleware or includes folder
function checkUserAccess($allowedUserType)
{
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }

    // Check if user type matches allowed type
    if ($_SESSION['role'] !== $allowedUserType) {
        // Redirect based on user type
        if ($_SESSION['role'] === 'Admin') {
            header("Location: ../admin/dashboard.php");
        } elseif ($_SESSION['role'] === 'Consultant') {
            header("Location: ../consultant/manage_bookings.php");
        } elseif ($_SESSION['role'] === 'Client') {
            header("Location: ../user/dashboard.php");
        }
        exit;
    }
}
