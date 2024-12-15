<?php
require_once '../../db/config.php';
require_once '../../functions/session_check.php';
require_once '../../middleware/checkUserAccess.php';
checkUserAccess('Client');

// Get application status if any
$applicationStatus = 'none'; // You'll need to implement the actual status check from your database
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('../../assets/includes/head-dashboard.php'); ?>
    <title>Become a Consultant | idafü</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>

<body class="bg-gray-50 font-sans antialiased">
    <div class="flex flex-col min-h-screen">
        <!-- Logo -->
        <div class="flex items-center justify-center py-6">
            <img src="../../assets/images/IDAFU-logo-green.png" alt="IDAFU Logo" class="h-20 w-auto">
        </div>

        <!-- Back Link -->
        <div class="max-w-3xl mx-auto px-4 mb-4">
            <a href="../manage_account.php" class="inline-flex items-center text-idafu-primary hover:text-idafu-primaryDarker">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Account Management
            </a>
        </div>

        <!-- Main Content -->
        <main class="flex-grow pt-8 px-4 sm:px-6">
            <div class="max-w-3xl mx-auto py-2 sm:py-4">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6">Become a Consultant</h1>

                    <?php if ($applicationStatus === 'none'): ?>
                        <form id="consultantApplicationForm" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Professional Background</label>
                                <textarea 
                                    name="background"
                                    rows="3" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0"
                                    placeholder="Describe your professional experience and background..."
                                    required></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Areas of Expertise</label>
                                <select 
                                    name="expertise[]"
                                    multiple 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0"
                                    required>
                                    <option value="nutrition">Nutrition & Diet Planning</option>
                                    <option value="fitness">Fitness Training</option>
                                    <option value="wellness">Wellness Coaching</option>
                                    <option value="mental_health">Mental Health</option>
                                    <option value="lifestyle">Lifestyle Coaching</option>
                                </select>
                                <p class="mt-1 text-sm text-gray-500">Hold Ctrl/Cmd to select multiple areas</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Certifications</label>
                                <div class="mt-1 space-y-2" id="certificationsContainer">
                                    <div class="certification-input">
                                        <input 
                                            type="text" 
                                            name="certifications[]"
                                            placeholder="Enter certification (e.g., CPT - ACE 2023)" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0"
                                            required>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    onclick="addCertificationField()" 
                                    class="mt-2 text-sm text-idafu-primary hover:text-idafu-primaryDarker">
                                    + Add Another Certification
                                </button>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Proposed Hourly Rate ($)</label>
                                <input 
                                    type="number" 
                                    name="hourlyRate"
                                    min="0" 
                                    step="5" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0"
                                    required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Bio</label>
                                <textarea 
                                    name="bio"
                                    rows="4" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0"
                                    placeholder="Tell potential clients about yourself..."
                                    required></textarea>
                            </div>

                            <div class="flex justify-end">
                                <button 
                                    type="submit" 
                                    class="px-4 py-2 bg-idafu-primary text-white rounded hover:bg-idafu-primaryDarker transition-colors duration-200">
                                    Submit Application
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="bg-yellow-50 border border-yellow-100 rounded-lg p-4">
                            <p class="text-sm text-yellow-800">
                                Your application is currently under review. We'll notify you once a decision has been made.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?php include('../../assets/includes/footer-dashboard.php'); ?>
    </div>

    <script>
        function addCertificationField() {
            const container = document.getElementById('certificationsContainer');
            const newInput = document.createElement('div');
            newInput.innerHTML = `
                <input 
                    type="text" 
                    name="certifications[]"
                    placeholder="Enter certification (e.g., CPT - ACE 2023)" 
                    class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-idafu-primary focus:ring-0"
                    required>
            `;
            container.appendChild(newInput);
        }

        document.getElementById('consultantApplicationForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const formData = new FormData(this);
                const response = await fetch('../../actions/submitConsultantApplication.php', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    alert('Application submitted successfully! Our team will review your application.');
                    window.location.href = 'manage-account.php';
                } else {
                    throw new Error('Application submission failed');
                }
            } catch (error) {
                alert('There was an error submitting your application. Please try again.');
                console.error('Error:', error);
            }
        });
    </script>
</body>
</html>