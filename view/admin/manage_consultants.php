<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Admin');

// Add this helper function near the top of the file, after the other helper functions
function formatExpertise($expertise) {
    // Decode JSON string to array
    $areas = json_decode($expertise, true);
    
    if (!is_array($areas)) {
        return ucwords(str_replace('_', ' ', $expertise));
    }
    
    // Format each area
    $formatted = array_map(function($area) {
        // Replace underscore with space and capitalize each word
        return ucwords(str_replace('_', ' ', $area));
    }, $areas);
    
    // Join back with commas
    return implode(', ', $formatted);
}

// Function to fetch consultants with their metrics
function fetchConsultants($status = 'Active') {
    global $conn;
    
    $query = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.profile_picture,
                     c.expertise, c.total_clients, c.rating, c.status
              FROM ida_users u
              INNER JOIN ida_consultants c ON u.user_id = c.consultant_id
              WHERE c.status = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $status);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $consultants = [];
    while ($row = $result->fetch_assoc()) {
        $consultants[] = [
            'user_id' => $row['user_id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'email' => $row['email'],
            'expertise' => $row['expertise'],
            'total_clients' => $row['total_clients'],
            'rating' => $row['rating'],
            'image' => $row['profile_picture']
        ];
    }
    
    return $consultants;
}

// Function to fetch pending consultant applications
function fetchPendingApplications() {
    global $conn;
    
    $query = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.profile_picture,
                     ca.application_id, ca.expertise, ca.submitted_at
              FROM ida_users u
              INNER JOIN ida_consultant_applications ca ON u.user_id = ca.user_id
              WHERE ca.status = 'Pending'";
              
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $applications = [];
    while ($row = $result->fetch_assoc()) {
        // Fetch certifications for this application
        $certQuery = "SELECT name FROM ida_consultant_certifications 
                     WHERE consultant_id = ? AND status = 'Pending'";
        $certStmt = $conn->prepare($certQuery);
        $certStmt->bind_param('i', $row['user_id']);
        $certStmt->execute();
        $certResult = $certStmt->get_result();
        
        $certifications = [];
        while ($cert = $certResult->fetch_assoc()) {
            $certifications[] = $cert['name'];
        }
        
        $applications[] = [
            'user_id' => $row['user_id'],
            'application_id' => $row['application_id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'email' => $row['email'],
            'expertise' => $row['expertise'],
            'certifications' => $certifications,
            'image' => $row['profile_picture']
        ];
    }
    
    return $applications;
}

// Fetch consultants and applications
$activeConsultants = fetchConsultants('Active');
$inactiveConsultants = fetchConsultants('Inactive');
$pendingRequests = fetchPendingApplications();

// Get metrics for the dashboard
$stmt = $conn->prepare("SELECT 
    (SELECT COUNT(*) FROM ida_consultants WHERE status = 'Active') as total_active,
    (SELECT COUNT(*) FROM ida_consultant_applications WHERE status = 'Pending') as total_pending,
    (SELECT COUNT(*) FROM ida_consultants WHERE status = 'Inactive') as total_inactive
");
$stmt->execute();
$metrics = $stmt->get_result()->fetch_assoc();

// Helper function to get initials from name
function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return $initials;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('../../assets/includes/head-dashboard.php'); ?>
    <title>Manage Consultants | idafü</title>
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
            <div class="max-w-7xl mx-auto py-4 sm:py-6">
                <!-- Page Title -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2 sm:mb-0">Manage Consultants</h1>
                    <button class="w-full sm:w-auto px-3 py-2 sm:px-4 sm:py-2 bg-idafu-primary text-white rounded-lg hover:bg-idafu-primary-dark text-sm sm:text-base">
                        + Add Consultant
                    </button>
                </div>

                <!-- Metrics Section -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4 mb-4 sm:mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 text-center">
                        <h3 class="text-xs sm:text-sm text-gray-500">Total Consultants</h3>
                        <p class="text-xl sm:text-2xl font-semibold mt-1 sm:mt-2"><?php echo $metrics['total_active']; ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 text-center">
                        <h3 class="text-xs sm:text-sm text-gray-500">Pending Approvals</h3>
                        <p class="text-xl sm:text-2xl font-semibold mt-1 sm:mt-2"><?php echo $metrics['total_pending']; ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 text-center">
                        <h3 class="text-xs sm:text-sm text-gray-500">Inactive Consultants</h3>
                        <p class="text-xl sm:text-2xl font-semibold mt-1 sm:mt-2"><?php echo $metrics['total_inactive']; ?></p>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="mb-4">
                    <input type="text" class="w-full px-3 py-2 sm:px-4 sm:py-2 border rounded-lg focus:outline-none text-sm sm:text-base" placeholder="Search by name, email, or expertise">
                </div>

                <!-- Tabs -->
                <div class="flex overflow-x-auto space-x-4 border-b mb-4 sm:mb-6">
                    <button data-tab="consultants" class="tab-btn py-2 px-3 sm:px-4 text-sm sm:text-base font-medium text-idafu-primary border-b-2 border-idafu-primary whitespace-nowrap">Consultants</button>
                    <button data-tab="pending" class="tab-btn py-2 px-3 sm:px-4 text-sm sm:text-base font-medium text-gray-500 hover:text-idafu-primary whitespace-nowrap">Pending Requests</button>
                    <button data-tab="search" id="searchTab" class="tab-btn py-2 px-3 sm:px-4 text-sm sm:text-base font-medium text-gray-500 hover:text-idafu-primary whitespace-nowrap hidden">Search Results</button>
                </div>

                <!-- Consultants Section -->
                <div id="consultantsContent" class="tab-content">
                    <!-- Active Consultants -->
                    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-6 overflow-x-auto">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Active Consultants</h2>
                        <table class="w-full text-xs sm:text-sm text-left text-gray-500">
                            <thead class="bg-idafu-lightBlue text-gray-700">
                                <tr>
                                    <th class="px-2 sm:px-4 py-2">Name</th>
                                    <th class="px-2 sm:px-4 py-2">Email</th>
                                    <th class="px-2 sm:px-4 py-2 w-1/5">Expertise</th>
                                    <th class="px-2 sm:px-4 py-2 w-1/5">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activeConsultants as $consultant): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-2 sm:px-4 py-3">
                                            <div class="flex items-center space-x-2">
                                                <?php
                                                $imagePath = "../../assets/images/consultants/" . $consultant['image'];
                                                if (file_exists($imagePath)): ?>
                                                    <div class="flex-shrink-0">
                                                        <img src="<?php echo $imagePath; ?>" 
                                                             alt="<?php echo $consultant['name']; ?>" 
                                                             class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover">
                                                    </div>
                                                <?php else: ?>
                                                    <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-idafu-primary text-white flex items-center justify-center text-xs sm:text-sm">
                                                        <?php echo getInitials($consultant['name']); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <span class="text-gray-900 truncate"><?php echo $consultant['name']; ?></span>
                                            </div>
                                        </td>
                                        <td class="px-2 sm:px-4 py-3"><?php echo $consultant['email']; ?></td>
                                        <td class="px-2 sm:px-4 py-3"><?php echo formatExpertise($consultant['expertise']); ?></td>
                                        <td class="px-2 sm:px-4 py-3">
                                            <div class="flex flex-wrap gap-2 justify-end">
                                                <button class="text-idafu-primary hover:bg-idafu-lightBlue px-2 py-1 rounded transition-colors duration-200 whitespace-nowrap" 
                                                        onclick="window.open('./view_consultant.php?id=<?php echo urlencode($consultant['email']); ?>', '_blank')">
                                                    View
                                                </button>
                                                <button class="text-idafu-primary hover:bg-idafu-lightBlue px-2 py-1 rounded transition-colors duration-200 whitespace-nowrap"
                                                        onclick='openEditModal(<?php echo json_encode($consultant); ?>)'>
                                                    Edit
                                                </button>
                                                <button onclick="openDeactivateModal(<?php echo $consultant['user_id']; ?>, 'consultant')" 
                                                        class="text-idafu-accentDeeper hover:bg-red-50 px-2 py-1 rounded transition-colors duration-200 whitespace-nowrap">
                                                    Deactivate
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Inactive Consultants -->
                    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-6 overflow-x-auto">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Inactive Consultants</h2>
                        <table class="w-full text-xs sm:text-sm text-left text-gray-500">
                            <thead class="bg-idafu-lightBlue text-gray-700">
                                <tr>
                                    <th class="px-2 sm:px-4 py-2">Name</th>
                                    <th class="px-2 sm:px-4 py-2">Email</th>
                                    <th class="px-2 sm:px-4 py-2 w-1/5">Expertise</th>
                                    <th class="px-2 sm:px-4 py-2 w-1/5">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inactiveConsultants as $consultant): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-2 sm:px-4 py-3">
                                            <div class="flex items-center space-x-2">
                                                <?php
                                                $imagePath = "../../assets/images/consultants/" . $consultant['image'];
                                                if (file_exists($imagePath)): ?>
                                                    <div class="flex-shrink-0">
                                                        <img src="<?php echo $imagePath; ?>" 
                                                             alt="<?php echo $consultant['name']; ?>" 
                                                             class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover">
                                                    </div>
                                                <?php else: ?>
                                                    <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-idafu-primary text-white flex items-center justify-center text-xs sm:text-sm">
                                                        <?php echo getInitials($consultant['name']); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <span class="text-gray-900 truncate"><?php echo $consultant['name']; ?></span>
                                            </div>
                                        </td>
                                        <td class="px-2 sm:px-4 py-3"><?php echo $consultant['email']; ?></td>
                                        <td class="px-2 sm:px-4 py-3"><?php echo formatExpertise($consultant['expertise']); ?></td>
                                        <td class="px-2 sm:px-4 py-3">
                                            <div class="flex flex-wrap gap-2 justify-end">
                                                <button class="text-idafu-primary hover:bg-idafu-lightBlue px-2 py-1 rounded transition-colors duration-200 whitespace-nowrap" 
                                                        onclick="window.open('./view_consultant.php?id=<?php echo urlencode($consultant['email']); ?>', '_blank')">
                                                    View
                                                </button>
                                                <button class="text-idafu-primary hover:bg-idafu-lightBlue px-2 py-1 rounded transition-colors duration-200 whitespace-nowrap"
                                                        onclick='openEditModal(<?php echo json_encode($consultant); ?>)'>
                                                    Edit
                                                </button>
                                                <button onclick="openActivateModal(<?php echo $consultant['user_id']; ?>, 'consultant')"
                                                        class="text-green-600 hover:bg-green-50 px-2 py-1 rounded transition-colors duration-200">
                                                    Activate
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pending Requests Section -->
                <div id="pendingContent" class="tab-content hidden">
                    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 overflow-x-auto">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Pending Requests</h2>
                        <table class="w-full text-xs sm:text-sm text-left text-gray-500">
                            <thead class="bg-idafu-lightBlue text-gray-700">
                                <tr>
                                    <th class="px-2 sm:px-4 py-2">Name</th>
                                    <th class="px-2 sm:px-4 py-2">Email</th>
                                    <th class="px-2 sm:px-4 py-2">Expertise</th>
                                    <th class="px-2 sm:px-4 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingRequests as $request): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-2 sm:px-4 py-3">
                                            <div class="flex items-center space-x-2">
                                                <?php
                                                $imagePath = "../../assets/images/consultants/" . $request['image'];
                                                if (file_exists($imagePath)): ?>
                                                    <div class="flex-shrink-0">
                                                        <img src="<?php echo $imagePath; ?>" 
                                                             alt="<?php echo $request['name']; ?>" 
                                                             class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover">
                                                    </div>
                                                <?php else: ?>
                                                    <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-idafu-primary text-white flex items-center justify-center text-xs sm:text-sm">
                                                        <?php echo getInitials($request['name']); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <span class="text-gray-900 truncate"><?php echo $request['name']; ?></span>
                                            </div>
                                        </td>
                                        <td class="px-2 sm:px-4 py-3"><?php echo $request['email']; ?></td>
                                        <td class="px-2 sm:px-4 py-3"><?php echo formatExpertise($request['expertise']); ?></td>
                                        <td class="px-2 sm:px-4 py-3">
                                            <div class="flex flex-wrap gap-2 justify-end">
                                                <button class="text-idafu-primary hover:bg-idafu-lightBlue px-2 py-1 rounded transition-colors duration-200 whitespace-nowrap" 
                                                        onclick="window.open('./view_consultant.php?id=<?php echo urlencode($request['email']); ?>', '_blank')">
                                                    View
                                                </button>
                                                <button onclick="openApprovalModal(<?php echo htmlspecialchars(json_encode([
                                                    'user_id' => $request['user_id'],
                                                    'application_id' => $request['application_id'],
                                                    'name' => $request['name'],
                                                    'email' => $request['email']
                                                ]), ENT_QUOTES, 'UTF-8'); ?>)"
                                                        class="text-idafu-primary hover:bg-idafu-lightBlue px-2 py-1 rounded transition-colors duration-200">
                                                    Approve
                                                </button>
                                                <button onclick="openRejectModal(<?php echo htmlspecialchars(json_encode([
                                                    'user_id' => $request['user_id'],
                                                    'application_id' => $request['application_id'],
                                                    'name' => $request['name'],
                                                    'email' => $request['email']
                                                ]), ENT_QUOTES, 'UTF-8'); ?>)"
                                                        class="text-idafu-accentDeeper hover:bg-red-50 px-2 py-1 rounded transition-colors duration-200 whitespace-nowrap">
                                                    Reject
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Search Results Section -->
                <div id="searchContent" class="tab-content hidden">
                    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 overflow-x-auto">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Search Results</h2>
                        <table class="w-full text-xs sm:text-sm text-left text-gray-500">
                            <thead class="bg-idafu-lightBlue text-gray-700">
                                <tr>
                                    <th class="px-2 sm:px-4 py-2">Name</th>
                                    <th class="px-2 sm:px-4 py-2">Email</th>
                                    <th class="px-2 sm:px-4 py-2">Expertise</th>
                                    <th class="px-2 sm:px-4 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="searchBar" colspan="4" class="text-center py-4 text-xs sm:text-sm">Start typing to search...</td>
                                </tr>
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

    <!-- Edit Consultant Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Edit Consultant</h3>
                    <button class="text-gray-600 hover:text-gray-800" onclick="closeEditModal()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form id="editConsultantForm" class="space-y-4">
                    <input type="hidden" id="editConsultantId">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" id="editName" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-idafu-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="editEmail" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-idafu-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expertise</label>
                        <input type="text" id="editExpertise" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-idafu-primary">
                    </div>
                    
                    <div class="flex justify-between pt-4">
                        <button type="button" 
                                class="text-red-600 hover:bg-red-50 px-4 py-2 rounded transition-colors duration-200"
                                onclick="confirmDelete()">
                            Delete Account
                        </button>
                        <button type="submit" 
                                class="bg-idafu-primary text-white px-4 py-2 rounded hover:bg-idafu-primary-dark transition-colors duration-200">
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
                
                <p class="text-gray-600 mb-4">Are you sure you want to deactivate this consultant? They will no longer be able to access the platform.</p>
                
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

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Confirm Deletion</h3>
                    <button class="text-gray-600 hover:text-gray-800" onclick="closeDeleteModal()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <p class="text-gray-600 mb-4">Are you sure you want to permanently delete this consultant? This action cannot be undone.</p>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="closeDeleteModal()" 
                            class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded transition-colors duration-200">
                        Cancel
                    </button>
                    <button onclick="confirmDeleteFinal()" 
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors duration-200">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Modal -->
    <div id="approvalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Confirm Approval</h3>
                    <button class="text-gray-600 hover:text-gray-800" onclick="closeApprovalModal()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <p class="text-gray-600 mb-4">Are you sure you want to approve this consultant request?</p>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="closeApprovalModal()" 
                            class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded transition-colors duration-200">
                        Cancel
                    </button>
                    <button onclick="confirmApproval()" 
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors duration-200">
                        Approve
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Confirm Rejection</h3>
                    <button class="text-gray-600 hover:text-gray-800" onclick="closeRejectModal()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="mb-4">
                    <p class="text-gray-600 mb-2">Are you sure you want to reject this consultant request?</p>
                    <label class="block text-sm font-medium text-gray-700 mt-4">Reason for rejection:</label>
                    <textarea id="rejectionReason" rows="3" 
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0"
                            placeholder="Please provide a reason for rejection..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="closeRejectModal()" 
                            class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded transition-colors duration-200">
                        Cancel
                    </button>
                    <button onclick="confirmRejection()" 
                            class="px-4 py-2 bg-idafu-accentDeeper text-white rounded hover:bg-red-600 transition-colors duration-200">
                        Reject
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Activate Modal -->
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
                <p class="text-gray-600 mb-4">Are you sure you want to activate this consultant?</p>
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

    <!-- Deactivate Modal -->
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
                <p class="text-gray-600 mb-4">Are you sure you want to deactivate this consultant?</p>
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
</body>

</html>