<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Client');

// Get consultant ID from URL
$consultantId = $_GET['consultant_id'] ?? null;

if (!$consultantId) {
    header('Location: explore_consultants.php');
    exit;
}

// Fetch consultant details
function fetchConsultantDetails($consultantId) {
    global $conn;
    $query = "SELECT u.first_name, u.last_name, u.profile_picture,
                     c.expertise, c.rating, c.hourly_rate, c.bio
              FROM ida_users u
              JOIN ida_consultants c ON u.user_id = c.consultant_id
              WHERE u.user_id = ? AND c.status = 'Active'";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $consultantId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Fetch available time slots
function fetchAvailableSlots($consultantId) {
    global $conn;
    $query = "SELECT a.availability_id, a.date, a.start_time, a.end_time
              FROM ida_availability a
              LEFT JOIN ida_bookings b ON (
                  b.consultant_id = a.consultant_id 
                  AND b.booking_date = a.date 
                  AND b.time_slot = a.start_time
                  AND b.status = 'Approved'
              )
              WHERE a.consultant_id = ?
              AND a.date >= CURDATE()
              AND b.booking_id IS NULL
              ORDER BY a.date ASC, a.start_time ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $consultantId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$consultant = fetchConsultantDetails($consultantId);
$availableSlots = fetchAvailableSlots($consultantId);

// If consultant not found or not active
if (!$consultant) {
    header('Location: explore_consultants.php');
    exit;
}

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

function getInitials($firstName, $lastName) {
    return strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('../../assets/includes/head-dashboard.php'); ?>
    <title>Book Consultation | idafü</title>
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
                <div class="mb-6">
                    <a href="explore_consultants.php" class="text-idafu-primary hover:text-idafu-primaryDarker">
                        ← Back to Consultants
                    </a>
                </div>

                <!-- Consultant Details -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 md:space-x-8">
                        <?php if ($consultant['profile_picture']): ?>
                            <img src="<?php echo htmlspecialchars($consultant['profile_picture']); ?>" 
                                 alt="Profile" 
                                 class="w-48 h-48 rounded-lg object-cover shadow-md">
                        <?php else: ?>
                            <div class="w-48 h-48 bg-idafu-primary rounded-lg flex items-center justify-center text-white text-4xl font-semibold shadow-md">
                                <?php echo getInitials($consultant['first_name'], $consultant['last_name']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="flex-1 text-center md:text-left">
                            <h1 class="text-2xl font-semibold text-gray-800">
                                <?php echo htmlspecialchars($consultant['first_name'] . ' ' . $consultant['last_name']); ?>
                            </h1>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars(formatExpertise($consultant['expertise'])); ?>
                            </p>
                            <div class="mt-2 flex items-center">
                                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="ml-1 text-sm font-medium text-gray-600">
                                    <?php echo number_format($consultant['rating'] ?? 0, 1); ?>
                                </span>
                                <span class="mx-2 text-gray-300">|</span>
                                <span class="text-gray-600">
                                    $<?php echo number_format($consultant['hourly_rate'] ?? 0, 2); ?>/hour
                                </span>
                            </div>
                            <?php if ($consultant['bio']): ?>
                                <p class="mt-4 text-gray-600">
                                    <?php echo htmlspecialchars($consultant['bio']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Available Time Slots -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Available Time Slots</h2>
                    <?php if (empty($availableSlots)): ?>
                        <p class="text-gray-600">No available time slots at the moment.</p>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($availableSlots as $slot): ?>
                                <button onclick="bookSlot(<?php echo $slot['availability_id']; ?>, '<?php echo $slot['date']; ?>', '<?php echo $slot['start_time']; ?>', '<?php echo $slot['end_time']; ?>')"
                                        class="p-4 border rounded-lg hover:border-idafu-primary focus:outline-none focus:border-idafu-primary transition-colors duration-200">
                                    <div class="font-medium text-gray-800">
                                        <?php echo date('l, F j, Y', strtotime($slot['date'])); ?>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <?php echo date('g:i A', strtotime($slot['start_time'])); ?> - 
                                        <?php echo date('g:i A', strtotime($slot['end_time'])); ?>
                                    </div>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?php include('../../assets/includes/footer-dashboard.php'); ?>
    </div>

    <!-- Booking Confirmation Modal -->
    <div id="bookingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Confirm Booking</h3>
                    <button onclick="closeBookingModal()" class="text-gray-600 hover:text-gray-800">×</button>
                </div>
                <div id="bookingDetails" class="mb-4">
                    <!-- Will be populated by JavaScript -->
                </div>
                <div class="mb-4">
                    <label for="meetingNotes" class="block text-sm font-medium text-gray-700 mb-1">Meeting Notes (Optional)</label>
                    <textarea id="meetingNotes" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-idafu-primary"
                            rows="3"
                            placeholder="Add any notes or topics you'd like to discuss..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button onclick="closeBookingModal()" class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded">Cancel</button>
                    <button onclick="confirmBooking()" class="px-4 py-2 bg-idafu-primary text-white rounded hover:bg-idafu-primaryDarker">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/script-dashboard.js" defer></script>
    <script src="../../assets/js/script-booking.js" defer></script>
</body>
</html> 
