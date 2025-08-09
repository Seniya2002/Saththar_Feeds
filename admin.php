<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== "admin") {
  header("Location: login.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Saththar Feeds - Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100 font-sans">
  <nav class="bg-green-800 text-white p-4 fixed w-full z-10 animate-slide-down">
    <div class="container mx-auto flex justify-between items-center">
      <div class="flex items-center">
        <img src="logo.png" alt="Saththar Feeds Logo" class="h-12 transition-transform duration-300 hover:scale-110">
        <h1 class="ml-4 text-xl font-bold">Admin Panel</h1>
      </div>
      <a href="logout.php" class="hover:text-gray-300 transition-colors duration-300">Logout</a>
    </div>
  </nav>

  <div class="container mx-auto py-20 pt-24 animate-fade-in-up">
    <h2 class="text-3xl font-semibold text-center mb-6">Admin Dashboard</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white p-6 rounded shadow animate-fade-in-up-delay">
        <h3 class="text-xl font-semibold mb-4">Manage Products</h3>
        <form method="POST" action="manage_products.php">
          <div class="mb-4">
            <label class="block">Product Name</label>
            <input type="text" name="product_name" class="border p-2 w-full" required>
          </div>
          <div class="mb-4">
            <label class="block">Price</label>
            <input type="number" name="price" class="border p-2 w-full" step="0.01" required>
          </div>
          <div class="mb-4">
            <label class="block">Pet Type</label>
            <select name="pet_type" class="border p-2 w-full" required>
              <option value="cow">Cow</option>
              <option value="horse">Horse</option>
              <option value="sheep">Sheep</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block">Age Group</label>
            <input type="text" name="age_group" class="border p-2 w-full">
          </div>
          <div class="mb-4">
            <label class="block">Stock</label>
            <input type="number" name="stock" class="border p-2 w-full" required>
          </div>
          <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Add Product</button>
        </form>
      </div>
      <div class="bg-white p-6 rounded shadow animate-fade-in-up-delay">
        <h3 class="text-xl font-semibold mb-4">Manage Orders</h3>
        <p>View and update orders here...</p>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js"></script>
</body>
</html>