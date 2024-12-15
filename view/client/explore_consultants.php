<?php
require_once '../../db/config.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Client');

// Mock data for demonstration
$consultants = [
    [
        'name' => 'Sarah Johnson',
        'expertise' => 'Nutrition & Diet Planning',
        'rating' => 4.8,
        'reviews' => 124,
        'next_available' => '2024-03-25 14:00:00',
        'hourly_rate' => 75,
        'image' => 'sarah_johnson.jpg',
        'bio' => 'Certified nutritionist with 8+ years of experience in personalized diet planning and wellness coaching.'
    ],
    [
        'name' => 'Mike Wilson',
        'expertise' => 'Fitness Training',
        'rating' => 4.9,
        'reviews' => 89,
        'next_available' => '2024-03-24 10:00:00',
        'hourly_rate' => 65,
        'image' => 'mike_wilson.jpg',
        'bio' => 'Professional fitness trainer specializing in strength training and weight loss programs.'
    ],
    [
        'name' => 'Emma Davis',
        'expertise' => 'Wellness Coaching',
        'rating' => 4.7,
        'reviews' => 156,
        'next_available' => '2024-03-23 15:30:00',
        'hourly_rate' => 70,
        'image' => 'emma_davis.jpg',
        'bio' => 'Holistic wellness coach focusing on mental health, stress management, and work-life balance.'
    ]
];
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
                <!-- Search and Filters -->
                <div class="mb-6 space-y-4">
                    <div class="flex flex-col sm:flex-row justify-between gap-4">
                        <div class="relative flex-grow max-w-2xl">
                            <input type="text" 
                                placeholder="Search consultants by name or expertise..." 
                                class="w-full px-4 py-1.5 pl-10 pr-4 rounded-lg border border-gray-300 focus:outline-none focus:border-idafu-primary text-sm">
                            <svg class="absolute left-3 top-2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <div class="flex gap-2">
                            <select class="px-4 py-1.5 rounded-lg border border-gray-300 focus:outline-none focus:border-idafu-primary text-sm">
                                <option value="">Expertise</option>
                                <option value="nutrition">Nutrition</option>
                                <option value="fitness">Fitness</option>
                                <option value="wellness">Wellness</option>
                            </select>
                            <select class="px-4 py-1.5 rounded-lg border border-gray-300 focus:outline-none focus:border-idafu-primary text-sm">
                                <option value="">Rating</option>
                                <option value="4.5">4.5+</option>
                                <option value="4.0">4.0+</option>
                                <option value="3.5">3.5+</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Consultants Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($consultants as $consultant): ?>
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <!-- Consultant Image/Avatar -->
                            <div class="h-48 bg-idafu-lightBlue relative">
                                <?php if (file_exists("../../assets/images/consultants/{$consultant['image']}")): ?>
                                    <img src="../../assets/images/consultants/<?php echo $consultant['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($consultant['name']); ?>"
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-idafu-primary text-white text-4xl font-semibold">
                                        <?php echo substr($consultant['name'], 0, 2); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Consultant Info -->
                            <div class="p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($consultant['name']); ?></h3>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($consultant['expertise']); ?></p>
                                    </div>
                                    <div class="flex items-center text-sm">
                                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <span class="ml-1 font-medium"><?php echo $consultant['rating']; ?></span>
                                        <span class="ml-1 text-gray-500">(<?php echo $consultant['reviews']; ?>)</span>
                                    </div>
                                </div>

                                <p class="text-sm text-gray-600 mb-4"><?php echo htmlspecialchars($consultant['bio']); ?></p>

                                <div class="flex justify-between items-center text-sm">
                                    <div class="text-gray-600">
                                        <span class="font-medium">$<?php echo $consultant['hourly_rate']; ?></span>/hour
                                    </div>
                                    <div class="text-gray-600">
                                        Next available:<br>
                                        <?php echo date('M d, g:i A', strtotime($consultant['next_available'])); ?>
                                    </div>
                                </div>

                                <button onclick="window.location.href='./book_consultant.php?id=<?php echo urlencode($consultant['name']); ?>'"
                                        class="w-full mt-4 px-4 py-2 bg-idafu-primary text-white rounded hover:bg-idafu-primaryDarker transition-colors duration-200">
                                    Book Consultation
                                </button>
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