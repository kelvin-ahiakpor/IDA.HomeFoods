<?php
session_start();

function checkUserAccess($requiredRole) {
    // Get the project root directory with user directory
    $projectRoot = '/~kelvin.ahiakpor/IDA_HOME_FOODS';

    // If user is not logged in, redirect to login
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: {$projectRoot}/view/login.php");
        exit;
    }

    // If user's role doesn't match required role
    if ($_SESSION['role'] !== $requiredRole) {
        // Only redirect if we're not already on the correct dashboard
        $currentPath = $_SERVER['PHP_SELF'];
        $correctPath = '';
        
        switch ($_SESSION['role']) {
            case 'Admin':
                $correctPath = "{$projectRoot}/view/admin/dashboard.php";
                break;
            case 'Consultant':
                $correctPath = "{$projectRoot}/view/consultant/dashboard.php";
                break;
            case 'Client':
                $correctPath = "{$projectRoot}/view/client/dashboard.php";
                break;
            default:
                header("Location: {$projectRoot}/view/login.php");
                exit;
        }

        // Only redirect if we're not already on the correct path
        if ($currentPath !== $correctPath) {
            header("Location: {$correctPath}");
            exit;
        }
    }
}
