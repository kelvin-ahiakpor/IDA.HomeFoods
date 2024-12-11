<?php
session_start();
require '../db/config.php';

header('Content-Type: application/json');

$response = [];
$errors = [];

// Default role for new users
$role = 'Client'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input data
    $firstName = htmlspecialchars(trim($_POST["first_name"]), ENT_QUOTES, 'UTF-8');
    $lastName = htmlspecialchars(trim($_POST["last_name"]), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST["password"]);
    $passwordConfirm = trim($_POST["password-confirm"]);
    $marketingOptIn = isset($_POST["marketing"]) ? 1 : 0;

    // Validation
    if (empty($firstName)) {
        $errors[] = ["field" => "first_name", "message" => "First name is required."];
    } elseif (strlen($firstName) < 2) {
        $errors[] = ["field" => "first_name", "message" => "First name must be at least 2 characters long."];
    }

    if (empty($lastName)) {
        $errors[] = ["field" => "last_name", "message" => "Last name is required."];
    } elseif (strlen($lastName) < 2) {
        $errors[] = ["field" => "last_name", "message" => "Last name must be at least 2 characters long."];
    }

    if (empty($email)) {
        $errors[] = ["field" => "email", "message" => "Email is required."];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = ["field" => "email", "message" => "Invalid email format."];
    }

    $passwordRegex = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{8,}$/";
    if (empty($password)) {
        $errors[] = ["field" => "password", "message" => "Password is required."];
    } elseif (!preg_match($passwordRegex, $password)) {
        $errors[] = ["field" => "password", "message" => "Password must be at least 8 characters, include an uppercase letter, 3 digits, and a special character."];
    }

    if (empty($passwordConfirm)) {
        $errors[] = ["field" => "password-confirm", "message" => "Password confirmation is required."];
    } elseif ($password !== $passwordConfirm) {
        $errors[] = ["field" => "password-confirm", "message" => "Passwords do not match."];
    }

    // Check for duplicate email
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM ida_users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = ["field" => "email", "message" => "An account with this email already exists."];
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert user into the database
            try {
                $stmt = $conn->prepare(
                    "INSERT INTO ida_users (first_name, last_name, email, password, marketing_opt_in, role, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())"
                );
                $stmt->execute([$firstName, $lastName, $email, $hashedPassword, $marketingOptIn, $role]);

                // Get the inserted user ID
                $userId = $conn->insert_id;

                // Send Welcome Email
                sendWelcomeEmail($email, $firstName, $userId, $conn);

                $response['success'] = true;
                $response['message'] = "Account created successfully! A welcome email has been sent.";
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                $errors[] = ["field" => "general", "message" => "Error creating account. Please try again later."];
            }
        }
    }

    // Close the statement
    $stmt = null;
}

// Send error messages if any
if (!empty($errors)) {
    $response['success'] = false;
    $response['errors'] = $errors;
}

echo json_encode($response);

/**
 * Send a welcome email and log the activity in the database.
 *
 * @param string $email
 * @param string $firstName
 * @param int $userId
 * @param PDO $conn
 * @return void
 */
function sendWelcomeEmail($email, $firstName, $userId, $conn) {
    $subject = "Welcome to Idafü!";
    $message = "
        <!DOCTYPE html>
        <html>
        <head>
        <style>
            /* Import the Superclarendon font */
            @import url('https://use.typekit.net/yoa3zvz.css');

            h1 {
            font-family: superclarendon, serif;
            font-weight: 400;
            font-style: normal;
            color: #67A0B9; /* Use Idafü's brand color */
            font-size: 2em; 
            margin: 0 0 1rem;
            }
        </style>
        <title>Welcome to Idafü!</title>

        </head>
        <body>
            <h1>Welcome to Idafü!</h1>
            <h2>Hello, $firstName!</h2>
            <p>Thank you for signing up for Idafü! We're thrilled to have you on board.</p>
            <p>Feel free to explore our platform and enjoy all the amazing features.</p>
            <p>If you have any questions, feel free to reach out to our support team.</p>
            <br>
            <p>Warm regards,</p>
            <p>The Idafü Team</p>
        </body>
        </html>
    ";

    // Headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@idafu.com" . "\r\n";

    // Send the email
    if (mail($email, $subject, $message, $headers)) {
        try {
            // Log the email in the database
            $stmt = $conn->prepare("INSERT INTO ida_emails (user_id, email_subject, email_body, sent_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$userId, $subject, $message]);
        } catch (PDOException $e) {
            error_log("Failed to log email: " . $e->getMessage());
        }
    } else {
        error_log("Failed to send email to $email");
    }
}