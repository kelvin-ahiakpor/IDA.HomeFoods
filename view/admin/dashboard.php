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
    <header class="bg-white shadow-sm fixed w-full z-10">
        <div class="max-w-full mx-auto">
            <div class="flex items-center justify-between px-6 py-3">
                <div class="flex items-center space-x-4">
                    <button class="lg:hidden p-2 hover:bg-gray-100 rounded-lg" id="menuBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <img src="../../assets/images/IDAFU-logo-min-black.png" alt="IDAFU Logo" class="h-9">
                </div>
                <nav class="hidden lg:flex items-center">
                    <div class="flex space-x-12">
                        <a href="./dashboard.php" class="nav-item active">Overview</a>
                        <a href="./consultants.php" class="nav-item">Consultants</a>
                        <a href="./clients.php" class="nav-item">Clients</a>
                        <a href="./reports.php" class="nav-item">Reports</a>
                    </div>
                </nav>
                <div class="flex items-center">
                    <button class="relative group p-2 hover:bg-gray-100 rounded-full" id="profileBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="text-gray-600" viewBox="0 0 16 16">
                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Profile Modal -->
    <div id="profileModal" class="hidden fixed top-14 right-4 bg-white rounded-lg shadow-lg p-4 w-72 z-20">
        <div class="flex flex-col">
            <div class="flex items-center space-x-3 pb-3">
                <div class="bg-gray-200 rounded-full p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="text-gray-600" viewBox="0 0 16 16">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                    </svg>
                </div>
                <div>
                <h3 class="font-medium"><?php echo htmlspecialchars($_SESSION['first_name']) . ' ' . htmlspecialchars($_SESSION['last_name']); ?></h3>
                    <p class="text-sm text-gray-500">Admin</p>
                </div>
            </div>
            <div class="border-t pt-3">
                <a href="../../view/manage_account.php" class="flex items-center space-x-3 px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                    <span>Account Settings</span>
                </a>
                <a href="../../actions/logout.php" class="flex items-center space-x-3 px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                    <span>Sign Out</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <div id="mobileMenu" class="fixed inset-0 bg-gray-800 bg-opacity-50 z-50 hidden lg:hidden">
        <div class="bg-white w-64 h-full transform transition-transform duration-300 -translate-x-full" id="mobileMenuContent">
            <div class="p-4 border-b"> <img src="../../assets/images/IDAFU-logo-min-black.png" alt="IDAFU Logo" class="h-8"> </div>
            <nav class="p-4">
                <ul class="space-y-2">
                    <li> <a href="./dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100"> <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg> <span>Overview</span> </a> </li>
                    <li> <a href="./consultants.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100"> <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg> <span>Consultants</span> </a> </li>
                    <li> <a href="./clients.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100"> <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg> <span>Clients</span> </a> </li>
                    <li> <a href="./reports.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100"> <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg> <span>Reports</span> </a> </li>
                </ul>
            </nav>
        </div>
    </div>

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