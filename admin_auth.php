<?php
session_start();

// Initialize error logging
$log_file = 'auth_errors.log';
function log_error($message) {
    global $log_file;
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'saththar_feeds_db');
if ($conn->connect_error) {
    log_error('Database connection failed: ' . $conn->connect_error);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
$username = isset($input['username']) ? trim($input['username']) : '';
$password = isset($input['password']) ? trim($input['password']) : '';

if (empty($username) || empty($password)) {
    log_error('Empty username or password: username=' . $username);
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    $conn->close();
    exit;
}

// Prepare statement to prevent SQL injection
$stmt = $conn->prepare('SELECT password FROM admins WHERE username = ?');
if (!$stmt) {
    log_error('Prepare statement failed: ' . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Server error']);
    $conn->close();
    exit;
}

$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    log_error('No admin found for username: ' . $username);
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    $stmt->close();
    $conn->close();
    exit;
}

$admin = $result->fetch_assoc();
$stmt->close();

if ($password === $admin['password']) {
    $_SESSION['admin_loggedin'] = true;
    $_SESSION['admin_username'] = $username;
    echo json_encode(['success' => true, 'message' => 'Login successful']);
} else {
    log_error('Password mismatch for username: ' . $username . ', provided: ' . $password . ', stored: ' . $admin['password']);
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
}

$conn->close();
?>