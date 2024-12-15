<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Admin');

// Get consultant email from URL
$consultantEmail = isset($_GET['id']) ? $_GET['id'] : null;

if (!$consultantEmail) {
    header('Location: manage_consultants.php');
    exit;
}

// Fetch consultant data
function fetchConsultantData($email) {
    global $conn;
    
    // Get basic user and consultant info
    $query = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.profile_picture,
                     c.expertise, c.total_clients, c.rating, c.status,
                     c.bio, c.joined_date, c.last_active,
                     ca.application_id, ca.status as application_status
              FROM ida_users u
              LEFT JOIN ida_consultants c ON u.user_id = c.consultant_id
              LEFT JOIN ida_consultant_applications ca ON u.user_id = ca.user_id
              WHERE u.email = ? AND (ca.status = 'Pending' OR ca.status IS NULL)";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if (!$result) {
        return null;
    }

    // Get specializations
    $specQuery = "SELECT name FROM ida_consultant_specializations 
                 WHERE consultant_id = ? AND status = 'Active'";
    $specStmt = $conn->prepare($specQuery);
    $specStmt->bind_param('i', $result['user_id']);
    $specStmt->execute();
    $specializations = [];
    $specResult = $specStmt->get_result();
    while ($row = $specResult->fetch_assoc()) {
        $specializations[] = $row['name'];
    }

    // Get certifications
    $certQuery = "SELECT name, issuer, year, status 
                 FROM ida_consultant_certifications 
                 WHERE consultant_id = ? AND status = 'Active'";
    $certStmt = $conn->prepare($certQuery);
    $certStmt->bind_param('i', $result['user_id']);
    $certStmt->execute();
    $certifications = [];
    $certResult = $certStmt->get_result();
    while ($row = $certResult->fetch_assoc()) {
        $certifications[] = $row;
    }

    // Get pending updates
    $pendingUpdates = [
        'certifications' => [],
        'specializations' => []
    ];

    // Pending certifications
    $pendingCertQuery = "SELECT name, issuer, year, submitted_date, proof_document 
                        FROM ida_consultant_certifications 
                        WHERE consultant_id = ? AND status = 'Pending'";
    $pendingCertStmt = $conn->prepare($pendingCertQuery);
    $pendingCertStmt->bind_param('i', $result['user_id']);
    $pendingCertStmt->execute();
    $pendingCertResult = $pendingCertStmt->get_result();
    while ($row = $pendingCertResult->fetch_assoc()) {
        $pendingUpdates['certifications'][] = [
            'name' => $row['name'],
            'issuer' => $row['issuer'],
            'year' => $row['year'],
            'submitted_date' => $row['submitted_date'],
            'proof_document' => $row['proof_document']
        ];
    }

    // Pending specializations
    $pendingSpecQuery = "SELECT name, description, submitted_date 
                        FROM ida_consultant_specializations 
                        WHERE consultant_id = ? AND status = 'Pending'";
    $pendingSpecStmt = $conn->prepare($pendingSpecQuery);
    $pendingSpecStmt->bind_param('i', $result['user_id']);
    $pendingSpecStmt->execute();
    $pendingSpecResult = $pendingSpecStmt->get_result();
    while ($row = $pendingSpecResult->fetch_assoc()) {
        $pendingUpdates['specializations'][] = [
            'name' => $row['name'],
            'description' => $row['description'],
            'submitted_date' => $row['submitted_date']
        ];
    }

    // Format the data
    return [
        'user_id' => $result['user_id'],
        'email' => $result['email'],
        'name' => $result['first_name'] . ' ' . $result['last_name'],
        'expertise' => $result['expertise'],
        'image' => $result['profile_picture'],
        'status' => $result['status'],
        'application_status' => $result['application_status'],
        'application_id' => $result['application_id'],
        'joined_date' => $result['joined_date'],
        'last_active' => $result['last_active'],
        'total_clients' => $result['total_clients'],
        'rating' => $result['rating'],
        'bio' => $result['bio'],
        'specializations' => $specializations,
        'certifications' => $certifications,
        'pending_updates' => $pendingUpdates
    ];
}

// Fetch the consultant data
$consultant = fetchConsultantData($consultantEmail);

if (!$consultant) {
    header('Location: manage_consultants.php');
    exit;
}

function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return $initials;
}

// Format expertise for display
function formatExpertise($expertise) {
    $areas = json_decode($expertise, true);
    if (!is_array($areas)) {
        return ucwords(str_replace('_', ' ', $expertise));
    }
    return implode(', ', array_map(function($area) {
        return ucwords(str_replace('_', ' ', $area));
    }, $areas));
}

// Update the date formatting to handle null values
function formatDate($date) {
    if (!$date) return 'Not available';
    return date('M d, Y', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('../../assets/includes/head-dashboard.php'); ?>
    <title>View Consultant | idafü</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>

<body class="bg-gray-50 font-sans antialiased">
    <div class="flex flex-col min-h-screen">
        <!-- Main Content -->
        <main class="flex-grow pt-16 px-4 sm:px-6">
            <div class="max-w-7xl mx-auto py-2 sm:py-4">
                <!-- Back Button -->
                <a href="./manage_consultants.php" class="inline-flex items-center text-idafu-primary hover:underline mb-3">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Consultants
                </a>

                <!-- Consultant Profile -->
                <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                    <!-- Profile Header -->
                    <div class="flex flex-col lg:flex-row gap-6 mb-6">
                        <!-- Left Column: Image and Basic Info -->
                        <div class="flex flex-col sm:flex-row lg:flex-col items-center sm:items-start text-center sm:text-left gap-4">
                            <!-- Profile Image/Initials -->
                            <?php
                            $imagePath = "../../assets/images/consultants/" . $consultant['image'];
                            if (file_exists($imagePath)): ?>
                                <img src="<?php echo $imagePath; ?>" 
                                     alt="<?php echo $consultant['name']; ?>" 
                                     class="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover">
                            <?php else: ?>
                                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-idafu-primary text-white text-3xl flex items-center justify-center">
                                    <?php echo getInitials($consultant['name']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Name, Email, Status -->
                            <div class="flex flex-col items-center sm:items-start">
                                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-1"><?php echo $consultant['name']; ?></h1>
                                <p class="text-gray-600 mb-2"><?php echo $consultant['email']; ?></p>
                                <span class="px-3 py-1 text-sm rounded-full inline-flex items-center <?php echo $consultant['status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo $consultant['status']; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Right Column: Stats Grid -->
                        <div class="flex-grow">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                                    <p class="text-xs sm:text-sm text-gray-500">Joined Date</p>
                                    <p class="text-sm sm:text-base font-semibold"><?php echo formatDate($consultant['joined_date']); ?></p>
                                </div>
                                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                                    <p class="text-xs sm:text-sm text-gray-500">Last Active</p>
                                    <p class="text-sm sm:text-base font-semibold"><?php echo formatDate($consultant['last_active']); ?></p>
                                </div>
                                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                                    <p class="text-xs sm:text-sm text-gray-500">Total Clients</p>
                                    <p class="text-sm sm:text-base font-semibold"><?php echo $consultant['total_clients']; ?></p>
                                </div>
                                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                                    <p class="text-xs sm:text-sm text-gray-500">Rating</p>
                                    <p class="text-sm sm:text-base font-semibold"><?php echo $consultant['rating']; ?> / 5.0</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-2 justify-start mb-6">
                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($consultant), ENT_QUOTES, 'UTF-8'); ?>)" 
                                class="text-idafu-primary hover:bg-idafu-lightBlue px-4 py-2 rounded transition-colors duration-200">
                            Edit Profile
                        </button>
                        <?php if ($consultant['status'] === 'Active'): ?>
                            <button onclick="openDeactivateModal('<?php echo $consultant['email']; ?>')" 
                                    class="text-idafu-accentDeeper hover:bg-red-50 px-4 py-2 rounded transition-colors duration-200">
                                Deactivate Account
                            </button>
                        <?php else: ?>
                            <button onclick="activateConsultant(<?php echo $consultant['email']; ?>)"
                                    class="text-green-600 hover:bg-green-50 px-4 py-2 rounded transition-colors duration-200">
                                Activate Account
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Bio Section -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-2">About</h2>
                        <p class="text-gray-600"><?php echo $consultant['bio']; ?></p>
                    </div>

                    <!-- Specializations -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-2">Specializations</h2>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($consultant['specializations'] as $specialization): ?>
                                <span class="px-3 py-1 bg-idafu-lightBlue text-idafu-primary rounded-full text-sm">
                                    <?php echo $specialization; ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Certifications -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-2">Certifications</h2>
                        <div class="space-y-3">
                            <?php foreach ($consultant['certifications'] as $certification): ?>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <p class="font-medium text-gray-800"><?php echo $certification['name']; ?></p>
                                    <p class="text-sm text-gray-600">
                                        <?php echo $certification['issuer']; ?> • <?php echo $certification['year']; ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Pending Updates -->
                    <?php if (!empty($consultant['pending_updates']['certifications']) || !empty($consultant['pending_updates']['specializations'])): ?>
                        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mt-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Pending Updates</h2>
                            
                            <!-- Pending Certifications -->
                            <?php if (!empty($consultant['pending_updates']['certifications'])): ?>
                                <div class="mb-6">
                                    <h3 class="text-base font-medium text-gray-700 mb-3">New Certifications</h3>
                                    <div class="space-y-4">
                                        <?php foreach ($consultant['pending_updates']['certifications'] as $cert): ?>
                                            <div class="bg-yellow-50 border border-yellow-100 rounded-lg p-4">
                                                <div class="flex flex-col sm:flex-row justify-between sm:items-start gap-4">
                                                    <div class="flex-grow">
                                                        <p class="font-medium text-gray-800"><?php echo $cert['name']; ?></p>
                                                        <!-- <p class="text-sm text-gray-600"> --->
                                                            <!-- php echo $cert['issuer']; ?> • php echo $cert['year']; ?> -->
                                                        <!-- </p> --> 
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            Submitted on <?php echo formatDate($cert['submitted_date']); ?>
                                                        </p>
                                                    </div>
                                                    <div class="flex flex-wrap gap-2 sm:flex-nowrap">
                                                        <a href="../../assets/documents/certifications/<?php echo $cert['proof_document']; ?>" 
                                                           target="_blank"
                                                           class="text-idafu-primary hover:bg-idafu-lightBlue px-2 py-1 rounded text-sm transition-colors duration-200 whitespace-nowrap">
                                                            View Document
                                                        </a>
                                                        <button onclick="approveCertification('<?php echo htmlspecialchars(json_encode($cert), ENT_QUOTES, 'UTF-8'); ?>')"
                                                                class="text-green-600 hover:bg-green-50 px-2 py-1 rounded text-sm transition-colors duration-200 whitespace-nowrap">
                                                            Approve
                                                        </button>
                                                        <button onclick="rejectUpdate('certification', '<?php echo $cert['name']; ?>')"
                                                                class="text-idafu-accentDeeper hover:bg-red-50 px-2 py-1 rounded text-sm transition-colors duration-200 whitespace-nowrap">
                                                            Reject
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Pending Specializations -->
                            <?php if (!empty($consultant['pending_updates']['specializations'])): ?>
                                <div>
                                    <h3 class="text-base font-medium text-gray-700 mb-3">New Specializations</h3>
                                    <div class="space-y-4">
                                        <?php foreach ($consultant['pending_updates']['specializations'] as $spec): ?>
                                            <div class="bg-yellow-50 border border-yellow-100 rounded-lg p-4">
                                                <div class="flex flex-col sm:flex-row justify-between sm:items-start gap-4">
                                                    <div class="flex-grow">
                                                        <p class="font-medium text-gray-800"><?php echo $spec['name']; ?></p>
                                                        <p class="text-sm text-gray-600"><?php echo $spec['description']; ?></p>
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            Submitted on <?php echo formatDate($spec['submitted_date']); ?>
                                                        </p>
                                                    </div>
                                                    <div class="flex flex-wrap gap-2 sm:flex-nowrap">
                                                        <button onclick="approveSpecialization('<?php echo htmlspecialchars(json_encode($spec), ENT_QUOTES, 'UTF-8'); ?>')"
                                                                class="text-green-600 hover:bg-green-50 px-2 py-1 rounded text-sm transition-colors duration-200 whitespace-nowrap">
                                                            Approve
                                                        </button>
                                                        <button onclick="rejectUpdate('specialization', '<?php echo $spec['name']; ?>')"
                                                                class="text-idafu-accentDeeper hover:bg-red-50 px-2 py-1 rounded text-sm transition-colors duration-200 whitespace-nowrap">
                                                            Reject
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Approval Action Section -->
                    <?php if (!$consultant['status'] || $consultant['status'] === 'Pending'): ?>
                        <div class="bg-yellow-50 border border-yellow-100 rounded-lg p-4 mb-6">
                            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                                <div>
                                    <h3 class="text-base font-medium text-gray-800 mb-1">Pending Application</h3>
                                    <p class="text-sm text-gray-600">This consultant is awaiting approval to join the platform.</p>
                                </div>
                                <div class="flex gap-2">
                                    <button onclick="openApprovalModal(<?php echo htmlspecialchars(json_encode([
                                        'user_id' => $consultant['user_id'],
                                        'application_id' => $consultant['application_id'],
                                        'name' => $consultant['name'],
                                        'email' => $consultant['email']
                                    ]), ENT_QUOTES, 'UTF-8'); ?>)"
                                            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors duration-200">
                                        Approve Application
                                    </button>
                                    <button onclick="openRejectModal(<?php echo htmlspecialchars(json_encode([
                                        'user_id' => $consultant['user_id'],
                                        'application_id' => $consultant['application_id'],
                                        'name' => $consultant['name'],
                                        'email' => $consultant['email']
                                    ]), ENT_QUOTES, 'UTF-8'); ?>)"
                                            class="text-idafu-accentDeeper hover:bg-red-50 px-4 py-2 rounded border border-current transition-colors duration-200">
                                        Reject Application
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?php include('../../assets/includes/footer-dashboard.php'); ?>
    </div>

    <script src="../../assets/js/script-dashboard.js" defer></script>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Edit Consultant Profile</h3>
                    <button class="text-gray-600 hover:text-gray-800" onclick="closeEditModal()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form id="editConsultantForm" class="space-y-4">
                    <input type="hidden" id="editConsultantId">
                    <div>
                        <label for="editName" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" id="editName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0">
                    </div>
                    <div>
                        <label for="editEmail" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="editEmail" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0">
                    </div>
                    <div>
                        <label for="editExpertise" class="block text-sm font-medium text-gray-700">Expertise</label>
                        <input type="text" id="editExpertise" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0">
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
                
                <p class="text-gray-600 mb-4">Are you sure you want to approve this consultant application?</p>
                
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
                    <p class="text-gray-600 mb-2">Are you sure you want to reject this consultant application?</p>
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
</body>

</html>