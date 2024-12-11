document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");
    const messageBox = document.getElementById("validationMessage");
    const messageText = document.getElementById("validationMessageText");

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
