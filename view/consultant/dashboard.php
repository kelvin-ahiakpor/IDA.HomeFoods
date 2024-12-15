<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Consultant');

// Fetch consultant metrics
function fetchConsultantMetrics($userId) {
    global $conn;
    
    $query = "SELECT c.total_clients, c.rating, c.status 
              FROM ida_consultants c 
              WHERE c.consultant_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    // Get today's sessions count
    $today = date('Y-m-d');
    $sessionsQuery = "SELECT COUNT(*) as sessions_today 
                     FROM ida_bookings 
                     WHERE consultant_id = ? 
                     AND DATE(booking_date) = ?
                     AND status = 'Approved'";
    $stmt = $conn->prepare($sessionsQuery);
    $stmt->bind_param('is', $userId, $today);
    $stmt->execute();
    $sessionsResult = $stmt->get_result()->fetch_assoc();
    
    // Since there's no payments table in the schema yet, we'll return 0 for earnings
    $totalEarnings = 0;
    
    return [
        'total_clients' => $result['total_clients'] ?? 0,
        'sessions_today' => $sessionsResult['sessions_today'] ?? 0,
        'total_earnings' => $totalEarnings,
        'rating' => $result['rating'] ?? 0,
        'status' => $result['status']
    ];
}

// Fetch today's sessions
function fetchTodaySessions($userId) {
    global $conn;
    
    $today = date('Y-m-d');
    $query = "SELECT b.*, 
                     u.first_name, u.last_name,
                     TIME_FORMAT(b.time_slot, '%H:%i') as formatted_time
              FROM ida_bookings b
              JOIN ida_users u ON b.client_id = u.user_id
              WHERE b.consultant_id = ? 
              AND DATE(b.booking_date) = ?
              AND b.status = 'Approved'
              ORDER BY b.time_slot ASC";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('is', $userId, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $sessions = [];
    while ($row = $result->fetch_assoc()) {
        $sessions[] = [
            'client_name' => $row['first_name'] . ' ' . $row['last_name'],
            'time' => $row['formatted_time'],
            'duration' => 60, // Default duration since it's not in the schema
            'status' => $row['status'],
            'meeting_link' => 'https://meet.idafu.com/session/' . $row['booking_id'] // Example link
        ];
    }
    
    return $sessions;
}

// Fetch recent activities
function fetchRecentActivities($userId) {
    global $conn;
    
    $query = "SELECT 'Booking' as type,
                     u.first_name, u.last_name,
                     b.created_at as activity_time,
                     b.booking_date, b.time_slot
              FROM ida_bookings b
              JOIN ida_users u ON b.client_id = u.user_id
              WHERE b.consultant_id = ?
              AND b.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
              ORDER BY b.created_at DESC
              LIMIT 5";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $timeAgo = getTimeAgo($row['activity_time']);
        $sessionTime = date('H:i', strtotime($row['time_slot']));
        $sessionDate = date('d M Y', strtotime($row['booking_date']));
        
        $activities[] = [
            'type' => 'New Booking',
            'client' => $row['first_name'] . ' ' . $row['last_name'],
            'time' => $timeAgo,
            'details' => "Booked a session for $sessionDate at $sessionTime"
        ];
    }
    
    return $activities;
}

// Helper function to format time ago
function getTimeAgo($timestamp) {
    $diff = time() - strtotime($timestamp);
    
    if ($diff < 60) {
        return "Just now";
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . " minute" . ($mins > 1 ? "s" : "") . " ago";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } else {
        $days = floor($diff / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    }
}

// Fetch all data
$metrics = fetchConsultantMetrics($_SESSION['user_id']);
$todaysSessions = fetchTodaySessions($_SESSION['user_id']);
$recentActivities = fetchRecentActivities($_SESSION['user_id']);

// Rest of your HTML remains the same, just use the fetched data
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('../../assets/includes/head-dashboard.php'); ?>
    <title>Consultant Dashboard | idafü</title>
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
                <!-- Welcome Section -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-800">
                        Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>
                    </h1>
                    <button onclick="window.location.href='./availability.php'" 
                            class="px-4 py-2 bg-idafu-primary text-white rounded-lg hover:bg-idafu-primaryDarker transition-colors duration-200">
                        Manage Availability
                    </button>
                </div>

                <!-- Metrics Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="text-gray-500 text-sm">Total Clients</h3>
                        <p class="text-2xl font-semibold mt-1"><?php echo $metrics['total_clients']; ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="text-gray-500 text-sm">Today's Sessions</h3>
                        <p class="text-2xl font-semibold mt-1"><?php echo $metrics['sessions_today']; ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="text-gray-500 text-sm">Total Earnings</h3>
                        <p class="text-2xl font-semibold mt-1">$<?php echo $metrics['total_earnings']; ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="text-gray-500 text-sm">Rating</h3>
                        <p class="text-2xl font-semibold mt-1"><?php echo $metrics['rating']; ?>/5.0</p>
                    </div>
                </div>

                <!-- Today's Sessions -->
                <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Today's Sessions</h2>
                    <div class="space-y-4">
                        <?php foreach ($todaysSessions as $session): ?>
                            <div class="flex justify-between items-center bg-gray-50 p-4 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($session['client_name']); ?></p>
                                    <p class="text-sm text-gray-600">
                                        <?php echo $session['time']; ?> • <?php echo $session['duration']; ?> minutes
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                                        <?php echo $session['status']; ?>
                                    </span>
                                    <?php if ($session['meeting_link']): ?>
                                        <a href="<?php echo $session['meeting_link']; ?>" 
                                           class="px-4 py-2 bg-idafu-primary text-white rounded hover:bg-idafu-primaryDarker transition-colors duration-200">
                                            Join Meeting
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Activities</h2>
                    <div class="space-y-4">
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-idafu-lightBlue rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-idafu-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900"><?php echo $activity['type']; ?></p>
                                    <p class="text-sm text-gray-600"><?php echo $activity['details']; ?></p>
                                    <p class="text-xs text-gray-500 mt-1"><?php echo $activity['time']; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
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