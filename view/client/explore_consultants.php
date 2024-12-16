<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Client');

// Fetch consultants from the database
function fetchConsultants() {
    global $conn;
    $query = "SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email, u.profile_picture,
                     c.expertise, c.rating, c.hourly_rate,
                     c.status, c.bio
              FROM ida_users u
              JOIN ida_consultants c ON u.user_id = c.consultant_id
              JOIN ida_availability a ON c.consultant_id = a.consultant_id
              LEFT JOIN ida_bookings b ON (
                  b.consultant_id = c.consultant_id 
                  AND b.booking_date = a.date 
                  AND b.time_slot = a.start_time
                  AND b.status = 'Approved'
              )
              WHERE c.status = 'Active'
              AND a.date >= CURDATE()
              AND b.booking_id IS NULL
              ORDER BY c.rating DESC";
              
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function formatExpertise($expertise) {
    if (empty($expertise)) return 'General Consultant';
    
    $areas = json_decode($expertise, true);
    if (!is_array($areas)) return 'General Consultant';
    
    // Capitalize each area
    $areas = array_map(function($area) {
        return ucfirst(str_replace('_', ' ', $area));
    }, $areas);
    
    if (count($areas) === 1) {
        return $areas[0];
    } else {
        // Take first two areas only
        $firstTwo = array_slice($areas, 0, 2);
        return implode(' & ', $firstTwo);
    }
}

function getInitials($firstName, $lastName) {
    return strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
}

$consultants = fetchConsultants();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('../../assets/includes/head-dashboard.php'); ?>
    <title>Explore Consultants | idafü</title>
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
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6">Explore Consultants</h1>

                <!-- Consultants Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($consultants as $consultant): ?>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <?php if ($consultant['profile_picture']): ?>
                                <img src="<?php echo htmlspecialchars($consultant['profile_picture']); ?>" 
                                     alt="Profile" 
                                     class="w-full h-48 object-cover rounded-lg mb-4">
                            <?php else: ?>
                                <div class="w-full h-48 bg-idafu-primary rounded-lg mb-4 flex items-center justify-center text-white text-3xl font-semibold">
                                    <?php echo getInitials($consultant['first_name'], $consultant['last_name']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex justify-between items-start">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($consultant['first_name'] . ' ' . $consultant['last_name']); ?>
                                    </h2>
                                    <p class="text-sm text-gray-600">
                                        <?php echo htmlspecialchars(formatExpertise($consultant['expertise'])); ?>
                                    </p>
                                </div>
                                <div class="flex items-center ml-auto">
                                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span class="ml-1 text-sm font-medium text-gray-600">
                                        <?php echo number_format($consultant['rating'] ?? 0, 1); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 text-sm text-gray-600">
                                <span class="font-medium">$<?php echo number_format($consultant['hourly_rate'] ?? 0, 2); ?>/hour</span>
                            </div>

                            <div class="mt-6">
                                <button onclick="window.location.href='book_consultation.php?consultant_id=<?php echo $consultant['user_id']; ?>'"
                                        class="w-full px-4 py-2 bg-idafu-primary text-white rounded hover:bg-idafu-primaryDarker transition-colors duration-200">
                                    Book Consultation
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($consultants)): ?>
                    <div class="text-center py-12">
                        <p class="text-gray-600">No consultants available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <!-- Footer -->
        <?php include('../../assets/includes/footer-dashboard.php'); ?>
    </div>

    <script src="../../assets/js/script-dashboard.js" defer></script>
</body>

</html>


