<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Admin');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('../../assets/includes/head-dashboard.php'); ?>
    <title>Dashboard | idafü</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>

<body class="bg-gray-50 font-sans antialiased">
    <!-- Header -->
    <?php include('../../assets/includes/header-dashboard.php'); ?>

    <!-- Profile Modal -->
    <?php include('../../assets/includes/profile-modal.php'); ?>

    <!-- Mobile Navigation Menu -->
    <?php include('../../assets/includes/mobile-menu.php'); ?>

    <!-- Main Content -->
    <main class="pt-16 px-6">
        <div class="max-w-7xl mx-auto py-6">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="stats-card">
                    <div class="flex items-center justify-between">
                        <h3 class="text-gray-500 text-sm font-medium">Total Consultants</h3>
                        <span class="bg-idafu-lightBlue bg-idafu-lightBlue px-2 py-1 rounded-full text-xs">+12%</span>
                    </div>
                    <p class="text-2xl font-semibold mt-2">12</p>
                </div>
                <div class="stats-card">
                    <div class="flex items-center justify-between">
                        <h3 class="text-gray-500 text-sm font-medium">Total Clients</h3>
                        <span class="bg-idafu-lightBlue bg-idafu-lightBlue px-2 py-1 rounded-full text-xs">+25%</span>
                    </div>
                    <p class="text-2xl font-semibold mt-2">45</p>
                </div>
                <div class="stats-card">
                    <div class="flex items-center justify-between">
                        <h3 class="text-gray-500 text-sm font-medium">Total Bookings</h3>
                        <span class="bg-idafu-lightBlue bg-idafu-lightBlue px-2 py-1 rounded-full text-xs">+18%</span>
                    </div>
                    <p class="text-2xl font-semibold mt-2">78</p>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Activities</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <div class="bg-idafu-lightBlue p-2 rounded-full">
                                <svg class="w-5 h-5 text-idafu-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-idafu-primary">Consultant John Doe updated profile</p>
                                <p class="text-sm text-idafu-accentDeeper">2 hours ago</p>
                            </div>
                        </div>
                        <!-- View Logs Button -->
                        <button class="text-idafu-primary text-sm hover:underline">View Logs</button>
                    </div>
                    <!-- More activity items... -->
                </div>
            </div>
            <!-- Sessions Overview -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Ongoing Sessions</h3>
                    <div class="space-y-4">
                        <div class="bg-idafu-lightBlue rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium">Technical Consultation</h4>
                                    <p class="text-sm text-gray-600">Client: Tech Solutions Inc.</p>
                                    <p class="text-sm text-gray-600">Duration: 1h 30m</p>
                                </div>
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">In Progress</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Upcoming Sessions</h3>
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium">Strategy Planning</h4>
                                    <p class="text-sm text-gray-600">Client: Innovation Corp</p>
                                    <p class="text-sm text-gray-600">Starts in: 2 hours</p>
                                </div>
                                <span class="px-3 py-1 bg-idafu-accent text-idafu-accentMutedGold rounded-full text-sm">Scheduled</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performers -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Top 5 Active Clients</h3>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4 p-3 hover:bg-gray-50 rounded-lg">
                            <div class="w-10 h-10 bg-idafu-lightBlue rounded-full flex items-center justify-center">
                                <span class="font-medium">1</span>
                            </div>
                            <div>
                                <h4 class="font-medium">Tech Solutions Inc.</h4>
                                <p class="text-sm text-gray-600">32 sessions this month</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Top 5 Booked Consultants</h3>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4 p-3 hover:bg-gray-50 rounded-lg">
                            <div class="w-10 h-10 bg-idafu-lightBlue rounded-full flex items-center justify-center">
                                <span class="text-bg-idafu-lightBlue font-medium">1</span>
                            </div>
                            <div>
                                <h4 class="font-medium">Sarah Johnson</h4>
                                <p class="text-sm text-gray-600">45 sessions completed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../../assets/js/script-dashboard.js" defer></script>
</body>

<?php include('../../assets/includes/footer-dashboard.php'); ?>
</html>