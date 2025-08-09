<?php
session_start();

// Include database connection with error handling
require_once __DIR__ . '/db_connect.php';

// Initialize cart in session if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Debugging: Display received ID and any DB error
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
echo "<!-- Debugging: Received product_id = $product_id -->";
if (isset($_SESSION['db_error'])) {
    echo "<!-- Debugging: DB Error = " . htmlspecialchars($_SESSION['db_error']) . " -->";
}

$product = null;
$error_message = '';

// Check if connection is established
if (!isset($pdo) || $pdo === null) {
    $error_message = "Database connection failed: " . (isset($_SESSION['db_error']) ? $_SESSION['db_error'] : 'No connection established');
} else {
    if ($product_id) {
        // Prepare and execute query with error handling
        try {
            $stmt = $pdo->prepare("SELECT id, name, description, price, stock, pet_type, pet_age, image FROM products WHERE id = ?");
            if ($stmt === false) {
                $error_message = "Prepare failed: " . $pdo->errorInfo()[2];
            } else {
                $stmt->bindParam(1, $product_id, PDO::PARAM_INT);
                if (!$stmt->execute()) {
                    $error_message = "Execute failed: " . $stmt->errorInfo()[2];
                } else {
                    $product = $stmt->fetch();
                    if (!$product) {
                        $error_message = "No product found with ID: $product_id";
                    }
                }
            }
        } catch (PDOException $e) {
            $error_message = "Query failed: " . $e->getMessage();
        }
    } else {
        $error_message = "No product ID provided in the URL.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart']) && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    if ($product && $product['stock'] >= $quantity) {
        $new_stock = $product['stock'] - $quantity;
        try {
            $update_stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $update_stmt->bindParam(1, $new_stock, PDO::PARAM_INT);
            $update_stmt->bindParam(2, $product_id, PDO::PARAM_INT);
            if (!$update_stmt->execute()) {
                $error_message = "Update failed: " . $update_stmt->errorInfo()[2];
            }
        } catch (PDOException $e) {
            $error_message = "Update query failed: " . $e->getMessage();
        }

        if (!isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] = ['name' => $product['name'], 'quantity' => 0];
        }
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;

        header("Location: product_details.php?id=" . $product_id . "&success=1");
        exit;
    } else {
        $error_message = "Insufficient stock or product not found.";
        header("Location: product_details.php?id=" . $product_id . "&error=1");
        exit;
    }
}

// No need to close PDO connection explicitly; it closes when the script ends
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Saththar Feeds - Product Details">
    <title>Saththar Feeds - Product Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="./assets/images/logo/image.png" type="image/x-icon">
</head>
<body class="bg-gray-50 font-sans text-gray-900">
    <!-- Inline Header -->
    <nav id="navbar" class="fixed w-full z-50 transition-all duration-300 bg-transparent text-white shadow-md">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <img src="./assets/images/logo/feeds.png" alt="Saththar Feeds Logo" class="h-12 transition-transform duration-300 hover:scale-105" />
                <h1 class="ml-4 text-2xl font-bold">
                    <?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? 'Welcome, ' . htmlspecialchars($_SESSION['username']) : 'Saththar Feeds'; ?>
                </h1>
            </div>
            <div class="space-x-6 md:flex hidden items-center">
                <a href="index.php" class="hover:text-green-400 transition-colors duration-200" title="Home">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9 5v6h4v-6m2-2h.01"></path>
                    </svg>
                </a>
                <a href="products.php" class="hover:text-green-400 transition-colors duration-200" title="Shop">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18l-1.68 9H5.68L3 3zm2 12h14m-7 4h.01"></path>
                    </svg>
                </a>
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']): ?>
                    <a href="profile.php" class="hover:text-green-400 transition-colors duration-200" title="Profile">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zm-4 7a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </a>
                <?php endif; ?>
                <a href="cart.php" class="hover:text-green-400 transition-colors duration-200 relative" title="Cart">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.6 6.4M17 13l2.6 6.4M9 20a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"></path>
                    </svg>
                    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="absolute -top-3 -right-3 bg-red-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center">
                            <?php echo count($_SESSION['cart']); ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="notifications.php" class="hover:text-green-400 transition-colors duration-200 relative" title="Notifications">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="absolute -top-3 -right-3 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
                </a>
                <a href="<?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? 'logout.php' : 'login.php'; ?>" class="hover:text-green-400 transition-colors duration-200" title="<?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? 'Logout' : 'Login'; ?>">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? "M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-6 0V7a3 3 0 016 0v1" : "M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"; ?>"></path>
                    </svg>
                </a>
            </div>
            <div class="md:hidden">
                <button class="focus:outline-none text-white">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <section class="container mx-auto py-24 px-6 animate-panel">
        <?php if ($error_message): ?>
            <p class="text-center text-red-600 font-medium bg-red-50 p-4 rounded-lg"><?php echo htmlspecialchars($error_message); ?></p>
        <?php elseif ($product): ?>
            <div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow-xl hover:shadow-2xl transition-shadow duration-300">
                <div class="flex flex-col md:flex-row items-start gap-8">
                    <?php if ($product['image']): ?>
                        <div class="w-full md:w-1/2 h-96 relative overflow-hidden rounded-xl">
                            <img 
                                src="<?php echo htmlspecialchars($product['image']); ?>" 
                                alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                class="w-full h-full object-contain object-center transition-transform duration-300 hover:scale-105 bg-gray-100"
                            />
                        </div>
                    <?php endif; ?>
                    <div class="w-full md:w-1/2">
                        <h3 class="text-3xl font-bold text-green-800 mb-4"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-gray-600 mb-6 leading-relaxed"><?php echo htmlspecialchars($product['description']); ?></p>
                        <p class="text-2xl font-semibold text-green-600 mb-4">LKR <?php echo number_format($product['price'], 2); ?></p>
                        <p class="text-gray-600 mb-2">Pet Type: <span class="font-medium"><?php echo htmlspecialchars($product['pet_type']); ?></span></p>
                        <p class="text-gray-600 mb-2">Pet Age: <span class="font-medium"><?php echo htmlspecialchars($product['pet_age']); ?></span></p>
                        <p class="text-gray-600 mb-6">Stock: <span class="font-medium"><?php echo htmlspecialchars($product['stock']); ?></span></p>
                        <form method="POST" action="product_details.php" class="mt-6">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <div class="flex items-center mb-6">
                                <label for="quantity" class="mr-4 text-gray-700 font-medium">Quantity:</label>
                                <input 
                                    type="number" 
                                    id="quantity" 
                                    name="quantity" 
                                    min="1" 
                                    max="<?php echo $product['stock']; ?>" 
                                    value="1" 
                                    class="w-24 p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-200"
                                >
                            </div>
                            <button 
                                type="submit" 
                                name="add_to_cart" 
                                class="w-full bg-green-600 text-white px-6 py-3 rounded-full hover:bg-green-700 transition-all duration-300 flex items-center justify-center text-lg font-semibold"
                                <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>
                            >
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.6 6.4M17 13l2.6 6.4M9 20a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"></path>
                                </svg>
                                <?php echo $product['stock'] <= 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                            </button>
                        </form>
                        <?php
                        $success = isset($_GET['success']) && $_GET['success'] == 1;
                        $error = isset($_GET['error']) && $_GET['error'] == 1;
                        if ($success): ?>
                            <p class="mt-4 text-green-600 text-center font-medium bg-green-50 p-3 rounded-lg">Product added to cart successfully!</p>
                        <?php elseif ($error): ?>
                            <p class="mt-4 text-red-600 text-center font-medium bg-red-50 p-3 rounded-lg"><?php echo htmlspecialchars($error_message); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p class="text-center text-red-600 font-medium bg-red-50 p-4 rounded-lg"><?php echo htmlspecialchars($error_message ?: 'Product not found.'); ?></p>
        <?php endif; ?>
    </section>

    <!-- Inline Footer -->
    <footer class="bg-green-800 text-white py-16 animate-panel">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div class="text-center md:text-left">
                    <h4 class="text-xl font-semibold mb-6">Saththar Feeds</h4>
                    <p class="text-sm text-gray-200">Smart Pet Shop Management for Your Animals</p>
                </div>
                <div class="text-center">
                    <h4 class="text-xl font-semibold mb-6">Connect With Us</h4>
                    <div class="flex justify-center space-x-6">
                        <a href="#" class="hover:text-green-400 transition-colors duration-300" aria-label="Facebook">
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.563V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"></path>
                            </svg>
                        </a>
                        <a href="#" class="hover:text-green-400 transition-colors duration-300" aria-label="Twitter">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"></path>
                        </a>
                        <a href="#" class="hover:text-green-400 transition-colors duration-300" aria-label="Contact">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="text-center md:text-right">
                    <h4 class="text-xl font-semibold mb-6">Newsletter</h4>
                    <form class="flex justify-center md:justify-end">
                        <input type="email" placeholder="Enter your email" class="p-3 rounded-l-lg text-gray-900 bg-gray-100 border-none focus:ring-2 focus:ring-green-500 w-64" aria-label="Newsletter email" />
                        <button type="submit" class="bg-green-500 text-white p-3 rounded-r-lg hover:bg-green-600 transition-all duration-300">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>
            <p class="mt-12 text-center text-sm text-gray-300">© 2025 Saththar Feeds. All rights reserved. | Today: <?php echo date('F d, Y, h:i A', strtotime('02:52 PM +0530')); ?></p>
        </div>
    </footer>

</body>
</html>