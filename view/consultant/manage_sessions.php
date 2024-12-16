<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Consultant');

// Fetch sessions for the logged-in consultant
function fetchSessions($userId, $type = 'upcoming') {
    global $conn;
    
    $today = date('Y-m-d');
    $query = "SELECT b.*, 
                     u.first_name, u.last_name,
                     TIME_FORMAT(b.time_slot, '%H:%i') as formatted_time,
                     b.completed_at,
                     b.is_cancelled
              FROM ida_bookings b
              JOIN ida_users u ON b.client_id = u.user_id
              WHERE b.consultant_id = ? 
              AND b.status = 'Approved'";
    
    if ($type === 'upcoming') {
        $query .= " AND completed_at IS NULL 
                   AND is_cancelled = 0
                   AND (b.booking_date > ? OR (b.booking_date = ? AND b.time_slot >= CURTIME()))";
        $orderBy = "ASC";
    } else {
        $query .= " AND (completed_at IS NOT NULL 
                   OR is_cancelled = 1
                   OR b.booking_date < ? 
                   OR (b.booking_date = ? AND b.time_slot < CURTIME()))";
        $orderBy = "DESC";
    }
    
    $query .= " ORDER BY b.booking_date $orderBy, b.time_slot $orderBy";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iss', $userId, $today, $today);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$upcomingSessions = fetchSessions($_SESSION['user_id'], 'upcoming');
$pastSessions = fetchSessions($_SESSION['user_id'], 'past');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('../../assets/includes/head-dashboard.php'); ?>
    <title>My Sessions | idafü</title>
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
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6">My Sessions</h1>

                <!-- Upcoming Sessions -->
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Upcoming Sessions</h2>
                <?php if (empty($upcomingSessions)): ?>
                    <p class="text-gray-600">No upcoming sessions.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($upcomingSessions as $session): ?>
                            <div class="bg-white rounded-lg shadow-sm p-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="font-medium text-gray-800">
                                            <?php echo htmlspecialchars($session['first_name'] . ' ' . $session['last_name']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            <?php echo date('F j, Y', strtotime($session['booking_date'])); ?> at 
                                            <?php echo date('g:i A', strtotime($session['time_slot'])); ?>
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="viewSessionDetails(<?php echo $session['booking_id']; ?>, '<?php echo htmlspecialchars($session['notes'] ?? ''); ?>')"
                                                class="hidden sm:block px-4 py-2 text-gray-600 hover:bg-gray-50 rounded transition-colors duration-200">
                                            View Details
                                        </button>
                                        <button onclick="viewSessionDetails(<?php echo $session['booking_id']; ?>, '<?php echo htmlspecialchars($session['notes'] ?? ''); ?>')"
                                                class="sm:hidden px-3 py-1.5 text-gray-600 hover:bg-gray-50 rounded transition-colors duration-200">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>

                                        <a href="https://meet.google.com/auw-sofx-tho" target="_blank"
                                           class="hidden sm:inline-block px-4 py-2 bg-idafu-primary text-white rounded hover:bg-idafu-primaryDarker transition-colors duration-200">
                                            Join Meeting
                                        </a>
                                        <a href="https://meet.google.com/auw-sofx-tho" target="_blank"
                                           class="sm:hidden inline-flex items-center px-3 py-1.5 bg-idafu-primary text-white rounded hover:bg-idafu-primaryDarker transition-colors duration-200">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </a>

                                        <?php if (date('Y-m-d') === $session['booking_date']): ?>
                                            <button onclick="openCompletionModal(<?php echo $session['booking_id']; ?>, <?php echo $session['hourly_rate']; ?>)"
                                                    class="hidden sm:block px-4 py-2 bg-idafu-primary text-idafu-accent rounded hover:bg-idafu-primaryDarker transition-colors duration-200">
                                                Complete
                                            </button>
                                            <button onclick="openCompletionModal(<?php echo $session['booking_id']; ?>, <?php echo $session['hourly_rate']; ?>)"
                                                    class="sm:hidden px-3 py-1.5 bg-idafu-primary text-idafu-accent rounded hover:bg-idafu-primaryDarker transition-colors duration-200">
                                                ✓
                                            </button>
                                        <?php endif; ?>

                                        <button onclick="openCancellationModal(<?php echo $session['booking_id']; ?>)"
                                                class="hidden sm:block px-4 py-2 text-red-600 hover:bg-red-50 rounded transition-colors duration-200">
                                            Cancel
                                        </button>
                                        <button onclick="openCancellationModal(<?php echo $session['booking_id']; ?>)"
                                                class="sm:hidden px-3 py-1.5 text-red-600 hover:bg-red-50 rounded transition-colors duration-200">
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Past Sessions -->
                <h2 class="text-lg font-semibold text-gray-800 mb-4 mt-6">Past Sessions</h2>
                <?php if (empty($pastSessions)): ?>
                    <p class="text-gray-600">No past sessions.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($pastSessions as $session): ?>
                            <div class="bg-white rounded-lg shadow-sm p-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="font-medium text-gray-800">
                                            <?php echo htmlspecialchars($session['first_name'] . ' ' . $session['last_name']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            <?php echo date('F j, Y', strtotime($session['booking_date'])); ?> at 
                                            <?php echo date('g:i A', strtotime($session['time_slot'])); ?>
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="px-3 py-1 <?php 
                                            if ($session['is_cancelled']) {
                                                echo 'bg-red-100 text-red-800';
                                            } elseif ($session['completed_at']) {
                                                echo 'bg-blue-100 text-blue-800';
                                            } else {
                                                echo 'bg-gray-100 text-gray-800';
                                            }
                                        ?> rounded-full text-sm">
                                            <?php 
                                                if ($session['is_cancelled']) {
                                                    echo 'Cancelled';
                                                } elseif ($session['completed_at']) {
                                                    echo 'Completed';
                                                } else {
                                                    echo 'Past';
                                                }
                                            ?>
                                        </span>
                                        <button onclick="viewSessionDetails(<?php echo $session['booking_id']; ?>, '<?php echo htmlspecialchars($session['notes'] ?? ''); ?>')"
                                                class="px-4 py-2 text-idafu-primary hover:bg-idafu-lightBlue rounded transition-colors duration-200">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <!-- View Session Modal -->
        <div id="viewSessionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
                <div class="flex flex-col">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Session Notes</h3>
                        <button onclick="closeViewModal()" class="text-gray-600 hover:text-gray-800">×</button>
                    </div>
                    <div id="sessionNotes" class="mb-4 text-gray-600">
                        <!-- Will be populated by JavaScript -->
                    </div>
                    <div class="flex justify-end">
                        <button onclick="closeViewModal()" 
                                class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <?php include('../../assets/includes/footer-dashboard.php'); ?>
    </div>

    <script src="../../assets/js/script-dashboard.js" defer></script>
    <script src="../../assets/js/script-manage-sessions.js" defer></script>

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
                        class="px-4 py-2 bg-idafu-primary text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-idafu-primaryDarker focus:outline-none focus:ring-2 focus:ring-idafu-primary mb-2">
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

    <!-- Session Cancellation Modal -->
    <div id="cancellationModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Cancel Session</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Are you sure you want to cancel this session? This action cannot be undone.
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <input type="hidden" id="cancelBookingId">
                    <button id="confirmCancel" 
                        class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 mb-2">
                        Cancel Session
                    </button>
                    <button id="cancelCancellation"
                        class="px-4 py-2 bg-gray-100 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Keep Session
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

