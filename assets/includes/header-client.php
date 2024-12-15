<header class="bg-white shadow-sm fixed w-full z-10">
    <div class="max-w-full mx-auto">
        <div class="flex items-center justify-between px-4 sm:px-6 py-3">
            <div class="flex items-center space-x-2 sm:space-x-4">
                <button class="lg:hidden p-2 hover:bg-gray-100 rounded-lg" id="menuBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <img src="../../assets/images/IDAFU-logo-min-black.png" alt="IDAFU Logo" class="h-6 sm:h-9">
            </div>
            <nav class="hidden lg:flex items-center">
                <div class="flex space-x-6 md:space-x-12">
                    <a href="./dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?> text-sm md:text-base">Dashboard</a>
                    <a href="./explore_consultants.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'explore_consultants.php' ? 'active' : ''; ?> text-sm md:text-base">Consultants</a>
                    <a href="./bookings.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'bookings.php' ? 'active' : ''; ?> text-sm md:text-base">Bookings</a>
                    <a href="./view_reports.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?> text-sm md:text-base">Reports</a>
                </div>
            </nav>
            <div class="flex items-center">
                <button class="relative group p-2 hover:bg-gray-100 rounded-full" id="profileBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" sm:width="24" sm:height="24" fill="currentColor" class="text-gray-600" viewBox="0 0 16 16">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</header>
