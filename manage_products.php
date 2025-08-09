<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== "admin") {
  header("Location: login.php");
  exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST['product_name'];
  $price = $_POST['price'];
  $pet_type = $_POST['pet_type'];
  $age_group = $_POST['age_group'] ?: NULL;
  $stock = $_POST['stock'];
  $conn = new mysqli("localhost", "root", "", "saththar_feeds_db");
  if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
  $stmt = $conn->prepare("INSERT INTO products (name, price, pet_type, age_group, stock) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sdssi", $name, $price, $pet_type, $age_group, $stock);
  $stmt->execute();
  $stmt->close();
  $conn->close();
  header("Location: admin.php");
  exit();
}
?>