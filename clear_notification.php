<?php
session_start();
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

$response = array('success' => false, 'message' => 'Unknown error occurred.');

if (!isset($_SESSION['customer_id'])) {
    $response['message'] = 'Unauthorized access. Please log in.';
    error_log("clear_notification.php: Unauthorized access");
    echo json_encode($response);
    exit;
}

if (!$pdo) {
    $response['message'] = isset($_SESSION['db_error']) ? $_SESSION['db_error'] : 'Database connection failed.';
    error_log("clear_notification.php: DB connection failed - " . $response['message']);
    echo json_encode($response);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$order_id = $input['order_id'] ?? null;

if (!$order_id) {
    $response['message'] = 'Missing order_id.';
    error_log("clear_notification.php: Missing order_id");
    echo json_encode($response);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ? AND order_id = ?");
    $stmt->execute([$_SESSION['customer_id'], $order_id]);
    error_log("clear_notification.php: Notification cleared for order_id: $order_id");

    $response = array(
        'success' => true,
        'message' => 'Notification cleared successfully.'
    );
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    error_log("clear_notification.php: Database error - " . $e->getMessage());
}

error_log("clear_notification.php: Sending response - " . json_encode($response));
echo json_encode($response);
?>