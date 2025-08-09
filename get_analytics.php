<?php
session_start();
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

$response = array('success' => false, 'message' => 'Unknown error occurred.');

if (!isset($_SESSION['admin_loggedin']) || !$_SESSION['admin_loggedin']) {
    $response['message'] = 'Unauthorized access. Please log in as admin.';
    echo json_encode($response);
    exit;
}

if (!$pdo) {
    $response['message'] = isset($_SESSION['db_error']) ? $_SESSION['db_error'] : 'Database connection failed.';
    echo json_encode($response);
    exit;
}

try {
    // Total Sales (sum of amount for approved requests)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) AS total_sales FROM requests WHERE status = 'Approved'");
    $stmt->execute();
    $total_sales = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'];

    // Total Requests
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_requests FROM requests");
    $stmt->execute();
    $total_requests = $stmt->fetch(PDO::FETCH_ASSOC)['total_requests'];

    // Pending Requests
    $stmt = $pdo->prepare("SELECT COUNT(*) AS pending_requests FROM requests WHERE status = 'Pending'");
    $stmt->execute();
    $pending_requests = $stmt->fetch(PDO::FETCH_ASSOC)['pending_requests'];

    $response = array(
        'success' => true,
        'totalSales' => floatval($total_sales),
        'totalRequests' => intval($total_requests),
        'pendingRequests' => intval($pending_requests)
    );
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    error_log("Error in get_analytics.php: " . $e->getMessage());
}

echo json_encode($response);
?>