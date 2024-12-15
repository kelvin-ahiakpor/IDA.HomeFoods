<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Client');

// Mock data for demonstration
$upcomingSessions = [
    ['consultant' => 'Sarah Johnson', 'date' => '2024-03-25', 'time' => '2:00 PM'],
    ['consultant' => 'Mike Wilson', 'date' => '2024-03-28', 'time' => '11:00 AM']
];

$recentSessions = [
    ['consultant' => 'Sarah Johnson', 'date' => '2024-03-18', 'status' => 'Completed'],
    ['consultant' => 'Mike Wilson', 'date' => '2024-03-15', 'status' => 'Completed']
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('../../assets/includes/head-dashboard.php'); ?>
    <title>Client Dashboard | idafü</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>

<body class="bg-gray-50 font-sans antialiased">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <?php include('../../assets/includes/header-client.php'); ?>

        <!-- Profile Modal -->
        <?php include('../../assets/includes/profile-modal.php'); ?>

        <!-- Mobile Navigation Menu -->
        <?php include('../../assets/includes/mobile-menu.php'); ?>

        <!-- Main Content -->
        <main class="flex-grow pt-16 px-4 sm:px-6">
            <div class="max-w-7xl mx-auto py-2 sm:py-4">
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?></h1>

                <!-- Upcoming Sessions -->
                <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Upcoming Sessions</h2>
                    <ul class="space-y-4">
                        <?php foreach ($upcomingSessions as $session): ?>
                            <li class="flex justify-between items-center bg-idafu-lightBlue p-4 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo $session['consultant']; ?></p>
                                    <p class="text-sm text-gray-600"><?php echo $session['date']; ?> at <?php echo $session['time']; ?></p>
                                </div>
                                <button class="text-idafu-primary hover:bg-idafu-lightBlue px-3 py-1 rounded transition-colors duration-200">
                                    View Details
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Recent Sessions -->
                <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Sessions</h2>
                    <ul class="space-y-4">
                        <?php foreach ($recentSessions as $session): ?>
                            <li class="flex justify-between items-center bg-gray-50 p-4 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo $session['consultant']; ?></p>
                                    <p class="text-sm text-gray-600"><?php echo $session['date']; ?> - <?php echo $session['status']; ?></p>
                                </div>
                                <button class="text-idafu-primary hover:bg-idafu-lightBlue px-3 py-1 rounded transition-colors duration-200">
                                    View Details
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?php include('../../assets/includes/footer-dashboard.php'); ?>
    </div>

    <script src="../../assets/js/script-dashboard.js" defer></script>
</body>

</html> 