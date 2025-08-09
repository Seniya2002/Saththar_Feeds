<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || !$_SESSION['admin_loggedin']) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'saththar_feeds_db');
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$name = $_POST['name'];
$description = $_POST['description'];
$price = $_POST['price'];
$stock = $_POST['stock'];
$pet_type = $_POST['pet_type'];
$pet_age = $_POST['pet_age'];
$image_path = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    $upload_dir = './Uploads/products/';
    $image_path = $upload_dir . time() . '_' . basename($_FILES['image']['name']);
    move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
}

$stmt = $conn->prepare('INSERT INTO products (name, description, price, stock, pet_type, pet_age, image) VALUES (?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('ssdisss', $name, $description, $price, $stock, $pet_type, $pet_age, $image_path);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add product']);
}

$stmt->close();
$conn->close();
?>