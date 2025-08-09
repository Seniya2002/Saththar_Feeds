<?php
session_start();
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

$response = array('success' => false, 'message' => 'Unknown error occurred.');

error_log("get_transactions.php: Script started");

if (!isset($_SESSION['admin_loggedin']) || !$_SESSION['admin_loggedin']) {
    $response['message'] = 'Unauthorized access. Please log in as admin.';
    error_log("get_transactions.php: Unauthorized access");
    echo json_encode($response);
    exit;
}

if (!$pdo) {
    $response['message'] = isset($_SESSION['db_error']) ? $_SESSION['db_error'] : 'Database connection failed.';
    error_log("get_transactions.php: DB connection failed - " . $response['message']);
    echo json_encode($response);
    exit;
}

try {
    error_log("get_transactions.php: Preparing query");
    $stmt = $pdo->prepare("SELECT id, customer_name, product_name, pet_type, pet_age, quantity, amount, created_at FROM requests WHERE status = 'Approved' ORDER BY created_at DESC");
    error_log("get_transactions.php: Executing query");
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("get_transactions.php: Query executed, rows fetched: " . count($transactions));

    $response = array(
        'success' => true,
        'data' => $transactions
    );
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    error_log("get_transactions.php: Database error - " . $e->getMessage());
}

error_log("get_transactions.php: Sending response - " . json_encode($response));
echo json_encode($response);
?>