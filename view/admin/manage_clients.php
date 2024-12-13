<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Admin');

// Mock data for clients
$clients = [
    [
        'user_id' => 1,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'total_bookings' => 15,
        'last_booking' => '2024-03-15',
        'is_active' => true
    ],
    [
        'user_id' => 2,
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane.smith@example.com',
        'total_bookings' => 8,
        'last_booking' => '2024-03-18',
        'is_active' => true
    ],
    [
        'user_id' => 3,
        'first_name' => 'Mike',
        'last_name' => 'Johnson',
        'email' => 'mike.j@example.com',
        'total_bookings' => 0,
        'last_booking' => null,
        'is_active' => false
    ]
];

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
        <header class="bg-white shadow-sm fixed w-full z-10">
            <div class="max-w-full mx-auto">
                <div class="flex items-center justify-between px-4 sm:px-6 py-3">
                    <div class="flex items-center space-x-2 sm:space-x-4">
                        <button class="lg:hidden p-2 hover:bg-gray-100 rounded-lg" id="menuBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <img src="../../assets/images/IDAFU-logo-min-black.png" alt="IDAFU Logo" class="h-6 sm:h-9">
                    </div>
                    <nav class="hidden lg:flex items-center">
                        <div class="flex space-x-6 md:space-x-12">
                            <a href="./dashboard.php" class="nav-item text-sm md:text-base">Overview</a>
                            <a href="./manage_consultants.php" class="nav-item text-sm md:text-base">Consultants</a>
                            <a href="./manage_clients.php" class="nav-item active text-sm md:text-base">Clients</a>
                            <a href="./view_reports.php" class="nav-item text-sm md:text-base">Reports</a>
                        </div>
                    </nav>
                    <div class="flex items-center">
                        <button class="relative group p-2 hover:bg-gray-100 rounded-full" id="profileBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" sm:width="24" sm:height="24" fill="currentColor" class="text-gray-600" viewBox="0 0 16 16">
                                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </header>

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
                        <input type="text" 
                               placeholder="Search clients..." 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-idafu-primary text-sm">
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
                                                        onclick="deactivateClient(<?php echo $client['user_id']; ?>)">
                                                    Deactivate
                                                </button>
                                            <?php else: ?>
                                                <button class="text-green-600 hover:bg-green-50 px-2 py-1 rounded transition-colors duration-200 whitespace-nowrap"
                                                        onclick="activateClient(<?php echo $client['user_id']; ?>)">
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
</body>

</html> 