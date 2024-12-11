document.addEventListener("DOMContentLoaded", () => {
    const signupForm = document.getElementById("signupForm");
    const validationMessage = document.getElementById("validationMessage");
    const validationMessageText = document.getElementById("validationMessageText");

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
        const passwordRegex = /^(?=.*[A-Z])(?=.*\d{3,})(?=.*\W).{8,}$/;
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