<!-- Profile Modal -->
<div id="profileModal" class="hidden fixed top-14 right-4 bg-white rounded-lg shadow-lg p-4 w-72 z-20">
        <div class="flex flex-col">
            <div class="flex items-center space-x-3 pb-3">
                <div class="bg-gray-200 rounded-full p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="text-gray-600" viewBox="0 0 16 16">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                    </svg>
                </div>
                <div>
                <h3 class="font-medium"><?php echo htmlspecialchars($_SESSION['first_name']) . ' ' . htmlspecialchars($_SESSION['last_name']); ?></h3>
                    <p class="text-sm text-gray-500">Admin</p>
                </div>
            </div>
            <div class="border-t pt-3">
                <a href="../../view/manage_account.php" class="flex items-center space-x-3 px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                    <span>Account Settings</span>
                </a>
                <a href="../../actions/logout.php" class="flex items-center space-x-3 px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                    <span>Sign Out</span>
                </a>
            </div>
        </div>
    </div>

