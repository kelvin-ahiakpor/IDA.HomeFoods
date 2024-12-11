document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");
    const messageBox = document.getElementById("validationMessage");
    const messageText = document.getElementById("validationMessageText");

    const togglePasswordBtn = document.getElementById("togglePasswordBtn");
    const passwordInput = document.getElementById("password");

    // SVG Icons
    const icons = {
        eyeOpen: `
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>`,
        eyeClosed: `
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M10.5 10.677a2 2 0 002.823 2.823M7.362 7.561A7.714 7.714 0 0012 7c4.478 0 8.268 2.943 9.542 7a7.714 7.714 0 01-1.904 3.439M9.88 9.88a7.714 7.714 0 00-7.422 2.12C2.732 7.943 6.523 5 12 5c4.478 0 8.268 2.943 9.542 7"/>
            </svg>`
    };

    // Password Toggle Functionality
    function setupPasswordToggle(toggleBtn, input) {
        if (toggleBtn && input) {
        toggleBtn.innerHTML = icons.eyeOpen;
        input.type = "password";

        toggleBtn.addEventListener("click", function () {
            if (input.type === "password") {
            input.type = "text";
            toggleBtn.innerHTML = icons.eyeClosed;
            } else {
            input.type = "password";
            toggleBtn.innerHTML = icons.eyeOpen;
            }
        });
        }
    }

    // Set up password toggles
    setupPasswordToggle(togglePasswordBtn, passwordInput);

    function validateLoginForm() {
        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value.trim();
        const errors = [];

        // Check if email is empty or invalid
        if (!email) {
            errors.push("Email is required.");
        } 

        // Check if password is empty
        if (!password) {
            errors.push("Password is required.");
        }

        return errors;
    }

    function showValidationMessage(messages) {
        // Clear any previous messages
        messageText.innerHTML = "";

        // Display messages as bullet points
        messages.forEach((message) => {
            const listItem = document.createElement("li");
            listItem.textContent = message;
            messageText.appendChild(listItem);
        });

        // Show the container
        messageBox.classList.remove("hidden");
    }

    function hideValidationMessage() {
        messageBox.classList.add("hidden");
        messageText.innerHTML = "";
    }

    loginForm.addEventListener("submit", async (e) => {
        e.preventDefault(); // Prevent the form from refreshing the page

        // Validate the form
        const errors = validateLoginForm();

        if (errors.length > 0) {
            // Show validation errors
            showValidationMessage(errors);
            return;
        }

        // Hide any previous validation messages
        hideValidationMessage();

        // Collect form data
        const formData = new FormData(loginForm);

        try {
            // Make AJAX POST request to loginUser.php
            const response = await fetch("../actions/loginUser.php", {
                method: "POST",
                body: formData,
            });

            const result = await response.json();

            // Display success or error message
            if (result.success) {
                window.location.href = "../view/dashboard.php"; // Redirect to dashboard
            } else if (result.errors) {
                // Extract error messages
                const messages = result.errors.map((error) => error.message);
                showValidationMessage(messages); // Display errors
            } else {
                showValidationMessage([result.message]); // Show error message
            }
        } catch (error) {
            showValidationMessage(["An error occurred. Please try again."]);
        }
    });
});

// Close validation message
function closeValidationMessage() {
    const messageContainer = document.getElementById("validationMessage");
    messageContainer.classList.add("hidden");
    document.getElementById("validationMessageText").innerHTML = ""; // Clear messages
}