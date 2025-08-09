<?php
header('Content-Type: application/json');

// Include the correct database connection
require_once 'db_connect.php'; // make sure this sets up $pdo, not $conn

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        if (!isset($pdo) || !$pdo) {
            echo json_encode(["status" => "fail", "message" => "Database connection failed."]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            echo json_encode(["status" => "success", "message" => "Login successful"]);
        } else {
            echo json_encode(["status" => "fail", "message" => "Invalid email or password"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "fail", "message" => "Database error: " . $e->getMessage()]);
    }
}
?>
