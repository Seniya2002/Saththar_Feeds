<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = array('success' => false, 'message' => 'Invalid request.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    $username = isset($input['username']) ? $input['username'] : '';
    $password = isset($input['password']) ? $input['password'] : '';

    if ($username && $password) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password FROM customers WHERE username = ?");
            $stmt->execute(array($username));
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($customer && $password === $customer['password']) { // Replace with password_verify() in production
                $_SESSION['customer_id'] = $customer['id'];
                $response = array('success' => true, 'message' => 'Login successful.');
            } else {
                $response['message'] = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
            error_log("Error in customer_login.php: " . $e->getMessage());
        }
    } else {
        $response['message'] = 'Username and password are required.';
    }
}

echo json_encode($response);
?>