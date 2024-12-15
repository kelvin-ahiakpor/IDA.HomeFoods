<div id="mobileMenu" class="fixed inset-0 bg-gray-800 bg-opacity-50 z-50 hidden lg:hidden">
    <div class="bg-white w-64 h-full transform transition-transform duration-300 -translate-x-full" id="mobileMenuContent">
        <div class="p-4 border-b"> <img src="../../assets/images/IDAFU-logo-min-black.png" alt="IDAFU Logo" class="h-8"> </div>
        <nav class="p-4">
            <ul class="space-y-2">
                <li> <a href="./dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100"> <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg> <span><?php echo ($_SESSION['role'] === 'Admin') ? 'Overview' : 'Dashboard'; ?></span> </a> </li>
                <li> <a href="<?php echo ($_SESSION['role'] === 'Admin') ? './manage_consultants.php' : './explore_consultants.php'; ?>" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100"> <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg> <span>Consultants</span> </a> </li>
                <li> <a href="<?php echo ($_SESSION['role'] === 'Admin') ? './manage_clients.php' : './bookings.php'; ?>" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100"> <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg> <span><?php echo ($_SESSION['role'] === 'Admin') ? 'Clients' : 'Bookings'; ?></span> </a> </li>
                <li> <a href="./view_reports.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100"> <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg> <span>Reports</span> </a> </li>
            </ul>
        </nav>
    </div>
</div>