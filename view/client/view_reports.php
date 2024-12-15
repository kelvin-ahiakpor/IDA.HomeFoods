<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Client');

// Mock data for demonstration
$metrics = [
    'total_consultations' => 12,
    'upcoming_sessions' => 2,
    'completed_sessions' => 9,
    'cancelled_sessions' => 1,
    'avg_rating_given' => 4.5,
    'total_spent' => 875
];

$consultationHistory = [
    [
        'date' => '2024-03-15',
        'consultant' => 'Sarah Johnson',
        'expertise' => 'Nutrition & Diet Planning',
        'duration' => 60,
        'status' => 'Completed',
        'rating_given' => 5,
        'cost' => 75,
        'feedback' => 'Excellent session, very informative'
    ],
    [
        'date' => '2024-02-28',
        'consultant' => 'Mike Wilson',
        'expertise' => 'Fitness Training',
        'duration' => 45,
        'status' => 'Completed',
        'rating_given' => 4,
        'cost' => 65,
        'feedback' => 'Good workout plan, looking forward to next session'
    ]
];
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
                                        <?php echo htmlspecialchars($session['expertise']); ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                            <?php echo $session['status'] === 'Completed' ? 
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