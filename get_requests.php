<?php
session_start();
require_once __DIR__ . '/db_connect.php';

$response = ['success' => false, 'message' => '', 'data' => []];

if (!isset($_SESSION['admin_loggedin']) || !$_SESSION['admin_loggedin']) {
    $response['message'] = 'Unauthorized access. Please log in as admin.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, customer_id, customer_name, product_id, product_name, pet_type, pet_age, quantity, amount, status, created_at FROM requests ORDER BY created_at DESC");
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = $requests;
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>