<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Client');

// Get the current user's ID from the session
$client_id = $_SESSION['user_id'];

// Get the active tab from URL parameter, default to 'all'
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'all';

// Build WHERE clause based on active tab
$whereClause = "WHERE b.client_id = ?";
if ($activeTab === 'upcoming') {
    $whereClause .= " AND b.is_cancelled = 0 AND b.completed_at IS NULL AND b.booking_date >= CURDATE()";
} elseif ($activeTab === 'completed') {
    $whereClause .= " AND b.completed_at IS NOT NULL";
} elseif ($activeTab === 'cancelled') {
    $whereClause .= " AND b.is_cancelled = 1";
}

// Fetch bookings for the current client
$query = "
    SELECT 
        b.booking_id,
        b.booking_date,
        b.time_slot,
        b.status,
        b.notes,
        b.is_cancelled,
        b.completed_at,
        u.first_name,
        u.last_name,
        c.expertise,
        c.hourly_rate as price,
        COALESCE(
            (SELECT duration 
             FROM ida_consultant_sessions cs 
             WHERE cs.consultant_id = b.consultant_id 
             AND cs.client_id = b.client_id
             LIMIT 1), 
            60
        ) as duration
    FROM ida_bookings b
    JOIN ida_users u ON u.user_id = b.consultant_id
    JOIN ida_consultants c ON c.consultant_id = b.consultant_id
    " . $whereClause . "
    ORDER BY b.booking_date DESC, b.time_slot DESC
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    error_log("Query preparation failed: " . $conn->error);
    die("Failed to prepare query");
}
$stmt->bind_param("i", $client_id);
if (!$stmt->execute()) {
    error_log("Query execution failed: " . $stmt->error);
    die("Failed to execute query");
}
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    // Format the booking data
    $booking = [
        'id' => $row['booking_id'],
        'consultant' => $row['first_name'] . ' ' . $row['last_name'],
        'expertise' => formatExpertise($row['expertise']),
        'date' => $row['booking_date'],
        'time' => $row['time_slot'],
        'duration' => $row['duration'],
        'status' => $row['is_cancelled'] ? 'Cancelled' : 
                   ($row['completed_at'] ? 'Completed' : 'Upcoming'),
        'is_cancelled' => $row['is_cancelled'],
        'price' => $row['price'],
        'notes' => $row['notes'],
        'meeting_link' => 'https://meet.google.com/auw-sofx-tho' 
    ];
    
    $bookings[] = $booking;
}

$stmt->close();

// Add the formatExpertise function from dashboard.php
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
    <title>My Bookings | idafü</title>
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
                <!-- Header Section -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4 sm:mb-0">My Bookings</h1>
                    <a href="explore_consultants.php" 
                       class="inline-flex items-center px-4 py-2 bg-idafu-primary text-white rounded-lg hover:bg-idafu-primaryDarker transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Book New Consultation
                    </a>
                </div>

                <!-- Tabs -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="-mb-px flex space-x-4 overflow-x-auto">
                        <button data-tab="all" 
                                class="tab-btn whitespace-nowrap px-3 py-2 border-b-2 <?php echo $activeTab === 'all' ? 'border-idafu-primary text-idafu-primary' : 'border-transparent text-gray-500'; ?> hover:text-gray-700 hover:border-gray-300 text-sm">
                            All Bookings
                        </button>
                        <button data-tab="upcoming" 
                                class="tab-btn whitespace-nowrap px-3 py-2 border-b-2 <?php echo $activeTab === 'upcoming' ? 'border-idafu-primary text-idafu-primary' : 'border-transparent text-gray-500'; ?> hover:text-gray-700 hover:border-gray-300 text-sm">
                            Upcoming
                        </button>
                        <button data-tab="completed" 
                                class="tab-btn whitespace-nowrap px-3 py-2 border-b-2 <?php echo $activeTab === 'completed' ? 'border-idafu-primary text-idafu-primary' : 'border-transparent text-gray-500'; ?> hover:text-gray-700 hover:border-gray-300 text-sm">
                            Completed
                        </button>
                        <button data-tab="cancelled" 
                                class="tab-btn whitespace-nowrap px-3 py-2 border-b-2 <?php echo $activeTab === 'cancelled' ? 'border-idafu-primary text-idafu-primary' : 'border-transparent text-gray-500'; ?> hover:text-gray-700 hover:border-gray-300 text-sm">
                            Cancelled
                        </button>
                    </nav>
                </div>

                <!-- Bookings List -->
                <div class="space-y-4">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-card bg-white rounded-lg shadow-sm overflow-hidden" 
                             data-status="<?php 
                                 if ($booking['status'] === 'Completed') echo 'completed';
                                 else if ($booking['is_cancelled']) echo 'cancelled';
                                 else echo 'upcoming';
                             ?>">
                            <div class="p-4 sm:p-6">
                                <!-- Header with Consultant Info and Status -->
                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($booking['consultant']); ?></h3>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($booking['expertise']); ?></p>
                                    </div>
                                    <div class="mt-2 sm:mt-0">
                                        <span class="px-3 py-1 rounded-full text-sm 
                                            <?php echo $booking['status'] === 'Upcoming' ? 'bg-green-100 text-green-800' : 
                                                    ($booking['status'] === 'Completed' ? 'bg-idafu-lightBlue text-idafu-primary' : 'bg-idafu-accent text-idafu-accentMutedGold'); ?>">
                                            <?php echo $booking['status']; ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Booking Details Grid -->
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Date & Time</p>
                                        <p class="font-medium">
                                            <?php echo date('F j, Y', strtotime($booking['date'])); ?><br>
                                            <?php echo date('g:i A', strtotime($booking['time'])); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Duration</p>
                                        <p class="font-medium"><?php echo $booking['duration'] ? $booking['duration'] . ' minutes' : 'N/A'; ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Price</p>
                                        <p class="font-medium"><?php echo $booking['price'] ? '$' . number_format($booking['price'], 2) : 'N/A'; ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Booking ID</p>
                                        <p class="font-medium">#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
                                    </div>
                                </div>

                                <!-- Notes Section -->
                                <?php if ($booking['notes']): ?>
                                    <div class="mb-4">
                                        <p class="text-sm text-gray-600">Notes</p>
                                        <p class="text-gray-700"><?php echo htmlspecialchars($booking['notes']); ?></p>
                                    </div>
                                <?php endif; ?>

                                <!-- Action Buttons -->
                                <div class="flex flex-wrap gap-2">
                                    <?php if ($booking['status'] === 'Upcoming'): ?>
                                        <?php if ($booking['meeting_link']): ?>
                                            <a href="<?php echo $booking['meeting_link']; ?>" 
                                               class="flex-1 sm:flex-none text-center inline-flex items-center justify-center px-3 py-1.5 bg-idafu-primary text-white rounded hover:bg-idafu-primaryDarker transition-colors duration-200 text-sm">
                                                <svg class="w-4 h-4 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                <span class="hidden sm:inline">Join Meeting</span>
                                                <span class="sm:hidden">Join</span>
                                            </a>
                                        <?php endif; ?>
                                        <button class="flex-1 sm:flex-none px-3 py-1.5 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 transition-colors duration-200 text-sm">
                                            Reschedule
                                        </button>
                                        <button 
                                            class="cancel-booking-btn flex-1 sm:flex-none px-3 py-1.5 border border-red-300 text-red-700 rounded hover:bg-red-50 transition-colors duration-200 text-sm"
                                            data-booking-id="<?php echo $booking['id']; ?>">
                                            Cancel
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?php include('../../assets/includes/footer-dashboard.php'); ?>

        <!-- Cancellation Modal -->
        <div id="cancellationModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Cancel Booking</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to cancel this booking? This action cannot be undone.
                        </p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button id="confirmCancellation"
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Yes, Cancel Booking
                        </button>
                        <button id="cancelCancellation"
                            class="mt-3 px-4 py-2 bg-white text-gray-700 text-base font-medium rounded-md border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            No, Keep Booking
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/script-dashboard.js" defer></script>
    <script src="../../assets/js/script-bookings.js" defer></script>
</body>

</html>