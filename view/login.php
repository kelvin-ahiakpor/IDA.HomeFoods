<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('../assets/includes/head-auth.php'); ?>
    <title>Log In | idafü</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="min-h-screen flex items-center justify-center p-5">
    <div class="max-w-xl w-full bg-transparent">
        <h1 class="text-5xl mb-12 text-custom text-center custom-font">Log In</h1>

        <!-- Container for validation messages -->
        <div id="validationMessage" class="hidden bg-[#4c4c4c] border border-red-400 text-red-500 px-6 py-4 rounded-lg mb-6" role="alert">
            <ul class="list-disc pl-4 space-y-1">
                <ul id="validationMessageText" class="list-disc pl-4 space-y-1"></ul>
            </ul>
            <span class="absolute top-3 right-3">
                <svg onclick="closeValidationMessage()" class="fill-current h-5 w-5 text-red-500 cursor-pointer hover:text-red-700" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M14.95 5.05a.75.75 0 011.06 1.06L11.06 10l4.95 4.95a.75.75 0 11-1.06 1.06L10 11.06l-4.95 4.95a.75.75 0 01-1.06-1.06L8.94 10 4.05 5.05a.75.75 0 011.06-1.06L10 8.94l4.95-4.95z"></path>
                </svg>
            </span>
        </div>

        <form id="loginForm" class="space-y-8" novalidate>
            <!-- Email Field -->
            <div>
                <label for="email" class="block text-custom text-sm mb-2">Email*</label>
                <input type="email" name="email" id="email" placeholder="Enter your email" class="w-full px-4 py-3 rounded-full bg-white focus:outline-none focus:ring-2 focus:ring-custom-text" >
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-custom text-sm mb-2">Password*</label>
                <input type="password" name="password" id="password" placeholder="Enter your password" class="w-full px-4 py-3 rounded-full bg-white focus:outline-none focus:ring-2 focus:ring-custom-text" >
            </div>

            <!-- Submit Button -->
            <div class="flex justify-center">
                <button type="submit" class="w-40 bg-custom-text text-custom-bg px-6 py-3 rounded-full hover:opacity-90 transition-opacity">
                    LOGIN
                </button>
            </div>

            <!-- Signup Link -->
            <p class="mt-6 text-sm text-custom text-center px-4">
                Don't have an account? <a href="signup.php" class="underline">Sign up here</a>.
            </p>
        </form>
    </div>

    <script src="../assets/js/script-login.js" defer></script>
</body>
</html>
