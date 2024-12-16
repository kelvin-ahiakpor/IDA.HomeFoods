<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Consultant');

// Fetch consultant metrics
function fetchConsultantMetrics($userId) {
    global $conn;
    
    // Query to get metrics including total unique clients and earnings from completed sessions
    $query = "SELECT 
                COUNT(DISTINCT b.client_id) as total_clients,
                c.status,
                COALESCE(ROUND(AVG(sr.rating), 1), 0) as rating,
                COALESCE((
                    SELECT SUM(cs.price)
                    FROM ida_consultant_sessions cs 
                    WHERE cs.consultant_id = c.consultant_id
                    AND cs.status = 'Completed'
                ), 0) as total_earnings
              FROM ida_consultants c 
              LEFT JOIN ida_bookings b ON c.consultant_id = b.consultant_id
              LEFT JOIN ida_session_ratings sr ON sr.consultant_id = c.consultant_id
              WHERE c.consultant_id = ?
              GROUP BY c.consultant_id, c.status";
              
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
    
    return [
        'total_clients' => $result['total_clients'] ?? 0,
        'sessions_today' => $sessionsResult['sessions_today'] ?? 0,
        'total_earnings' => $result['total_earnings'] ?? 0,
        'rating' => $result['rating'] ?? 0,
        'status' => $result['status'] ?? 'Pending'
    ];
}

// Fetch today's sessions
function fetchTodaySessions($userId) {
    global $conn;
    
    $today = date('Y-m-d');
    $query = "SELECT b.*, 
                     u.first_name, u.last_name,
                     TIME_FORMAT(b.time_slot, '%H:%i') as formatted_time,
                     b.completed_at,
                     c.hourly_rate
              FROM ida_bookings b
              JOIN ida_users u ON b.client_id = u.user_id
              JOIN ida_consultants c ON b.consultant_id = c.consultant_id
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
            'duration' => 60,
            'status' => $row['status'],
            'booking_id' => $row['booking_id'],
            'hourly_rate' => $row['hourly_rate'],
            'completed_at' => $row['completed_at'],
            'meeting_link' => 'https://meet.google.com/auw-sofx-tho' . $row['booking_id']
        ];
    }
    
    return $sessions;
}

// Fetch recent activities
function fetchRecentActivities($userId) {
    global $conn;
    
    $query = "SELECT 'Booking' as type,
                     CONCAT(u.first_name, ' ', u.last_name) as client_name,
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
            'client' => $row['client_name'],
            'time' => $timeAgo,
            'details' => "Session booked by {$row['client_name']} for $sessionDate at $sessionTime"
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
                    <button onclick="window.location.href='./manage_availability.php'" 
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
                        <p class="text-2xl font-semibold mt-1">$<?php echo number_format($metrics['total_earnings'], 2); ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="text-gray-500 text-sm">Rating</h3>
                        <p class="text-2xl font-semibold mt-1"><?php echo number_format($metrics['rating'], 1); ?>/5.0</p>
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
                                    <span class="px-3 py-1 <?php echo $session['completed_at'] ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?> rounded-full text-sm">
                                        <?php echo $session['completed_at'] ? 'Completed' : $session['status']; ?>
                                    </span>
                                    <?php if ($session['meeting_link'] && !$session['completed_at']): ?>
                                        <a href="<?php echo $session['meeting_link']; ?>" 
                                           target="_blank"
                                           class="px-4 py-2 bg-idafu-primary text-white rounded hover:bg-idafu-primaryDarker transition-colors duration-200">
                                            Join Meeting
                                        </a>
                                        <button onclick="openCompletionModal(<?php echo $session['booking_id']; ?>, <?php echo $session['hourly_rate']; ?>)"
                                                class="px-4 py-2 bg-idafu-primary text-idafu-accent rounded hover:bg-idafu-primaryDarker transition-colors duration-200">
                                            Complete
                                        </button>
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

    <!-- Session Completion Modal -->
    <div id="completionModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Complete Session</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Are you sure you want to mark this session as complete?
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <input type="hidden" id="currentBookingId">
                    <input type="hidden" id="currentHourlyRate">
                    <button id="confirmComplete" 
                        class="px-4 py-2 bg-idafu-primary text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-idafu-primaryDarker focus:outline-none focus:ring-2 focus:ring-idafu-primary mb-2"
                        onclick="completeSession()">
                        Complete Session
                    </button>
                    <button id="cancelComplete"
                        class="px-4 py-2 bg-gray-100 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
