<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Consultant');

// Handle form submission to add certifications
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the submitted certification data
    // (This is where you would handle the logic to save the new certifications)
    $certifications = $_POST['certifications'] ?? [];
    // Save certifications logic here
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('../../assets/includes/head-dashboard.php'); ?>
    <title>Consultant Profile | idafü</title>
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
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6">Consultant Profile</h1>

                <form method="POST" id="certificationForm" class="space-y-4">
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Add Certifications</h2>
                        <div id="certificationsContainer">
                            <input type="text" name="certifications[]" placeholder="Enter certification (e.g., CPT - ACE 2023)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0" required>
                        </div>
                        <div class="flex justify-end mt-4">
                            <button type="submit" class="px-4 py-2 bg-idafu-primary text-white rounded hover:bg-idafu-primaryDarker transition-colors duration-200">Add Certification</button>
                        </div>
                    </div>
                </form>

                <!-- Link to Manage Account -->
                <div class="mt-6">
                    <a href="./manage_account.php" class="text-idafu-primary hover:text-idafu-primaryDarker">Manage Account Information →</a>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?php include('../../assets/includes/footer-dashboard.php'); ?>
    </div>

    <script src="../../assets/js/script-dashboard.js" defer></script>
</body>
</html>