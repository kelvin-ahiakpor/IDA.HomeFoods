<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Admin');

// Mock data - replace with actual database queries
$revenueData = [
    'current_month' => 12500,
    'previous_month' => 10800,
    'growth' => 15.7
];

$bookingMetrics = [
    'total_bookings' => 245,
    'completed_sessions' => 180,
    'cancelled_sessions' => 15,
    'no_shows' => 8,
    'completion_rate' => 85.2
];

$consultantMetrics = [
    'total_active' => 12,
    'avg_rating' => 4.7,
    'avg_sessions' => 15.3,
    'top_performers' => [
        ['name' => 'Sarah Johnson', 'sessions' => 45, 'rating' => 4.9],
        ['name' => 'Mike Wilson', 'sessions' => 38, 'rating' => 4.8]
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('../../assets/includes/head-dashboard.php'); ?>
    <title>Reports | idafü</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <!-- Add Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-50 font-sans antialiased">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <?php include('../../assets/includes/header-dashboard.php'); ?>

        <!-- Profile Modal -->
        <?php include('../../assets/includes/profile-modal.php'); ?>

        <!-- Mobile Navigation Menu -->
        <?php include('../../assets/includes/mobile-menu.php'); ?>
        
        <!-- Main Content -->
        <main class="flex-grow pt-16 px-4 sm:px-6">
            <div class="max-w-7xl mx-auto py-6">
                <!-- Page Title and Date Filter -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4 sm:mb-0">Reports & Analytics</h1>
                    <div class="w-full sm:w-auto flex space-x-4">
                        <select class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-idafu-primary">
                            <option>Last 30 Days</option>
                            <option>Last 90 Days</option>
                            <option>This Year</option>
                            <option>Custom Range</option>
                        </select>
                        <button class="px-4 py-2 bg-idafu-primary text-white rounded-lg hover:bg-opacity-90">
                            Export PDF
                        </button>
                    </div>
                </div>

                <!-- Key Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Revenue Card -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-gray-500 text-sm font-medium">Revenue</h3>
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">
                                +<?php echo $revenueData['growth']; ?>%
                            </span>
                        </div>
                        <p class="text-2xl font-semibold">$<?php echo number_format($revenueData['current_month']); ?></p>
                        <p class="text-sm text-gray-500 mt-1">vs $<?php echo number_format($revenueData['previous_month']); ?> last month</p>
                    </div>

                    <!-- Bookings Overview -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-gray-500 text-sm font-medium mb-4">Booking Metrics</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm">Completion Rate</span>
                                <span class="font-semibold"><?php echo $bookingMetrics['completion_rate']; ?>%</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm">No-show Rate</span>
                                <span class="font-semibold"><?php echo round(($bookingMetrics['no_shows'] / $bookingMetrics['total_bookings']) * 100, 1); ?>%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Consultant Performance -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-gray-500 text-sm font-medium mb-4">Consultant Metrics</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm">Avg Rating</span>
                                <span class="font-semibold"><?php echo $consultantMetrics['avg_rating']; ?>/5.0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm">Avg Sessions/Month</span>
                                <span class="font-semibold"><?php echo $consultantMetrics['avg_sessions']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Booking Trends -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Booking Trends</h3>
                        <div class="relative">
                            <canvas id="bookingTrendsChart"></canvas>
                        </div>
                    </div>

                    <!-- Revenue Trends -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Revenue Trends</h3>
                        <div class="relative">
                            <canvas id="revenueTrendsChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Performers Table -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Performing Consultants</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="bg-idafu-lightBlue text-gray-700">
                                <tr>
                                    <th class="px-4 py-3">Consultant</th>
                                    <th class="px-4 py-3">Sessions</th>
                                    <th class="px-4 py-3">Rating</th>
                                    <th class="px-4 py-3">Revenue</th>
                                    <th class="px-4 py-3">Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($consultantMetrics['top_performers'] as $consultant): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900"><?php echo $consultant['name']; ?></td>
                                    <td class="px-4 py-3"><?php echo $consultant['sessions']; ?></td>
                                    <td class="px-4 py-3"><?php echo $consultant['rating']; ?>/5.0</td>
                                    <td class="px-4 py-3">$<?php echo number_format($consultant['sessions'] * 100); ?></td>
                                    <td class="px-4 py-3">
                                        <span class="text-green-600">↑ 12%</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?php include('../../assets/includes/footer-dashboard.php'); ?>
    </div>

    <script src="../../assets/js/script-dashboard.js" defer></script>
    <script>
        // Initialize charts when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Booking Trends Chart
            new Chart(document.getElementById('bookingTrendsChart'), {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Total Bookings',
                        data: [65, 78, 90, 85, 95, 110],
                        borderColor: '#435F6F',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Revenue Trends Chart
            new Chart(document.getElementById('revenueTrendsChart'), {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Revenue',
                        data: [8500, 9200, 11000, 10500, 12500, 13800],
                        backgroundColor: 'rgba(67, 95, 111, 0.2)',
                        borderColor: '#435F6F',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>