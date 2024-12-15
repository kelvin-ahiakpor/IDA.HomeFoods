<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Consultant');

// Fetch earnings data for the logged-in consultant
function fetchEarnings($userId) {
    global $conn;
    $query = "SELECT SUM(price) AS total_earnings, COUNT(session_id) AS total_sessions 
              FROM ida_consultant_sessions 
              WHERE consultant_id = ? AND status = 'Completed'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Fetch this month's earnings
function fetchMonthlyEarnings($userId) {
    global $conn;
    $query = "SELECT SUM(price) AS monthly_earnings 
              FROM ida_consultant_sessions 
              WHERE consultant_id = ? AND status = 'Completed' 
              AND MONTH(start_time) = MONTH(CURRENT_DATE()) 
              AND YEAR(start_time) = YEAR(CURRENT_DATE())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$earnings = fetchEarnings($_SESSION['user_id']);
$monthlyEarnings = fetchMonthlyEarnings($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('../../assets/includes/head-dashboard.php'); ?>
    <title>View Earnings | idafü</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <?php include('../../assets/includes/header-consultant.php'); ?>

        <!-- Profile Modal -->
        <?php include('../../assets/includes/profile-modal.php'); ?>

        <!-- Mobile Navigation Menu -->
        <?php include('../../assets/includes/mobile-menu.php'); ?>

        <!-- Main Content -->
        <main class="flex-grow pt-16 px-4 sm:px-6">
            <div class="max-w-7xl mx-auto py-2 sm:py-4">
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6">View Earnings</h1>

                <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Earnings Summary</h2>
                    <p class="text-sm text-gray-600">Total Earnings: $<?php echo number_format($earnings['total_earnings'] ?? 0, 2); ?></p>
                    <p class="text-sm text-gray-600">Total Completed Sessions: <?php echo $earnings['total_sessions'] ?? 0; ?></p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">This Month's Earnings</h2>
                    <p class="text-sm text-gray-600">Total Earnings This Month: $<?php echo number_format($monthlyEarnings['monthly_earnings'] ?? 0, 2); ?></p>
                </div>

                <!-- Coming Soon Message -->
                <div class="bg-white rounded-lg shadow-sm p-4 mt-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">More Metrics Coming Soon!</h2>
                    <p class="text-sm text-gray-600">Stay tuned for charts and analytics to help you track your performance better.</p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?php include('../../assets/includes/footer-dashboard.php'); ?>
    </div>

    <script src="../../assets/js/script-dashboard.js" defer></script>
</body>
</html> 