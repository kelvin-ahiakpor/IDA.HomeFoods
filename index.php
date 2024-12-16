<?php
require_once 'db/config.php';
require_once 'utils/format_utils.php';

// Fetch featured consultants
$query = "SELECT 
    u.first_name,
    u.last_name,
    c.expertise,
    c.bio,
    c.hourly_rate,
    COALESCE(ROUND(AVG(sr.rating), 1), 0) as avg_rating,
    COUNT(DISTINCT b.booking_id) as total_sessions
FROM ida_users u
JOIN ida_consultants c ON u.user_id = c.consultant_id
LEFT JOIN ida_bookings b ON c.consultant_id = b.consultant_id
LEFT JOIN ida_session_ratings sr ON b.booking_id = sr.booking_id
WHERE c.status = 'Active'
GROUP BY u.user_id
ORDER BY avg_rating DESC, total_sessions DESC
LIMIT 3";

$result = $conn->query($query);
$featured_consultants = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('assets/includes/head-auth.php'); ?>
    <title>idafü - Health & Wellness Consulting</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <style>
        .hero-gradient {
            background-color: #3e6576;
            color: #F2E2B2;
        }
        
        .consultant-card {
            transition: transform 0.3s ease;
        }
        
        .consultant-card:hover {
            transform: translateY(-5px);
        }

        .custom-section {
            background-color: #F2E2B2;
        }
    </style>
</head>

<body class="bg-white">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <img class="h-12 w-auto" src="assets/images/IDAFU-logo-green.png" alt="idafü">
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="view/login.php" class="text-custom-bg hover:text-opacity-80 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                    <a href="view/signup.php" class="bg-custom-bg text-custom-text hover:bg-opacity-90 px-4 py-2 rounded-md text-sm font-medium">Get Started</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-gradient">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="custom-font text-4xl md:text-6xl text-custom mb-6">
                    Your Journey to Wellness Starts Here
                </h1>
                <p class="text-xl md:text-2xl text-custom opacity-90 mb-12 max-w-3xl mx-auto">
                    Connect with expert health and wellness consultants for personalized guidance and transformative results
                </p>
                <a href="view/signup.php" class="inline-block bg-custom-text text-custom-bg hover:bg-opacity-90 text-lg px-8 py-4 rounded-lg shadow-lg transition duration-300">
                    Begin Your Wellness Journey
                </a>
            </div>
        </div>
    </div>

    <!-- Featured Consultants -->
    <div class="custom-section py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="custom-font text-3xl md:text-4xl text-center text-custom-bg mb-16">
                Meet Our Wellness Experts
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($featured_consultants as $consultant): ?>
                    <div class="consultant-card bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="p-6">
                            <div class="text-center mb-4">
                                <div class="w-24 h-24 bg-custom-bg rounded-full mx-auto mb-4 flex items-center justify-center">
                                    <span class="text-2xl custom-font text-custom">
                                        <?php echo substr($consultant['first_name'], 0, 1) . substr($consultant['last_name'], 0, 1); ?>
                                    </span>
                                </div>
                                <h3 class="custom-font text-xl text-custom-bg">
                                    <?php echo htmlspecialchars($consultant['first_name'] . ' ' . $consultant['last_name']); ?>
                                </h3>
                                <p class="text-custom-bg font-medium">
                                    <?php echo htmlspecialchars(formatExpertise($consultant['expertise'])); ?>
                                </p>
                            </div>
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-custom-bg">
                                    <?php echo $consultant['avg_rating']; ?> ★
                                </span>
                                <span class="text-custom-bg">
                                    <?php echo $consultant['total_sessions']; ?> sessions
                                </span>
                            </div>
                            <p class="text-gray-600 mb-6 line-clamp-3">
                                <?php echo htmlspecialchars($consultant['bio']); ?>
                            </p>
                            <div class="text-center">
                                <a href="view/signup.php" class="inline-block bg-custom-bg text-custom hover:bg-opacity-90 px-6 py-2 rounded-lg transition duration-300">
                                    Book Consultation
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Why Choose Us -->
    <div class="bg-white py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="custom-font text-3xl md:text-4xl text-center text-custom-bg mb-16">
                Why Choose idafü Wellness
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div class="text-center">
                    <div class="bg-custom-text w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-custom-bg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                    </div>
                    <h3 class="custom-font text-xl text-custom-bg mb-4">Expert Wellness Consultants</h3>
                    <p class="text-gray-600">Certified professionals dedicated to your health journey</p>
                </div>
                <div class="text-center">
                    <div class="bg-custom-text w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-custom-bg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                        </svg>
                    </div>
                    <h3 class="custom-font text-xl text-custom-bg mb-4">Personalized Approach</h3>
                    <p class="text-gray-600">Tailored wellness plans that fit your lifestyle and goals</p>
                </div>
                <div class="text-center">
                    <div class="bg-custom-text w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-custom-bg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/>
                            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/>
                        </svg>
                    </div>
                    <h3 class="custom-font text-xl text-custom-bg mb-4">Holistic Wellness</h3>
                    <p class="text-gray-600">Comprehensive approach to health and well-being</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="hero-gradient">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center">
                <h2 class="custom-font text-3xl md:text-4xl text-custom mb-6">
                    Ready to Transform Your Health?
                </h2>
                <p class="text-xl text-custom opacity-90 mb-8 max-w-2xl mx-auto">
                    Take the first step towards a healthier, more balanced life with idafü's wellness experts
                </p>
                <a href="view/signup.php" class="inline-block bg-custom-text text-custom-bg hover:bg-opacity-90 text-lg px-8 py-4 rounded-lg shadow-lg transition duration-300">
                    Start Your Journey
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include('assets/includes/footer.php'); ?>
</body>
</html>
