<?php
session_start();
header('Content-Type: application/json');

// Check if user or admin is logged in
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    if (!isset($_SESSION['admin_loggedin']) || !$_SESSION['admin_loggedin']) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }
}

// Database connection
$host = '127.0.0.1';
$dbname = 'saththar_feeds_db';
$username = 'root'; // Adjust if different
$password = '';     // Adjust if different

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Fetch all products (add debugging)
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Fetched " . count($products) . " products from database"); // Log number of products
    echo json_encode(['success' => true, 'products' => $products]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching products: ' . $e->getMessage()]);
    error_log("Error fetching products: " . $e->getMessage()); // Log error
}
?>