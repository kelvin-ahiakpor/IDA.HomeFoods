<?php
require_once '../db/config.php';
require_once '../functions/session_check.php';

$projectRoot = '/~kelvin.ahiakpor/IDA_HOME_FOODS';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('../assets/includes/head-dashboard.php'); ?>
    <title>Manage Account | idafü</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<body class="bg-gray-50 font-sans antialiased">
    <div class="flex flex-col min-h-screen">
        <!-- Logo -->
        <div class="flex items-center justify-center py-6">
            <img src="../assets/images/IDAFU-logo-green.png" alt="IDAFU Logo" class="h-20 w-auto">
        </div>

        <!-- Back to Dashboard Link -->
        <div class="max-w-3xl mx-auto px-4 mb-4">
            <a href="<?php 
                            if ($_SESSION['role'] === 'Admin') {
                                echo "{$projectRoot}/view/admin/dashboard.php";
                            } elseif ($_SESSION['role'] === 'Consultant') {
                                echo "{$projectRoot}/view/consultant/dashboard.php";
                            } elseif ($_SESSION['role'] === 'Client') {
                                echo "{$projectRoot}/view/client/dashboard.php";
                            }
            ?>" class="inline-flex items-center text-idafu-primary hover:text-idafu-primaryDarker">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Dashboard
            </a>
        </div>

        <!-- Main Content -->
        <main class="flex-grow pt-8 px-4 sm:px-6">
            <div class="max-w-3xl mx-auto py-2 sm:py-4">
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6">Manage Account</h1>

                <form id="profileForm" class="space-y-6">
                    <div>
                        <label for="firstName" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($_SESSION['first_name']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0" autocomplete="given-name">
                    </div>
                    <div>
                        <label for="lastName" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($_SESSION['last_name']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0" autocomplete="family-name">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0" readonly autocomplete="email">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($_SESSION['phone']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0" autocomplete="tel">
                    </div>
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($_SESSION['address']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0" autocomplete="street-address">
                    </div>
                    <div class="flex justify-between items-center">
                        <a href="./client/become_consultant.php" class="text-idafu-primary hover:text-idafu-primaryDarker">
                            Become a Consultant →
                        </a>
                        <button type="submit" class="px-4 py-2 bg-idafu-primary text-white rounded hover:bg-idafu-primaryDarker transition-colors duration-200">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </main>

        <!-- Footer -->
        <?php include('../assets/includes/footer-dashboard.php'); ?>
    </div>

    <script src="../assets/js/script-manage-account.js" defer></script>
</body>
</html>