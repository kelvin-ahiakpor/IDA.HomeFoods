<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Client');

// Fetch client metrics
function fetchClientMetrics($userId) {
    global $conn;
    
    $query = "SELECT 
                COUNT(*) as total_sessions,
                COUNT(CASE WHEN b.booking_date >= CURDATE() THEN 1 END) as upcoming_sessions,
                COUNT(CASE WHEN b.status = 'Approved' AND b.completed_at IS NOT NULL THEN 1 END) as completed_sessions,
                SUM(CASE WHEN b.status = 'Approved' AND b.completed_at IS NOT NULL THEN c.hourly_rate ELSE 0 END) as total_spent
              FROM ida_bookings b
              JOIN ida_consultants c ON b.consultant_id = c.consultant_id
              WHERE b.client_id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Fetch upcoming consultations
function fetchUpcomingConsultations($userId) {
    global $conn;
    
    $query = "SELECT b.*, 
                     u.first_name, u.last_name,
                     c.expertise, c.hourly_rate
              FROM ida_bookings b
              JOIN ida_users u ON b.consultant_id = u.user_id
              JOIN ida_consultants c ON b.consultant_id = c.consultant_id
              WHERE b.client_id = ? 
              AND b.booking_date >= CURDATE()
              AND b.status = 'Approved'
              AND b.completed_at IS NULL
              AND b.is_cancelled = 0
              ORDER BY b.booking_date ASC, b.time_slot ASC
              LIMIT 5";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Fetch recent consultations
function fetchRecentConsultations($userId) {
    global $conn;
    
    $query = "SELECT b.*, 
                     u.first_name, u.last_name,
                     c.expertise,
                     sr.rating
              FROM ida_bookings b
              JOIN ida_users u ON b.consultant_id = u.user_id
              JOIN ida_consultants c ON b.consultant_id = c.consultant_id
              LEFT JOIN ida_session_ratings sr ON b.booking_id = sr.booking_id
              WHERE b.client_id = ? 
              AND b.completed_at IS NOT NULL
              ORDER BY b.completed_at DESC
              LIMIT 5";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$metrics = fetchClientMetrics($_SESSION['user_id']);
$upcomingConsultations = fetchUpcomingConsultations($_SESSION['user_id']);
$recentConsultations = fetchRecentConsultations($_SESSION['user_id']);

function formatExpertise($expertise) {
    if (empty($expertise)) return 'General Consultant';
    
    $areas = json_decode($expertise, true);
    if (!is_array($areas)) return 'General Consultant';
    
    $areas = array_map(function($area) {
        return ucfirst(str_replace('_', ' ', $area));
    }, $areas);
    
    if (count($areas) === 1) {
        return $areas[0];
    } else {
        $firstTwo = array_slice($areas, 0, 2);
        return implode(' & ', $firstTwo);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('../../assets/includes/head-dashboard.php'); ?>
    <title>Dashboard | idafü</title>
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
                <!-- Welcome Section -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-800">
                        Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>
                    </h1>
                    <button onclick="window.location.href='./explore_consultants.php'" 
                            class="px-4 py-2 bg-idafu-primary text-white rounded-lg hover:bg-idafu-primaryDarker transition-colors duration-200">
                        Book Consultation
                    </button>
                </div>

                <!-- Metrics Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="text-gray-500 text-sm">Total Sessions</h3>
                        <p class="text-2xl font-semibold mt-1"><?php echo $metrics['total_sessions'] ?? 0; ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="text-gray-500 text-sm">Upcoming Sessions</h3>
                        <p class="text-2xl font-semibold mt-1"><?php echo $metrics['upcoming_sessions'] ?? 0; ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="text-gray-500 text-sm">Completed Sessions</h3>
                        <p class="text-2xl font-semibold mt-1"><?php echo $metrics['completed_sessions'] ?? 0; ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="text-gray-500 text-sm">Total Spent</h3>
                        <p class="text-2xl font-semibold mt-1">$<?php echo number_format($metrics['total_spent'] ?? 0, 2); ?></p>
                    </div>
                </div>

                <!-- Upcoming Consultations -->
                <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Upcoming Consultations</h2>
                    <div class="space-y-4">
                        <?php if (empty($upcomingConsultations)): ?>
                            <p class="text-gray-600">No upcoming consultations scheduled.</p>
                        <?php else: ?>
                            <?php foreach ($upcomingConsultations as $consultation): ?>
                                <div class="flex justify-between items-center bg-gray-50 p-4 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-800">
                                            <?php echo htmlspecialchars($consultation['first_name'] . ' ' . $consultation['last_name']); ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <?php echo htmlspecialchars(formatExpertise($consultation['expertise'])); ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <?php echo date('F j, Y', strtotime($consultation['booking_date'])); ?> at 
                                            <?php echo date('g:i A', strtotime($consultation['time_slot'])); ?>
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="https://meet.google.com/auw-sofx-tho" target="_blank"
                                           class="px-4 py-2 bg-idafu-primary text-white rounded hover:bg-idafu-primaryDarker transition-colors duration-200">
                                            Join Meeting
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Consultations -->
                <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Consultations</h2>
                    <div class="space-y-4">
                        <?php if (empty($recentConsultations)): ?>
                            <p class="text-gray-600">No past consultations yet.</p>
                        <?php else: ?>
                            <?php foreach ($recentConsultations as $consultation): ?>
                                <div class="flex justify-between items-center bg-gray-50 p-4 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-800">
                                            <?php echo htmlspecialchars($consultation['first_name'] . ' ' . $consultation['last_name']); ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <?php echo htmlspecialchars(formatExpertise($consultation['expertise'])); ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <?php echo date('F j, Y', strtotime($consultation['booking_date'])); ?> at 
                                            <?php echo date('g:i A', strtotime($consultation['time_slot'])); ?>
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <?php if ($consultation['rating']): ?>
                                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm flex items-center">
                                                <span class="text-yellow-400 mr-1">★</span>
                                                <?php echo $consultation['rating']; ?>/5
                                            </span>
                                        <?php else: ?>
                                            <button onclick="openRatingModal(<?php echo $consultation['booking_id']; ?>)"
                                                    class="px-3 py-1 text-gray-600 hover:text-gray-900 transition-colors duration-200">
                                                Rate Session
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?php include('../../assets/includes/footer-dashboard.php'); ?>
    </div>

    <!-- Add Rating Modal -->
    <div id="ratingModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 text-center">Rate Your Session</h3>
                <div class="mt-2 px-7 py-3">
                    <div class="flex justify-center space-x-2 mb-4">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <button class="rating-star text-3xl text-gray-300 hover:text-yellow-400 transition-colors duration-200" data-value="<?php echo $i; ?>">★</button>
                        <?php endfor; ?>
                    </div>
                    <textarea id="feedbackText" rows="3" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-idafu-primary sm:text-sm"
                        placeholder="Share your experience (optional)"></textarea>
                </div>
                <div class="items-center px-4 py-3">
                    <input type="hidden" id="currentBookingId">
                    <input type="hidden" id="selectedRating">
                    <button id="submitRating" 
                        class="px-4 py-2 bg-idafu-primary text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-idafu-primaryDarker focus:outline-none focus:ring-2 focus:ring-idafu-primary mb-2">
                        Submit Rating
                    </button>
                    <button id="cancelRating"
                        class="px-4 py-2 bg-gray-100 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/script-dashboard.js" defer></script>
</body>
</html>