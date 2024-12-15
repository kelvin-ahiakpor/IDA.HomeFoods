<header class="bg-white shadow-sm fixed w-full z-10">
    <div class="max-w-full mx-auto">
        <div class="flex items-center justify-between px-4 sm:px-6 py-3">
            <!-- Left side: Logo and mobile menu -->
            <div class="flex items-center space-x-2 sm:space-x-4">
                <button class="lg:hidden p-2 hover:bg-gray-100 rounded-lg" id="menuBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <img src="../../assets/images/IDAFU-logo-min-black.png" alt="IDAFU Logo" class="h-6 sm:h-9">
            </div>

            <!-- Center: Navigation -->
            <nav class="hidden lg:flex items-center">
                <div class="flex space-x-6 md:space-x-12">
                    <a href="./dashboard.php" 
                       class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?> text-sm md:text-base">
                        Dashboard
                    </a>
                    <a href="./manage_sessions.php" 
                       class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'manage_sessions.php' ? 'active' : ''; ?> text-sm md:text-base">
                        Sessions
                    </a>
                    <a href="./manage_availability.php" 
                       class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'manage_availability.php' ? 'active' : ''; ?> text-sm md:text-base">
                        Availability
                    </a>
                    <a href="./view_earnings.php" 
                       class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'view_earnings.php' ? 'active' : ''; ?> text-sm md:text-base">
                        Earnings
                    </a>
                    <a href="./consultant_profile.php" 
                       class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'consultant_profile.php' ? 'active' : ''; ?> text-sm md:text-base">
                        Profile
                    </a>
                </div>
            </nav>

            <!-- Right side: Notifications and Profile -->
            <div class="flex items-center space-x-2 sm:space-x-4">
                <!-- Notifications -->
                <!-- <button class="p-2 hover:bg-gray-100 rounded-full relative">
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </button> -->

                <!-- Profile Button -->
                <button class="relative group p-2 hover:bg-gray-100 rounded-full flex items-center space-x-2" id="profileBtn">
                    <?php if (!empty($_SESSION['profile_picture'])): ?>
                        <img src="<?php echo $_SESSION['profile_picture']; ?>" 
                             alt="Profile" 
                             class="w-8 h-8 rounded-full object-cover">
                    <?php else: ?>
                        <div class="w-8 h-8 rounded-full bg-idafu-primary text-white flex items-center justify-center text-sm">
                            <?php echo substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1); ?>
                        </div>
                    <?php endif; ?>
                    <span class="hidden md:block text-sm text-gray-700">
                        <?php echo $_SESSION['first_name']; ?>
                    </span>
                </button>
            </div>
        </div>
    </div>
</header> 