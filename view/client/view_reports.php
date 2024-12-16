<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Client');

// Get client ID from session
$client_id = $_SESSION['user_id'];

// Fetch metrics
$metrics = [];

// Get total consultations
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total_consultations,
    SUM(CASE WHEN booking_date >= CURRENT_DATE AND status = 'Approved' THEN 1 ELSE 0 END) as upcoming_sessions,
    SUM(CASE WHEN status = 'Approved' AND completed_at IS NOT NULL THEN 1 ELSE 0 END) as completed_sessions,
    SUM(CASE WHEN is_cancelled = 1 THEN 1 ELSE 0 END) as cancelled_sessions
    FROM ida_bookings 
    WHERE client_id = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$metrics = $result->fetch_assoc();

// Get average rating given by client
$stmt = $conn->prepare("SELECT AVG(rating) as avg_rating 
    FROM ida_session_ratings r 
    JOIN ida_consultant_sessions s ON r.rating_id = s.session_id 
    WHERE s.client_id = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$rating_result = $stmt->get_result();
$rating_data = $rating_result->fetch_assoc();
$metrics['avg_rating_given'] = number_format($rating_data['avg_rating'] ?? 0, 1);

// Get total spent
$stmt = $conn->prepare("SELECT 
    SUM(c.hourly_rate) as total_spent 
    FROM ida_bookings b
    JOIN ida_consultants c ON b.consultant_id = c.consultant_id
    WHERE b.client_id = ? 
    AND b.status = 'Approved' 
    AND b.completed_at IS NOT NULL");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$spent_result = $stmt->get_result();
$spent_data = $spent_result->fetch_assoc();
$metrics['total_spent'] = number_format($spent_data['total_spent'] ?? 0, 2);

// Add this function at the top with the other PHP code
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

// Fetch consultation history
$stmt = $conn->prepare("SELECT 
    b.booking_date as date,
    CONCAT(u.first_name, ' ', u.last_name) as consultant,
    c.expertise,
    c.hourly_rate as cost,
    b.status,
    cs.duration,
    r.rating as rating_given,
    r.feedback as feedback
    FROM ida_bookings b
    JOIN ida_users u ON b.consultant_id = u.user_id
    JOIN ida_consultants c ON b.consultant_id = c.consultant_id
    LEFT JOIN ida_consultant_sessions cs ON b.booking_id = cs.session_id
    LEFT JOIN ida_session_ratings r ON cs.session_id = r.rating_id
    WHERE b.client_id = ?
    ORDER BY b.booking_date DESC
    LIMIT 10");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$consultationHistory = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('../../assets/includes/head-dashboard.php'); ?>
    <title>My Reports | idafü</title>
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
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6">My Reports</h1>

                <!-- Overview Cards -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="text-gray-500 text-sm">Total Sessions</h3>
                        <p class="text-2xl font-semibold mt-1"><?php echo $metrics['total_consultations']; ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="text-gray-500 text-sm">Upcoming</h3>
                        <p class="text-2xl font-semibold mt-1"><?php echo $metrics['upcoming_sessions']; ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="text-gray-500 text-sm">Total Spent</h3>
                        <p class="text-2xl font-semibold mt-1">$<?php echo $metrics['total_spent']; ?></p>
                    </div>
                </div>

                <!-- Consultation History -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Consultation History</h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Consultant</th>
                                    <th class="px-4 py-3">Service</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Rating</th>
                                    <th class="px-4 py-3">Cost</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($consultationHistory as $session): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <?php echo date('M j, Y', strtotime($session['date'])); ?>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        <?php echo htmlspecialchars($session['consultant']); ?>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        <?php echo formatExpertise($session['expertise']); ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                            <?php echo $session['status'] === 'Approved' ? 
                                                'bg-green-100 text-green-800' : 
                                                'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo $session['status']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if ($session['rating_given']): ?>
                                            <div class="flex items-center">
                                                <span class="text-yellow-400 mr-1">★</span>
                                                <span><?php echo $session['rating_given']; ?>/5</span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400">Not rated</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        $<?php echo $session['cost']; ?>
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



