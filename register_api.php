<?php
header('Content-Type: application/json');
include 'db_config.php'; // replace with your actual DB connection file

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $response = ["status" => "error", "message" => "All fields are required"];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = ["status" => "error", "message" => "Invalid email format"];
    } elseif ($password !== $confirm_password) {
        $response = ["status" => "error", "message" => "Passwords do not match"];
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $response = ["status" => "error", "message" => "Email already registered"];
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            if ($stmt->execute()) {
                $response = ["status" => "success", "message" => "Registration successful"];
            } else {
                $response = ["status" => "error", "message" => "Registration failed"];
            }
        }
    }
} else {
    $response = ["status" => "error", "message" => "Invalid request method"];
}

echo json_encode($response);
    