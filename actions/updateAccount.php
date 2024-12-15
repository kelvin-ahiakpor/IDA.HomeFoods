<?php
session_start();
require '../db/config.php';

header('Content-Type: application/json');

$response = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($firstName) || empty($lastName)) {
        $response = [
            "success" => false,
            "message" => "First name and last name are required."
        ];
    } else {
        try {
            $stmt = $conn->prepare("UPDATE ida_users SET first_name = ?, last_name = ?, phone = ?, address = ? WHERE user_id = ?");
            $stmt->bind_param("ssssi", $firstName, $lastName, $phone, $address, $_SESSION['user_id']);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $_SESSION['first_name'] = $firstName;
                $_SESSION['last_name'] = $lastName;
                $_SESSION['phone'] = $phone;
                $_SESSION['address'] = $address;

                $response = [
                    "success" => true,
                    "noChanges" => false,
                    "message" => "Account updated successfully."
                ];
            } else {
                $response = [
                    "success" => true,
                    "noChanges" => true,
                    "message" => "No changes made to the account."
                ];
            }

            $stmt->close();
        } catch (Exception $e) {
            error_log("Update error: " . $e->getMessage());
            $response = [
                "success" => false,
                "message" => "An error occurred. Please try again later."
            ];
        }
    }
}

echo json_encode($response); 