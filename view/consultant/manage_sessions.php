<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Consultant');

// Fetch sessions for the logged-in consultant
function fetchSessions($userId) {
    global $conn;
    $query = "SELECT * FROM ida_consultant_sessions WHERE consultant_id = ? ORDER BY start_time DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$sessions = fetchSessions($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('../../assets/includes/head-dashboard.php'); ?>
    <title>My Sessions | idafü</title>
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
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6">My Sessions</h1>

                <!-- Upcoming Sessions -->
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Upcoming Sessions</h2>
                <div class="space-y-4">
                    <?php foreach ($sessions as $session): ?>
                        <?php if ($session['status'] === 'Upcoming'): ?>
                            <div class="bg-white rounded-lg shadow-sm p-4">
                                <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($session['client_name']); ?></h3>
                                <p class="text-sm text-gray-600">
                                    <?php echo date('F j, Y', strtotime($session['start_time'])); ?> at <?php echo date('g:i A', strtotime($session['start_time'])); ?>
                                </p>
                                <div class="flex justify-end mt-2">
                                    <button class="text-idafu-primary hover:bg-idafu-lightBlue px-2 py-1 rounded" onclick="viewSessionDetails(<?php echo $session['session_id']; ?>)">View Details</button>
                                    <button class="text-red-600 hover:bg-red-50 px-2 py-1 rounded ml-2" onclick="cancelSession(<?php echo $session['session_id']; ?>)">Cancel</button>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Past Sessions -->
                <h2 class="text-lg font-semibold text-gray-800 mb-4 mt-6">Past Sessions</h2>
                <div class="space-y-4">
                    <?php foreach ($sessions as $session): ?>
                        <?php if ($session['status'] === 'Completed'): ?>
                            <div class="bg-white rounded-lg shadow-sm p-4">
                                <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($session['client_name']); ?></h3>
                                <p class="text-sm text-gray-600">
                                    <?php echo date('F j, Y', strtotime($session['start_time'])); ?> at <?php echo date('g:i A', strtotime($session['start_time'])); ?>
                                </p>
                                <div class="flex justify-end mt-2">
                                    <button class="text-idafu-primary hover:bg-idafu-lightBlue px-2 py-1 rounded" onclick="viewSessionDetails(<?php echo $session['session_id']; ?>)">View Details</button>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?php include('../../assets/includes/footer-dashboard.php'); ?>
    </div>

    <script src="../../assets/js/script-dashboard.js" defer></script>
    <script>
        function viewSessionDetails(sessionId) {
            // Logic to view session details
        }

        function cancelSession(sessionId) {
            // Logic to cancel the session
            if (confirm('Are you sure you want to cancel this session?')) {
                // Perform cancellation
            }
        }
    </script>
</body>
</html> 