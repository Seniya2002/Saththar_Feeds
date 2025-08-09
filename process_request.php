<?php
session_start();
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

$response = array('success' => false, 'message' => 'Invalid request.');

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

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    $response['message'] = 'Invalid JSON input: ' . json_last_error_msg();
    echo json_encode($response);
    exit;
}

$request_id = isset($input['request_id']) ? (int)$input['request_id'] : null;
$status = isset($input['status']) ? $input['status'] : null;

if (!$request_id || !in_array($status, array('Approved', 'Rejected'))) {
    $response['message'] = 'Invalid request ID or status.';
    echo json_encode($response);
    exit;
}

try {
    $pdo->beginTransaction();

    // Fetch request details
    $stmt = $pdo->prepare("SELECT product_id, quantity, status FROM requests WHERE id = ?");
    $stmt->execute(array($request_id));
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        $pdo->rollBack();
        $response['message'] = 'Request not found.';
        echo json_encode($response);
        exit;
    }

    if ($request['status'] !== 'Pending') {
        $pdo->rollBack();
        $response['message'] = 'Request is already processed.';
        echo json_encode($response);
        exit;
    }

    if ($status === 'Approved') {
        // Check product stock
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute(array($request['product_id']));
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product || $product['stock'] < $request['quantity']) {
            $pdo->rollBack();
            $response['message'] = 'Insufficient stock for product.';
            echo json_encode($response);
            exit;
        }

        // Update product stock
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute(array($request['quantity'], $request['product_id']));
    }

    // Update request status
    $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?");
    $stmt->execute(array($status, $request_id));

    $pdo->commit();
    $response = array(
        'success' => true,
        'message' => "Request #$request_id $status successfully."
    );
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = 'Database error: ' . $e->getMessage();
    error_log("Error in process_request.php: " . $e->getMessage());
}

echo json_encode($response);
?>