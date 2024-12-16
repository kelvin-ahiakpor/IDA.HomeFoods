<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
require_once '../../functions/dashboard-helpers.php';
checkUserAccess('Admin');

// Fetch dashboard metrics
function fetchDashboardMetrics() {
    global $conn;
    
    // Get total counts and growth percentages
    $query = "SELECT 
        (SELECT COUNT(*) FROM ida_consultants WHERE status = 'Active') as total_consultants,
        (SELECT COUNT(DISTINCT client_id) FROM ida_bookings) as total_clients,
        (SELECT COUNT(DISTINCT booking_id) 
         FROM ida_bookings 
        ) as total_bookings,
        
        -- Calculate growth percentages (comparing this month to last month)
        COALESCE((SELECT 
            ROUND(((COUNT(CASE WHEN c.joined_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) THEN 1 END) * 100.0 / 
            NULLIF(COUNT(CASE WHEN c.joined_date >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH) 
                AND c.joined_date < DATE_SUB(CURDATE(), INTERVAL 1 MONTH) THEN 1 END), 0)) - 100), 1)
        FROM ida_consultants c WHERE status = 'Active'), 0) as consultant_growth,
        
        -- Calculate client growth based on first booking dates
        COALESCE((SELECT 
            ROUND(((COUNT(DISTINCT CASE WHEN b.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) THEN client_id END) * 100.0 / 
            NULLIF(COUNT(DISTINCT CASE WHEN b.created_at >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH) 
                AND b.created_at < DATE_SUB(CURDATE(), INTERVAL 1 MONTH) THEN client_id END), 0)) - 100), 1)
        FROM ida_bookings b), 0) as client_growth,
        
        -- Calculate booking growth
        COALESCE((SELECT 
            ROUND(((COUNT(DISTINCT CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) THEN booking_id END) * 100.0 / 
            NULLIF(COUNT(DISTINCT CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH) 
                AND created_at < DATE_SUB(CURDATE(), INTERVAL 1 MONTH) THEN booking_id END), 0)) - 100), 1)
        FROM ida_bookings 
        WHERE is_cancelled = 0
        ), 0) as booking_growth";
    
    $result = $conn->query($query);
    return $result->fetch_assoc();
}

// Fetch recent activities
function fetchRecentActivities($limit = 5) {
    global $conn;
    
    $query = "SELECT 
                l.*,
                u.first_name as admin_name,
                CASE 
                    WHEN l.affected_user_id IS NOT NULL THEN 
                        (SELECT CONCAT(first_name, ' ', last_name) 
                         FROM ida_users 
                         WHERE user_id = l.affected_user_id)
                END as affected_user_name
              FROM ida_admin_dashboard_logs l
              JOIN ida_users u ON l.admin_id = u.user_id
              ORDER BY l.timestamp DESC
              LIMIT ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Fetch ongoing sessions
function fetchOngoingSessions() {
    global $conn;
    
    $query = "SELECT 
                b.*,
                CONCAT(c.first_name, ' ', c.last_name) as client_name,
                CONCAT(co.first_name, ' ', co.last_name) as consultant_name,
                TIMESTAMPDIFF(MINUTE, b.time_slot, NOW()) as duration_minutes
              FROM ida_bookings b
              JOIN ida_users c ON b.client_id = c.user_id
              JOIN ida_users co ON b.consultant_id = co.user_id
              WHERE b.booking_date = CURDATE()
              AND b.time_slot <= NOW()
              AND b.completed_at IS NULL
              AND b.is_cancelled = 0
              LIMIT 5";
              
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch upcoming sessions
function fetchUpcomingSessions() {
    global $conn;
    
    $query = "SELECT 
                b.*,
                CONCAT(c.first_name, ' ', c.last_name) as client_name,
                CONCAT(co.first_name, ' ', co.last_name) as consultant_name,
                TIMESTAMPDIFF(MINUTE, NOW(), CONCAT(b.booking_date, ' ', b.time_slot)) as minutes_until_start
              FROM ida_bookings b
              JOIN ida_users c ON b.client_id = c.user_id
              JOIN ida_users co ON b.consultant_id = co.user_id
              WHERE CONCAT(b.booking_date, ' ', b.time_slot) > NOW()
              AND b.is_cancelled = 0
              AND b.completed_at IS NULL
              ORDER BY b.booking_date ASC, b.time_slot ASC
              LIMIT 1";
              
    $result = $conn->query($query);
    return $result->fetch_assoc();
}

// Fetch top clients
function fetchTopClients() {
    global $conn;
    
    $query = "SELECT 
                u.user_id,
                CONCAT(u.first_name, ' ', u.last_name) as client_name,
                COUNT(b.booking_id) as session_count
              FROM ida_users u
              JOIN ida_bookings b ON u.user_id = b.client_id
              WHERE b.booking_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
              GROUP BY u.user_id
              ORDER BY session_count DESC
              LIMIT 5";
              
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch top consultants
function fetchTopConsultants() {
    global $conn;
    
    $query = "SELECT 
                u.user_id,
                CONCAT(u.first_name, ' ', u.last_name) as consultant_name,
                COUNT(b.booking_id) as completed_sessions,
                AVG(sr.rating) as average_rating
              FROM ida_users u
              JOIN ida_bookings b ON u.user_id = b.consultant_id
              LEFT JOIN ida_session_ratings sr ON b.booking_id = sr.booking_id
              WHERE b.completed_at IS NOT NULL
              GROUP BY u.user_id
              ORDER BY completed_sessions DESC, average_rating DESC
              LIMIT 5";
              
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch all data
$metrics = fetchDashboardMetrics();
$recentActivities = fetchRecentActivities();
$ongoingSessions = fetchOngoingSessions();
$upcomingSessions = fetchUpcomingSessions();
$topClients = fetchTopClients();
$topConsultants = fetchTopConsultants();
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
                        <span class="bg-idafu-lightBlue px-2 py-1 rounded-full text-xs">
                            <?php echo ($metrics['consultant_growth'] >= 0 ? '+' : '') . number_format($metrics['consultant_growth'], 1); ?>%
                        </span>
                    </div>
                    <p class="text-2xl font-semibold mt-2"><?php echo $metrics['total_consultants']; ?></p>
                </div>
                <div class="stats-card">
                    <div class="flex items-center justify-between">
                        <h3 class="text-gray-500 text-sm font-medium">Total Clients</h3>
                        <span class="<?php echo $metrics['client_growth'] >= 0 ? 'bg-idafu-lightBlue' : 'bg-idafu-lightBlue'; ?> px-2 py-1 rounded-full text-xs">
                            <?php echo ($metrics['client_growth'] >= 0 ? '+' : '') . $metrics['client_growth']; ?>%
                        </span>
                    </div>
                    <p class="text-2xl font-semibold mt-2"><?php echo $metrics['total_clients']; ?></p>
                </div>
                <div class="stats-card">
                    <div class="flex items-center justify-between">
                        <h3 class="text-gray-500 text-sm font-medium">Total Bookings</h3>
                        <span class="<?php echo $metrics['booking_growth'] >= 0 ? 'bg-idafu-lightBlue' : 'bg-idafu-lightBlue'; ?> px-2 py-1 rounded-full text-xs">
                            <?php echo ($metrics['booking_growth'] >= 0 ? '+' : '') . $metrics['booking_growth']; ?>%
                        </span>
                    </div>
                    <p class="text-2xl font-semibold mt-2"><?php echo $metrics['total_bookings']; ?></p>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Activities</h3>
                <div class="space-y-4">
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="bg-idafu-lightBlue p-2 rounded-full">
                                    <!-- Icon based on action_type -->
                                    <?php echo getActivityIcon($activity['action_type']); ?>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-idafu-primary">
                                        <?php echo formatActivityMessage($activity); ?>
                                    </p>
                                    <p class="text-sm text-idafu-accentDeeper">
                                        <?php echo timeAgo($activity['timestamp']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Sessions Overview -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Current Session</h3>
                    <div class="space-y-4">
                        <div class="bg-idafu-lightBlue rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium">Food Business Strategy</h4>
                                    <p class="text-sm text-gray-600">Consultant: Sarah Johnson</p>
                                    <p class="text-sm text-gray-600">Client: Tech Foods Ltd.</p>
                                </div>
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">In Progress</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Next Upcoming Session</h3>
                    <div class="space-y-4">
                        <?php if (empty($upcomingSessions)): ?>
                            <p class="text-gray-500 text-sm">No upcoming sessions</p>
                        <?php else: ?>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-medium"><?php echo htmlspecialchars($upcomingSessions['consultant_name']); ?></h4>
                                        <p class="text-sm text-gray-600">Client: <?php echo htmlspecialchars($upcomingSessions['client_name']); ?></p>
                                        <p class="text-sm text-gray-600">
                                            <?php 
                                                echo "Date: " . date('M d, Y', strtotime($upcomingSessions['booking_date'])) . " at " . date('g:i A', strtotime($upcomingSessions['time_slot'])) . "<br>";
                                            ?>
                                        </p>
                                    </div>
                                    <span class="px-3 py-1 bg-idafu-accent text-idafu-accentMutedGold rounded-full text-sm">Scheduled</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Top Performers -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Active Clients</h3>
                    <div class="space-y-4">
                        <?php 
                        if (empty($topClients)): ?>
                            <p class="text-gray-500 text-sm">No client activity yet</p>
                        <?php else:
                            foreach ($topClients as $index => $client): ?>
                                <div class="flex items-center space-x-4 p-3 hover:bg-gray-50 rounded-lg">
                                    <div class="w-10 h-10 bg-idafu-lightBlue rounded-full flex items-center justify-center">
                                        <span class="font-medium"><?php echo $index + 1; ?></span>
                                    </div>
                                    <div>
                                        <h4 class="font-medium"><?php echo htmlspecialchars($client['client_name']); ?></h4>
                                        <p class="text-sm text-gray-600"><?php echo $client['session_count']; ?> sessions this month</p>
                                    </div>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Booked Consultants</h3>
                    <div class="space-y-4">
                        <?php 
                        if (empty($topConsultants)): ?>
                            <p class="text-gray-500 text-sm">No consultant activity yet</p>
                        <?php else:
                            foreach ($topConsultants as $index => $consultant): ?>
                                <div class="flex items-center space-x-4 p-3 hover:bg-gray-50 rounded-lg">
                                    <div class="w-10 h-10 bg-idafu-lightBlue rounded-full flex items-center justify-center">
                                        <span class="text-bg-idafu-lightBlue font-medium"><?php echo $index + 1; ?></span>
                                    </div>
                                    <div>
                                        <h4 class="font-medium"><?php echo htmlspecialchars($consultant['consultant_name']); ?></h4>
                                        <p class="text-sm text-gray-600">
                                            <?php echo $consultant['completed_sessions']; ?> sessions completed
                                            <?php if ($consultant['average_rating']): ?>
                                                • <?php echo number_format($consultant['average_rating'], 1); ?> ★
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../../assets/js/script-dashboard.js" defer></script>
</body>

<?php include('../../assets/includes/footer-dashboard.php'); ?>
</html>
