<?php
// Only start a session if it hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if no active session
    header("Location: ../login.php");
    exit;
}
?>
