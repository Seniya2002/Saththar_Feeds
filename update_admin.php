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
$username = $input['username'];
$email = $input['email'];
$password = $input['password'] ? password_hash($input['password'], PASSWORD_BCRYPT) : null;

if ($password) {
    $stmt = $conn->prepare('UPDATE admins SET username = ?, email = ?, password = ? WHERE id = ?');
    $stmt->bind_param('sssi', $username, $email, $password, $_SESSION['admin_id']);
} else {
    $stmt = $conn->prepare('UPDATE admins SET username = ?, email = ? WHERE id = ?');
    $stmt->bind_param('ssi', $username, $email, $_SESSION['admin_id']);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
}

$stmt->close();
$conn->close();
?>