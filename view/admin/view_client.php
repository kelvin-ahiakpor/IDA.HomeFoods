<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Admin');

// Get client ID from URL
$client_id = $_GET['id'] ?? null;
if (!$client_id) {
    header('Location: manage_clients.php');
    exit;
}

// Fetch client details
function fetchClientDetails($clientId) {
    global $conn;
    
    // Basic client info
    $query = "SELECT 
                u.*,
                COUNT(DISTINCT b.booking_id) as total_bookings,
                COUNT(DISTINCT CASE WHEN b.completed_at IS NOT NULL AND b.is_cancelled = 0 THEN b.booking_id END) as completed_sessions,
                COUNT(DISTINCT CASE WHEN b.booking_date >= CURDATE() AND b.is_cancelled = 0 AND b.completed_at IS NULL THEN b.booking_id END) as upcoming_sessions,
                COUNT(DISTINCT CASE WHEN b.is_cancelled = 1 THEN b.booking_id END) as cancelled_sessions,
                COALESCE(MAX(b.created_at), u.created_at) as last_active
              FROM ida_users u
              LEFT JOIN ida_bookings b ON u.user_id = b.client_id
              WHERE u.user_id = ? AND u.role = 'Client'
              GROUP BY u.user_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $clientId);
    $stmt->execute();
    $client = $stmt->get_result()->fetch_assoc();

    if (!$client) {
        return null;
    }

    // Fetch favorite consultants (most booked)
    $query = "SELECT 
                CONCAT(u.first_name, ' ', u.last_name) as name,
                c.expertise,
                COUNT(b.booking_id) as sessions
              FROM ida_bookings b
              JOIN ida_users u ON b.consultant_id = u.user_id
              JOIN ida_consultants c ON b.consultant_id = c.consultant_id
              WHERE b.client_id = ?
              GROUP BY b.consultant_id
              ORDER BY sessions DESC
              LIMIT 3";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $clientId);
    $stmt->execute();
    $client['favorite_consultants'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Fetch recent bookings
    $query = "SELECT 
                b.booking_date as date,
                b.time_slot as time,
                CONCAT(u.first_name, ' ', u.last_name) as consultant,
                CASE 
                    WHEN b.is_cancelled = 1 THEN 'Cancelled'
                    WHEN b.completed_at IS NOT NULL THEN 'Completed'
                    WHEN b.booking_date >= CURDATE() THEN 'Upcoming'
                    ELSE 'Past'
                END as status
              FROM ida_bookings b
              JOIN ida_users u ON b.consultant_id = u.user_id
              WHERE b.client_id = ?
              ORDER BY b.booking_date DESC, b.time_slot DESC
              LIMIT 5";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $clientId);
    $stmt->execute();
    $client['recent_bookings'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    return $client;
}

$client = fetchClientDetails($client_id);
if (!$client) {
    header('Location: manage_clients.php');
    exit;
}

// Helper function to get initials
function getInitials($firstName, $lastName) {
    return strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
}

// Add this function after the getInitials function
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
    <title>View Client | idafü</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>

<body class="bg-gray-50 font-sans antialiased">
    <div class="flex flex-col min-h-screen">
        <!-- Main Content -->
        <main class="flex-grow pt-16 px-4 sm:px-6">
            <div class="max-w-7xl mx-auto py-2 sm:py-4">
                <!-- Back Button -->
                <a href="./manage_clients.php" class="inline-flex items-center text-idafu-primary hover:underline mb-3">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Clients
                </a>

                <!-- Client Profile -->
                <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                    <!-- Profile Header -->
                    <div class="flex flex-col lg:flex-row gap-6 mb-6">
                        <!-- Left Column: Image and Basic Info -->
                        <div class="flex flex-col sm:flex-row lg:flex-col items-center sm:items-start text-center sm:text-left gap-4">
                            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-idafu-primary text-white text-3xl flex items-center justify-center">
                                <?php echo getInitials($client['first_name'], $client['last_name']); ?>
                            </div>
                            
                            <div class="flex flex-col items-center sm:items-start">
                                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-1">
                                    <?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?>
                                </h1>
                                <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($client['email']); ?></p>
                                <span class="px-3 py-1 text-sm rounded-full <?php echo $client['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo $client['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Right Column: Stats Grid -->
                        <div class="flex-grow">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                                    <p class="text-xs sm:text-sm text-gray-500">Joined Date</p>
                                    <p class="text-sm sm:text-base font-semibold">
                                        <?php echo date('M d, Y', strtotime($client['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                                    <p class="text-xs sm:text-sm text-gray-500">Last Active</p>
                                    <p class="text-sm sm:text-base font-semibold">
                                        <?php echo date('M d, Y', strtotime($client['last_active'])); ?>
                                    </p>
                                </div>
                                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                                    <p class="text-xs sm:text-sm text-gray-500">Total Bookings</p>
                                    <p class="text-sm sm:text-base font-semibold"><?php echo $client['total_bookings']; ?></p>
                                </div>
                                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                                    <p class="text-xs sm:text-sm text-gray-500">Completed Sessions</p>
                                    <p class="text-sm sm:text-base font-semibold"><?php echo $client['completed_sessions']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-2 justify-start mb-6">
                        <button onclick='openEditModal(<?php echo json_encode($client); ?>)' 
                                class="text-idafu-primary hover:bg-idafu-lightBlue px-4 py-2 rounded transition-colors duration-200">
                            Edit Profile
                        </button>
                        <?php if ($client['is_active']): ?>
                            <button onclick="openDeactivateModal(<?php echo $client['user_id']; ?>)" 
                                    class="text-idafu-accentDeeper hover:bg-red-50 px-4 py-2 rounded transition-colors duration-200">
                                Deactivate Account
                            </button>
                        <?php else: ?>
                            <button onclick="toggleClientStatus(<?php echo $client['user_id']; ?>, true)"
                                    class="text-green-600 hover:bg-green-50 px-4 py-2 rounded transition-colors duration-200">
                                Activate Account
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Booking History -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Bookings</h2>
                        <div class="space-y-4">
                            <?php foreach ($client['recent_bookings'] as $booking): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium text-gray-800"><?php echo $booking['consultant']; ?></p>
                                            <p class="text-sm text-gray-600">
                                                <?php echo date('M d, Y', strtotime($booking['date'])); ?> at <?php echo $booking['time']; ?>
                                            </p>
                                        </div>
                                        <span class="px-2 py-1 text-sm rounded-full 
                                            <?php echo $booking['status'] === 'Completed' ? 'bg-green-100 text-green-800' : 
                                                    ($booking['status'] === 'Upcoming' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'); ?>">
                                            <?php echo $booking['status']; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Favorite Consultants -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Favorite Consultants</h2>
                        <div class="space-y-4">
                            <?php foreach ($client['favorite_consultants'] as $consultant): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="font-medium text-gray-800"><?php echo $consultant['name']; ?></p>
                                    <p class="text-sm text-gray-600"><?php echo formatExpertise($consultant['expertise']); ?></p>
                                    <p class="text-sm text-gray-500 mt-1"><?php echo $consultant['sessions']; ?> sessions</p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?php include('../../assets/includes/footer-dashboard.php'); ?>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Edit Client Profile</h3>
                    <button class="text-gray-600 hover:text-gray-800" onclick="closeEditModal()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form id="editClientForm" class="space-y-4">
                    <input type="hidden" id="editClientId">
                    <div>
                        <label for="editFirstName" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" id="editFirstName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0">
                    </div>
                    <div>
                        <label for="editLastName" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" id="editLastName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0">
                    </div>
                    <div>
                        <label for="editEmail" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="editEmail" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0">
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeEditModal()" 
                                class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-idafu-primary text-white rounded hover:bg-idafu-primaryDarker transition-colors duration-200">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Deactivate Confirmation Modal -->
    <div id="deactivateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Confirm Deactivation</h3>
                    <button class="text-gray-600 hover:text-gray-800" onclick="closeDeactivateModal()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <p class="text-gray-600 mb-4">Are you sure you want to deactivate this client? They will no longer be able to access the platform.</p>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="closeDeactivateModal()" 
                            class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded transition-colors duration-200">
                        Cancel
                    </button>
                    <button onclick="confirmDeactivate()" 
                            class="px-4 py-2 bg-idafu-accentDeeper text-white rounded hover:bg-red-600 transition-colors duration-200">
                        Deactivate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/script-dashboard.js" defer></script>
    <script src="../../assets/js/script-view-client.js" defer></script>
</body>

</html>


