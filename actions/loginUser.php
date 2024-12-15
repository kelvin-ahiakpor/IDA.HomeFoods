<?php
session_start();
require '../db/config.php';

header('Content-Type: application/json');

$response = [];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Collect and sanitize input data
    $email = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST["password"] ?? '');

    // Validation
    if (empty($email)) {
        $errors[] = ["field" => "email", "message" => "Email is required."];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = ["field" => "email", "message" => "Please enter a valid email address."];
    }

    if (empty($password)) {
        $errors[] = ["field" => "password", "message" => "Password is required."];
    }

    // If there are no validation errors, proceed
    if (empty($errors)) {
        try {
            // Prepare and execute SQL statement
            $stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, phone, address, password, role FROM ida_users WHERE email = ? AND is_active = 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Verify the password
                if (password_verify($password, $user['password'])) {
                    // Set session variables for the logged-in user
                    $_SESSION["user_id"] = $user["user_id"];
                    $_SESSION["first_name"] = $user["first_name"];
                    $_SESSION["last_name"] = $user["last_name"];
                    $_SESSION["email"] = $user["email"];
                    $_SESSION["phone"] = $user["phone"];
                    $_SESSION["address"] = $user["address"];
                    $_SESSION["role"] = $user["role"];

                    // Determine redirect URL based on user type
                    switch ($user['role']) {
                        case 'Admin':
                            $redirectUrl = "../view/admin/dashboard.php";
                            break;
                        case 'Consultant':
                            $redirectUrl = "../view/consultant/appointments.php";
                            break;
                        case 'Client':
                        default:
                            $redirectUrl = "../view/client/dashboard.php";
                            break;
                    }

                    $response = [
                        "success" => true,
                        "message" => "Login successful!",
                        "redirect" => $redirectUrl
                    ];
                } else {
                    $errors[] = ["field" => "password", "message" => "Invalid credentials."];
                }
            } else {
                $errors[] = ["field" => "email", "message" => "No active account found with this email address."];
            }

            $stmt->close();
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $errors[] = ["field" => "general", "message" => "An error occurred. Please try again later."];
        }
    }
}

// Send response with errors if any
if (!empty($errors)) {
    $response["success"] = false;
    $response["errors"] = $errors;
}

echo json_encode($response);
?>