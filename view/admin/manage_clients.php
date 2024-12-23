<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Admin');

// Fetch all clients
function fetchClients() {
    global $conn;
    
    $query = "SELECT 
                u.user_id,
                u.first_name,
                u.last_name,
                u.email,
                u.is_active,
                COUNT(DISTINCT b.booking_id) as total_bookings,
                MAX(b.booking_date) as last_booking
              FROM ida_users u
              LEFT JOIN ida_bookings b ON u.user_id = b.client_id
              WHERE u.role = 'Client'
              GROUP BY u.user_id
              ORDER BY u.first_name, u.last_name";
              
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $clients = [];
    while ($row = $result->fetch_assoc()) {
        $clients[] = [
            'user_id' => $row['user_id'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => $row['email'],
            'total_bookings' => $row['total_bookings'],
            'last_booking' => $row['last_booking'],
            'is_active' => (bool)$row['is_active']
        ];
    }
    
    return $clients;
}

// Search functionality
$searchTerm = $_GET['search'] ?? '';
if ($searchTerm) {
    $clients = array_filter(fetchClients(), function($client) use ($searchTerm) {
        $searchIn = strtolower($client['first_name'] . ' ' . $client['last_name'] . ' ' . $client['email']);
        return strpos($searchIn, strtolower($searchTerm)) !== false;
    });
} else {
    $clients = fetchClients();
}

// Helper function to get initials
function getInitials($firstName, $lastName) {
    return strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('../../assets/includes/head-dashboard.php'); ?>
    <title>Manage Clients | idafü</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
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
            <div class="max-w-7xl mx-auto py-2 sm:py-4">
                <!-- Page Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start mb-6">
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-800">Manage Clients</h1>
                    <div class="w-full sm:w-64 mt-4 sm:mt-0">
                        <form action="" method="GET" class="relative">
                            <input type="text" 
                                   name="search"
                                   value="<?php echo htmlspecialchars($searchTerm); ?>"
                                   placeholder="Search clients..." 
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-idafu-primary text-sm"
                                   onkeyup="this.form.submit()">
                            <?php if ($searchTerm): ?>
                                <a href="?clear=1" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Clients Table -->
                <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm text-left text-gray-500">
                        <thead class="bg-idafu-lightBlue text-gray-700">
                            <tr>
                                <th class="px-2 sm:px-4 py-2">Name</th>
                                <th class="px-2 sm:px-4 py-2">Email</th>
                                <th class="px-2 sm:px-4 py-2">Total Bookings</th>
                                <th class="px-2 sm:px-4 py-2">Last Booking</th>
                                <th class="px-2 sm:px-4 py-2">Status</th>
                                <th class="px-2 sm:px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clients as $client): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-2 sm:px-4 py-3">
                                        <div class="flex items-center space-x-2">
                                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-idafu-primary text-white flex items-center justify-center text-xs sm:text-sm">
                                                <?php echo getInitials($client['first_name'], $client['last_name']); ?>
                                            </div>
                                            <span class="text-gray-900 truncate">
                                                <?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-2 sm:px-4 py-3"><?php echo htmlspecialchars($client['email']); ?></td>
                                    <td class="px-2 sm:px-4 py-3"><?php echo $client['total_bookings']; ?></td>
                                    <td class="px-2 sm:px-4 py-3">
                                        <?php echo $client['last_booking'] ? date('M d, Y', strtotime($client['last_booking'])) : 'No bookings'; ?>
                                    </td>
                                    <td class="px-2 sm:px-4 py-3">
                                        <span class="px-2 py-1 text-sm rounded-full <?php echo $client['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo $client['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td class="px-2 sm:px-4 py-3">
                                        <div class="flex flex-wrap gap-2 justify-end">
                                            <button class="text-idafu-primary hover:bg-idafu-lightBlue px-2 py-1 rounded transition-colors duration-200 whitespace-nowrap"
                                                    onclick="window.open('./view_client.php?id=<?php echo $client['user_id']; ?>', '_blank')">
                                                View
                                            </button>
                                            <?php if ($client['is_active']): ?>
                                                <button class="text-idafu-accentDeeper hover:bg-red-50 px-2 py-1 rounded transition-colors duration-200 whitespace-nowrap"
                                                        onclick="openDeactivateModal(<?php echo $client['user_id']; ?>)">
                                                    Deactivate
                                                </button>
                                            <?php else: ?>
                                                <button class="text-green-600 hover:bg-green-50 px-2 py-1 rounded transition-colors duration-200 whitespace-nowrap"
                                                        onclick="toggleClientStatus(<?php echo $client['user_id']; ?>, true)">
                                                    Activate
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?php include('../../assets/includes/footer-dashboard.php'); ?>
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

    <!-- Activate Confirmation Modal -->
    <div id="activateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Confirm Activation</h3>
                    <button class="text-gray-600 hover:text-gray-800" onclick="closeActivateModal()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <p class="text-gray-600 mb-4">Are you sure you want to activate this client? They will regain access to the platform.</p>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="closeActivateModal()" 
                            class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded transition-colors duration-200">
                        Cancel
                    </button>
                    <button onclick="confirmActivate()" 
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors duration-200">
                        Activate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/script-dashboard.js" defer></script>
    <script src="../../assets/js/script-manage-clients.js" defer></script>
</body>

</html> 