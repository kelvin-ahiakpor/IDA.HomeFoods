document.addEventListener("DOMContentLoaded", () => {
    const signupForm = document.getElementById("signupForm");
    const validationMessage = document.getElementById("validationMessage");
    const validationMessageText = document.getElementById("validationMessageText");
    const togglePasswordBtn = document.getElementById("togglePasswordBtn");
    const togglePasswordConfirmBtn = document.getElementById("togglePasswordConfirmBtn");
    const passwordInput = document.getElementById("password");
    const passwordConfirmInput = document.getElementById("password-confirm");

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
    setupPasswordToggle(togglePasswordConfirmBtn, passwordConfirmInput);

    function showValidationMessage(messages) {
        const validationMessageText = document.getElementById("validationMessageText");
        const messageContainer = document.getElementById("validationMessage");
    
        // Clear previous content
        validationMessageText.innerHTML = "";
    
        // Ensure messages are an array and limit to 3
        const messageArray = Array.isArray(messages) ? messages.slice(0, 3) : [messages];
    
        // Add messages as bullet points
        messageArray.forEach((message) => {
            const listItem = document.createElement("li");
            listItem.textContent = message;
            validationMessageText.appendChild(listItem);
        });
    
        // Show the container
        messageContainer.classList.remove("hidden");
    }
        
    function validateForm() {
        const firstName = document.getElementById("first_name").value.trim();
        const lastName = document.getElementById("last_name").value.trim();
        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value;
        const passwordConfirm = document.getElementById("password-confirm").value;

        const errors = [];

        // Validate First and Last Name
        if (!firstName || !lastName) {
            errors.push("All fields are required");
        }

        // Validate Email
        const emailRegex = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
        if (!emailRegex.test(email)) {
            errors.push("Please enter a valid email address");
        }

        // Validate Password
        const passwordRegex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{8,}$/;
        if (!passwordRegex.test(password)) {
            errors.push(
                "Password must be at least 8 characters with at least 3 digits, an uppercase, and a special character"
            );
        } else if (password !== passwordConfirm) {
            errors.push("Passwords do not match");
        }

        return errors;
    }

    signupForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const errors = validateForm();

        if (errors.length > 0) {
            showValidationMessage(errors);
            return;
        }

        // If validation passes, proceed with form submission
        const formData = new FormData(signupForm);

        try {
            const response = await fetch("../actions/registerUser.php", {
                method: "POST",
                body: formData,
            });

            const result = await response.json();

            if (result.success) {
                // Clear form
                signupForm.reset();

                // Show success message
                validationMessage.classList.remove(
                    "bg-red-100",
                    "border-red-400",
                    "text-red-700"
                );
                validationMessage.classList.add(
                    "bg-green-100",
                    "border-green-400",
                    "text-green-700"
                );
                showValidationMessage([result.message]);

                // Optional: Redirect after successful registration
                setTimeout(() => {
                    window.location.href = "login.php";
                }, 2000);
            } else if (result.errors) {
                // Extract error messages
                const messages = result.errors.map((error) => error.message);
                showValidationMessage(messages); // Display errors
            } else {
                showValidationMessage(result.message);
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