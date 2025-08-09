<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Missing product ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $product['image'] = $product['image'] ? "http://localhost/saththar_feeds/{$product['image']}" : 'http://localhost/saththar_feeds/uploads/products/default.jpg';
        echo json_encode(['success' => true, 'product' => $product]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>