<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Client');

// Mock data for demonstration
$bookings = [
    [
        'id' => 1,
        'consultant' => 'Sarah Johnson',
        'expertise' => 'Nutrition & Diet Planning',
        'date' => '2024-03-25',
        'time' => '14:00:00',
        'duration' => 60,
        'status' => 'Upcoming',
        'price' => 75,
        'notes' => 'Initial consultation for diet planning',
        'meeting_link' => 'https://meet.idafu.com/sarah-johnson/123'
    ],
    [
        'id' => 2,
        'consultant' => 'Mike Wilson',
        'expertise' => 'Fitness Training',
        'date' => '2024-03-28',
        'time' => '11:00:00',
        'duration' => 45,
        'status' => 'Upcoming',
        'price' => 65,
        'notes' => 'Follow-up session - workout plan review',
        'meeting_link' => 'https://meet.idafu.com/mike-wilson/456'
    ],
    [
        'id' => 3,
        'consultant' => 'Emma Davis',
        'expertise' => 'Wellness Coaching',
        'date' => '2024-03-15',
        'time' => '15:30:00',
        'duration' => 60,
        'status' => 'Completed',
        'price' => 70,
        'notes' => 'Stress management techniques discussion',
        'meeting_link' => null
    ],
    [
        'id' => 4,
        'consultant' => 'Nicole Davis',
        'expertise' => 'Wellness Coaching',
        'date' => '2024-03-17',
        'time' => '12:30:00',
        'duration' => 30,
        'status' => 'Cancelled',
        'price' => 70,
        'notes' => 'Stress management techniques discussion',
        'meeting_link' => null
    ]
];
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
                        <button data-tab="all" class="tab-btn whitespace-nowrap px-3 py-2 border-b-2 border-idafu-primary text-idafu-primary text-sm">
                            All Bookings
                        </button>
                        <button data-tab="upcoming" class="tab-btn whitespace-nowrap px-3 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 text-sm">
                            Upcoming
                        </button>
                        <button data-tab="completed" class="tab-btn whitespace-nowrap px-3 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 text-sm">
                            Completed
                        </button>
                        <button data-tab="cancelled" class="tab-btn whitespace-nowrap px-3 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 text-sm">
                            Cancelled
                        </button>
                    </nav>
                </div>

                <!-- Bookings List -->
                <div class="space-y-4">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
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
                                        <p class="font-medium"><?php echo $booking['duration']; ?> minutes</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Price</p>
                                        <p class="font-medium">$<?php echo $booking['price']; ?></p>
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
                                        <button class="flex-1 sm:flex-none px-3 py-1.5 border border-red-300 text-red-700 rounded hover:bg-red-50 transition-colors duration-200 text-sm">
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
    </div>

    <script src="../../assets/js/script-dashboard.js" defer></script>
</body>

</html>