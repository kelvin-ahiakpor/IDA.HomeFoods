<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Client');

// Dummy consultant data for demonstration
$consultantId = $_GET['id'] ?? null;

// Mock data for the consultant
$consultant = [
    'id' => $consultantId,
    'name' => 'Sarah Johnson',
    'expertise' => 'Nutrition & Diet Planning',
    'bio' => 'Certified nutritionist with 8+ years of experience in personalized diet planning and wellness coaching.',
    'hourly_rate' => 75,
    'image' => '../assets/images/sarah_johnson.jpg' // Path to the consultant's image
];

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
        <!-- Profile Modal -->
        <?php include('../../assets/includes/profile-modal.php'); ?>

        <!-- Mobile Navigation Menu -->
        <?php include('../../assets/includes/mobile-menu.php'); ?>

        <!-- Logo -->
        <div class="flex items-center justify-center py-6">
            <img src="../../assets/images/IDAFU-logo-green.png" alt="IDAFU Logo" class="h-20 w-auto">
        </div>

        <!-- Back to Explore Consultants Link -->
        <div class="max-w-3xl mx-auto px-4 mb-4">
            <a href="explore_consultants.php" class="inline-flex items-center text-idafu-primary hover:text-idafu-primaryDarker">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Explore Consultants
            </a>
        </div>

        <main class="flex-grow pt-8 px-4 sm:px-6">
            <div class="max-w-3xl mx-auto py-2 sm:py-4 flex">
                <!-- Consultant Image -->
                <div class="w-1/3 pr-4">
                    <img src="<?php echo htmlspecialchars($consultant['image']); ?>" alt="<?php echo htmlspecialchars($consultant['name']); ?>" class="rounded-lg shadow-lg">
                </div>

                <!-- Booking Form -->
                <div class="w-2/3">
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6">Book a Consultation with <?php echo htmlspecialchars($consultant['name']); ?></h1>

                    <form id="bookingForm">
                        <input type="hidden" name="consultant_id" value="<?php echo htmlspecialchars($consultant['id']); ?>">
                        <div class="mb-4">
                            <label for="booking_date" class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="booking_date" id="booking_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0" required>
                        </div>
                        <div class="mb-4">
                            <label for="time_slot" class="block text-sm font-medium text-gray-700">Time Slot</label>
                            <input type="time" name="time_slot" id="time_slot" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0" required>
                        </div>
                        <div class="mb-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0" placeholder="Any additional information..."></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" onclick="window.history.back()" class="mr-2 px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-idafu-primary text-white rounded hover:bg-idafu-primaryDarker">Book Now</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?php include('../../assets/includes/footer-dashboard.php'); ?>
    </div>
    <script src="../../assets/js/script-dashboard.js" defer></script>
    <script src="../../assets/js/script-booking.js" defer></script>
</body>
</html> 