<header class="bg-white shadow-sm fixed w-full z-10">
    <div class="max-w-full mx-auto">
        <div class="flex items-center justify-between px-6 py-3">
            <div class="flex items-center space-x-4">
                <button class="lg:hidden p-2 hover:bg-gray-100 rounded-lg" id="menuBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <img src="../../assets/images/IDAFU-logo-min-black.png" alt="IDAFU Logo" class="h-9">
            </div>
            <nav class="hidden lg:flex items-center">
                <div class="flex space-x-12">
                    <a href="./dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?> text-sm md:text-base">Overview</a>
                    <a href="./manage_consultants.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'manage_consultants.php' ? 'active' : ''; ?> text-sm md:text-base">Consultants</a>
                    <a href="./manage_clients.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'manage_clients.php' ? 'active' : ''; ?> text-sm md:text-base">Clients</a>
                    <a href="./view_reports.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'view_reports.php' ? 'active' : ''; ?> text-sm md:text-base">Reports</a>
                </div>
            </nav>
            <div class="flex items-center">
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
