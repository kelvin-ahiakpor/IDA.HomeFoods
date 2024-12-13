<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Admin');

// Get consultant ID from URL
$consultantId = isset($_GET['id']) ? $_GET['id'] : null;


// Mock data for demonstration (replace with actual database query)
$consultant = [
    'email' => 'john.smith@example.com',
    'name' => 'John Smith',
    'expertise' => 'Fitness Training',
    'image' => 'john_smith.jpg',
    'status' => 'Active',
    'joined_date' => '2024-01-15',
    'last_active' => '2024-03-20',
    'total_clients' => 25,
    'rating' => 4.8,
    'bio' => 'Certified fitness trainer with 10+ years of experience...',
    'specializations' => ['Weight Training', 'HIIT', 'Nutrition Planning'],
    'certifications' => [
        ['name' => 'Certified Personal Trainer', 'issuer' => 'ACE', 'year' => '2018'],
        ['name' => 'Sports Nutrition Specialist', 'issuer' => 'NASM', 'year' => '2020']
    ],
    'pending_updates' => [
        'certifications' => [
            [
                'name' => 'Advanced Nutrition Certification',
                'issuer' => 'ISSA',
                'year' => '2024',
                'submitted_date' => '2024-03-15',
                'proof_document' => 'cert_proof.pdf'
            ]
        ],
        'specializations' => [
            [
                'name' => 'Prenatal Fitness',
                'submitted_date' => '2024-03-18',
                'description' => 'Completed 200-hour specialized training'
            ],
            [
                'name' => 'Senior Fitness',
                'submitted_date' => '2024-03-18',
                'description' => 'Specialized in elderly care and exercise'
            ]
        ]
    ]
];

// Add this function at the top of the file after the mock data
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
                                    <p class="text-sm sm:text-base font-semibold"><?php echo date('M d, Y', strtotime($consultant['joined_date'])); ?></p>
                                </div>
                                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                                    <p class="text-xs sm:text-sm text-gray-500">Last Active</p>
                                    <p class="text-sm sm:text-base font-semibold"><?php echo date('M d, Y', strtotime($consultant['last_active'])); ?></p>
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
                                                    <p class="text-sm text-gray-600">
                                                        <?php echo $cert['issuer']; ?> • <?php echo $cert['year']; ?>
                                                    </p>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        Submitted on <?php echo date('M d, Y', strtotime($cert['submitted_date'])); ?>
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
                                                        Submitted on <?php echo date('M d, Y', strtotime($spec['submitted_date'])); ?>
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
</body>

</html>