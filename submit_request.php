<?php
session_start();
require_once __DIR__ . '/db_connect.php';

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    $response['message'] = 'Please log in to make a purchase request.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['make_request']) && !empty($_SESSION['cart'])) {
    try {
        $pdo->beginTransaction();
        foreach ($_SESSION['cart'] as $product_id => $item) {
            // Fetch product details
            $stmt = $pdo->prepare("SELECT name, price, pet_type, pet_age FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                $stmt = $pdo->prepare("INSERT INTO requests (customer_id, customer_name, product_id, product_name, pet_type, pet_age, quantity, amount, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $_SESSION['username'],
                    $product_id,
                    $product['name'],
                    $product['pet_type'],
                    $product['pet_age'],
                    $item['quantity'],
                    $product['price'] * $item['quantity']
                ]);
            }
        }
        $pdo->commit();
        $_SESSION['cart'] = []; // Clear cart
        $response['success'] = true;
        $response['message'] = 'Purchase request submitted successfully!';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $response['message'] = 'Error submitting request: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request or empty cart.';
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>