<?php
// Initialize response
header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Unknown error', 'products' => []];

try {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'saththar_feeds_db');

    // Check connection
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Query to get the latest 5 products (using id as fallback if created_at is not available)
    $result = $conn->query('SELECT name, description, price, image, pet_type, pet_age FROM products ORDER BY id DESC LIMIT 5');

    if ($result === false) {
        throw new Exception('Query failed: ' . $conn->error);
    }

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    $response = ['success' => true, 'message' => 'Products retrieved successfully', 'products' => $products];
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    // Close connection
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

// Output JSON response
echo json_encode($response);
?>