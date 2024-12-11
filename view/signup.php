<!DOCTYPE html>
<html lang="en">
<head>
<?php include('../assets/includes/head-auth.php'); ?>
    <title>Sign Up | idafü</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="min-h-screen flex items-center justify-center p-5">
    <div class="max-w-xl w-full bg-transparent">
        <h1 class="text-5xl mb-12 text-custom text-center custom-font">Sign Up</h1>
        
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

        <form id="signupForm" class="space-y-8" novalidate>
            <!-- First Row: First Name and Last Name side by side -->
            <div class="flex gap-4">
                <div class="flex-1">
                    <label for="first_name" class="block text-custom text-sm mb-2">First Name*</label>
                    <input type="text" name="first_name" id="first_name" placeholder="Enter your first name" class="w-full px-4 py-3 rounded-full bg-white focus:outline-none focus:ring-2 focus:ring-custom-text" >
                </div>
                <div class="flex-1">
                    <label for="last_name" class="block text-custom text-sm mb-2">Last Name*</label>
                    <input type="text" name="last_name" id="last_name" placeholder="Enter your last name" class="w-full px-4 py-3 rounded-full bg-white focus:outline-none focus:ring-2 focus:ring-custom-text" >
                </div>
            </div>

            <!-- Second Row: Email -->
            <div>
                <label for="email" class="block text-custom text-sm mb-2">Email*</label>
                <input type="email" name="email" id="email" placeholder="Enter your email" class="w-full px-4 py-3 rounded-full bg-white focus:outline-none focus:ring-2 focus:ring-custom-text" >
            </div>

            <!-- Third Row: Password and Confirm Password -->
            <div class="flex gap-4">
                <div class="flex-1">
                    <label for="password" class="block text-custom text-sm mb-2">Password*</label>
                    <input type="password" name="password" id="password" placeholder="Create a password" class="w-full px-4 py-3 rounded-full bg-white focus:outline-none focus:ring-2 focus:ring-custom-text" >
                </div>
                <div class="flex-1">
                    <label for="password-confirm" class="block text-custom text-sm mb-2">Confirm Password*</label>
                    <input type="password" name="password-confirm" id="password-confirm" placeholder="Confirm your password" class="w-full px-4 py-3 rounded-full bg-white focus:outline-none focus:ring-2 focus:ring-custom-text" >
                </div>
            </div>

            <!-- Marketing Checkbox -->
            <div class="flex items-center">
                <input type="checkbox" name="marketing" class="h-4 w-4 text-custom-text focus:ring-custom-text" id="marketing">
                <label for="marketing" class="ml-2 text-custom text-sm">
                    I agree to receive emails from idafü!
                </label>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-center">
                <button type="submit" class="w-40 bg-custom-text text-custom-bg px-6 py-3 rounded-full hover:opacity-90 transition-opacity">
                    SUBMIT
                </button>
            </div>

            <!-- Login instead -->
            <p class="mt-6 text-sm text-custom text-center px-4">
                Already have an account? <a href="login.php" class="underline">Login instead</a>.
            </p>
            
        </form>
    </div>

    <script src="../assets/js/script-signup.js" defer></script>
</body>
</html>
