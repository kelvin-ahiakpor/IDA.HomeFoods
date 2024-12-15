<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Consultant');

// Fetch current availability for the logged-in consultant
function fetchAvailability($userId) {
    global $conn;
    $query = "SELECT * FROM ida_consultants WHERE consultant_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$availability = fetchAvailability($_SESSION['user_id']);

// Handle form submission to update availability
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the submitted availability data
    // (This is where you would handle the logic to save the new availability)
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('../../assets/includes/head-dashboard.php'); ?>
    <title>Manage Availability | idafü</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <?php include('../../assets/includes/header-consultant.php'); ?>

        <!-- Profile Modal -->
        <?php include('../../assets/includes/profile-modal.php'); ?>

        <!-- Mobile Navigation Menu -->
        <?php include('../../assets/includes/mobile-menu.php'); ?>

        <!-- Main Content -->
        <main class="flex-grow pt-16 px-4 sm:px-6">
            <div class="max-w-7xl mx-auto py-2 sm:py-4">
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6">Manage Availability</h1>

                <form method="POST" id="availabilityForm" class="space-y-4">
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Set Your Availability</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="day" class="block text-sm font-medium text-gray-700">Day</label>
                                <select name="day" id="day" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0">
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                    <option value="Saturday">Saturday</option>
                                    <option value="Sunday">Sunday</option>
                                </select>
                            </div>
                            <div>
                                <label for="time_slot" class="block text-sm font-medium text-gray-700">Time Slot</label>
                                <input type="time" name="time_slot" id="time_slot" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0" required>
                            </div>
                        </div>
                        <div class="flex justify-end mt-4">
                            <button type="submit" class="px-4 py-2 bg-idafu-primary text-white rounded hover:bg-idafu-primaryDarker transition-colors duration-200">Add Availability</button>
                        </div>
                    </div>
                </form>

                <!-- Display Current Availability -->
                <h2 class="text-lg font-semibold text-gray-800 mb-4 mt-6">Current Availability</h2>
                <div class="space-y-4">
                    <?php if ($availability): ?>
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <p class="text-sm text-gray-600">Available on: <?php echo htmlspecialchars($availability['availability']); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <p class="text-sm text-gray-600">No availability set.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?php include('../../assets/includes/footer-dashboard.php'); ?>
    </div>

    <script src="../../assets/js/script-dashboard.js" defer></script>
</body>
</html> 