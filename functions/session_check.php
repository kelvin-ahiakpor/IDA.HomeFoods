<?php
// Only start a session if it hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Get the project root directory with user directory
$projectRoot = '/~kelvin.ahiakpor/IDA_HOME_FOODS';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if no active session
    header("Location: {$projectRoot}/view/login.php");
    exit;
}
?>
