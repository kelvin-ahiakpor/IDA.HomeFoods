<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Consultant');

// Fetch certifications from the database
function fetchCertifications() {
    global $conn;
    $query = "SELECT * FROM ida_consultant_certifications WHERE consultant_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$certifications = fetchCertifications();

// Handle form submission to add certifications
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the submitted certification data
    if (isset($_POST['new_certification'])) {
        $newCertification = $_POST['new_certification'] ?? '';
        // Logic to save the new certification
    } elseif (isset($_POST['certification'])) {
        $certification = $_POST['certification'] ?? '';
        $file = $_FILES['certification_file'] ?? null;
        // Logic to handle file upload and save certification proof
    }
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

                <!-- Section to Add New Certifications -->
                <form method="POST" class="space-y-4">
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Add New Certification</h2>
                        <input type="text" name="new_certification" placeholder="Enter certification (e.g., CPT - ACE 2023)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0" required>
                        <div class="flex justify-end mt-4">
                            <button type="submit" class="px-4 py-2 bg-idafu-primary text-white rounded hover:bg-idafu-primaryDarker transition-colors duration-200">Add Certification</button>
                        </div>
                    </div>
                </form>

                <!-- Section to Upload Proof for Existing Certifications -->
                <div class="bg-white rounded-lg shadow-sm p-4 mt-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Upload Proof for Existing Certifications</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="certification" class="block text-sm font-medium text-gray-700">Select Certification</label>
                            <select name="certification" id="certification" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0" required>
                                <option value="">Select a certification</option>
                                <?php foreach ($certifications as $cert): ?>
                                    <option value="<?php echo htmlspecialchars($cert['name']); ?>"><?php echo htmlspecialchars($cert['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="certification_file" class="block text-sm font-medium text-gray-700">Upload Proof</label>
                            <input type="file" name="certification_file" id="certification_file" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0" required>
                        </div>
                        <div class="flex justify-end mt-4">
                            <button type="submit" class="px-4 py-2 bg-idafu-primary text-white rounded hover:bg-idafu-primaryDarker transition-colors duration-200">Upload Certification Proof</button>
                        </div>
                    </form>
                </div>

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





