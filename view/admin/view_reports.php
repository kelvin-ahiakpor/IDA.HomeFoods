<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Admin');

// Calculate revenue data
$revenueQuery = "SELECT 
    -- Current month revenue
    SUM(CASE 
        WHEN b.completed_at >= DATE_FORMAT(NOW() ,'%Y-%m-01')
        THEN c.hourly_rate 
        ELSE 0 
    END) as current_month,
    -- Previous month revenue
    SUM(CASE 
        WHEN b.completed_at >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH) ,'%Y-%m-01')
        AND b.completed_at < DATE_FORMAT(NOW() ,'%Y-%m-01')
        THEN c.hourly_rate 
        ELSE 0 
    END) as previous_month
FROM ida_bookings b
JOIN ida_consultants c ON b.consultant_id = c.consultant_id
WHERE b.completed_at IS NOT NULL";

$result = $conn->query($revenueQuery);
$revenueData = $result->fetch_assoc();

// Calculate growth percentage
$revenueData['growth'] = $revenueData['previous_month'] > 0 
    ? round((($revenueData['current_month'] - $revenueData['previous_month']) / $revenueData['previous_month']) * 100, 1)
    : 0;

// Calculate booking metrics
$bookingQuery = "SELECT 
    COUNT(DISTINCT booking_id) as total_bookings,
    COUNT(CASE WHEN completed_at IS NOT NULL THEN 1 END) as completed_sessions,
    COUNT(CASE WHEN is_cancelled = 1 THEN 1 END) as cancelled_sessions,
    COUNT(CASE WHEN completed_at IS NULL AND is_cancelled = 0 AND booking_date < CURDATE() THEN 1 END) as no_shows
FROM ida_bookings";

$result = $conn->query($bookingQuery);
$bookingMetrics = $result->fetch_assoc();

// Calculate completion rate
$bookingMetrics['completion_rate'] = $bookingMetrics['total_bookings'] > 0
    ? round(($bookingMetrics['completed_sessions'] / $bookingMetrics['total_bookings']) * 100, 1)
    : 0;

// Calculate consultant metrics
$consultantQuery = "SELECT 
    COUNT(CASE WHEN status = 'Active' THEN 1 END) as total_active,
    ROUND(AVG(CASE WHEN status = 'Active' THEN 
        (SELECT ROUND(AVG(rating),1)
         FROM ida_session_ratings sr 
         JOIN ida_consultant_sessions cs ON sr.rating_id = cs.session_id 
         WHERE cs.consultant_id = c.consultant_id)
    END), 1) as avg_rating
FROM ida_consultants c";

$result = $conn->query($consultantQuery);
$consultantMetrics = $result->fetch_assoc();

// Calculate average sessions per consultant
$avgSessionsQuery = "SELECT 
    AVG(session_count) as avg_sessions
FROM (
    SELECT consultant_id, COUNT(*) as session_count
    FROM ida_bookings
    WHERE completed_at IS NOT NULL
    AND completed_at >= DATE_FORMAT(NOW() ,'%Y-%m-01')
    GROUP BY consultant_id
) as consultant_sessions";

$result = $conn->query($avgSessionsQuery);
$avgSessions = $result->fetch_assoc();
$consultantMetrics['avg_sessions'] = round($avgSessions['avg_sessions'] ?? 0, 1);

// Get top performing consultants
$topPerformersQuery = "SELECT 
    CONCAT(u.first_name, ' ', u.last_name) as name,
    COUNT(b.booking_id) as sessions,
    AVG(sr.rating) as rating,
    SUM(c.hourly_rate) as revenue
FROM ida_users u
JOIN ida_consultants c ON u.user_id = c.consultant_id
JOIN ida_bookings b ON c.consultant_id = b.consultant_id
LEFT JOIN ida_consultant_sessions cs ON b.booking_id = cs.session_id
LEFT JOIN ida_session_ratings sr ON cs.session_id = sr.rating_id
WHERE b.completed_at IS NOT NULL
AND b.completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY u.user_id
ORDER BY sessions DESC, rating DESC
LIMIT 2";

$result = $conn->query($topPerformersQuery);
$consultantMetrics['top_performers'] = [];
while ($row = $result->fetch_assoc()) {
    $consultantMetrics['top_performers'][] = [
        'name' => $row['name'],
        'sessions' => $row['sessions'],
        'rating' => round($row['rating'] ?? 0, 1),
        'revenue' => $row['revenue']
    ];
}
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
                                    <td class="px-4 py-3">$<?php echo number_format($consultant['revenue']); ?></td>
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
</body>
</html>