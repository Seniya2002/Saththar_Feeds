<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || !$_SESSION['admin_loggedin']) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'saththar_feeds_db');
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'];
$username = $input['username'];
$email = $input['email'];
$first_name = $input['first_name'];
$last_name = $input['last_name'];
$phone_number = $input['phone_number'];
$address = $input['address'];

$stmt = $conn->prepare('UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, phone_number = ?, address = ? WHERE id = ?');
$stmt->bind_param('ssssssi', $username, $email, $first_name, $last_name, $phone_number, $address, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update user']);
}

$stmt->close();
$conn->close();
?>