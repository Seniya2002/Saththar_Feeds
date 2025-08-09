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

$result = $conn->query('SELECT id, username, email, first_name, last_name, phone_number, address FROM users');
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);
$conn->close();
?>