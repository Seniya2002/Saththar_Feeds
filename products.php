<?php
session_start();
$username = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? $_SESSION['username'] : null;

// Initialize cart in session if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart directly
require_once 'db_connect.php'; // Database connection using PDO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart']) && isset($pdo) && $pdo !== null) {
    $product_id = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    try {
        // Verify product exists and has stock
        $stmt = $pdo->prepare("SELECT id, name, stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product && $product['stock'] >= $quantity) {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = [
                    'name' => $product['name'],
                    'quantity' => $quantity
                ];
            }
            $success = "Product added to cart successfully!";
        } else {
            $error = $product ? "Insufficient stock for {$product['name']}." : "Product not found.";
        }
    } catch (PDOException $e) {
        $error = "Error adding to cart: " . $e->getMessage();
    }

    // Close connection
    $pdo = null;

    // Redirect to prevent form resubmission
    header("Location: products.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saththar Feeds - Shop Products</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="produc.css">
    <link rel="shortcut icon" href="./assets/images/logo/image.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/@heroicons/react@2.0.18/solid.min.js" defer></script>
</head>
<body class="bg-gray-100 font-sans text-gray-900">
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="px-6">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <img src="./assets/images/logo/feeds.png" alt="Saththar Feeds Logo" class="h-14 transition-transform duration-200 hover:scale-110" />
                    <h1 class="text-3xl font-bold text-green-700 hidden sm:block">Saththar Feeds</h1>
                    <span class="text-lg text-green-600 font-semibold sm:hidden">SF</span>
                </div>
                <div class="flex items-center space-x-6 md:space-x-8">
                    <div class="hidden md:flex items-center space-x-6">
                        <a href="index.php#home" class="text-gray-600 hover:text-green-500 transition-all duration-200 flex items-center hover:scale-110" title="Home">
                            <svg class="w-8 h-8 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9 5v6h4v-6m2-2h.01" /></svg>
                        </a>
                        <a href="products.php" class="text-gray-600 hover:text-green-500 transition-all duration-200 flex items-center hover:scale-110" title="Shop">
                            <svg class="w-8 h-8 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18l-1.68 9H5.68L3 3zm2 12h14m-7 4h.01" /></svg>
                        </a>
                        <a href="cart.php" class="text-gray-600 hover:text-green-500 transition-all duration-200 flex items-center relative hover:scale-110" title="Cart">
                            <svg class="w-8 h-8 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.6 6.4M17 13l2.6 6.4M9 20a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z" /></svg>
                            <?php if (!empty($_SESSION['cart'])): ?>
                                <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?php echo count($_SESSION['cart']); ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="notifications.php" class="text-gray-600 hover:text-green-500 transition-all duration-200 flex items-center relative hover:scale-110" title="Notifications">
                            <svg class="w-8 h-8 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                            <?php $notificationCount = 0; if ($notificationCount > 0): ?>
                                <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?php echo $notificationCount; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?php echo $username ? 'logout.php' : 'login.php'; ?>" class="text-gray-600 hover:text-green-500 transition-all duration-200 flex items-center hover:scale-110" title="<?php echo $username ? 'Logout' : 'Login'; ?>">
                            <svg class="w-8 h-8 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $username ? 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-6 0V7a3 3 0 016 0v1' : 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1'; ?>" /></svg>
                        </a>
                    </div>
                    <div class="md:hidden">
                        <button id="menu-toggle" class="text-gray-600 hover:text-green-500 focus:outline-none">
                            <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div id="mobile-menu" class="hidden md:hidden bg-white shadow-md absolute w-full top-16 left-0 z-40">
                <div class="px-2 pt-2 pb-3 space-y-2">
                    <a href="index.php#home" class="block px-4 py-2 text-gray-600 hover:bg-green-50 hover:text-green-500 rounded-md transition-all duration-200 text-lg" title="Home">Home</a>
                    <a href="products.php" class="block px-4 py-2 text-gray-600 hover:bg-green-50 hover:text-green-500 rounded-md transition-all duration-200 text-lg" title="Shop">Shop</a>
                    <a href="cart.php" class="block px-4 py-2 text-gray-600 hover:bg-green-50 hover:text-green-500 rounded-md transition-all duration-200 text-lg" title="Cart">Cart</a>
                    <a href="notifications.php" class="block px-4 py-2 text-gray-600 hover:bg-green-50 hover:text-green-500 rounded-md transition-all duration-200 text-lg" title="Notifications">Notifications</a>
                    <a href="<?php echo $username ? 'logout.php' : 'login.php'; ?>" class="block px-4 py-2 text-gray-600 hover:bg-green-50 hover:text-green-500 rounded-md transition-all duration-200 text-lg" title="<?php echo $username ? 'Logout' : 'Login'; ?>"><?php echo $username ? 'Logout' : 'Login'; ?></a>
                </div>
            </div>
        </div>
    </nav>

    <script>
        document.getElementById('menu-toggle').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobile-menu');
            const toggle = document.getElementById('menu-toggle');
            if (!menu.contains(event.target) && !toggle.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>

    <section class="py-24 px-4 sm:px-6 bg-white">
        <h3 class="text-5xl font-bold text-center mb-16 text-green-800">All Products</h3>
        <?php if (isset($error)): ?>
            <p class="bg-red-50 text-red-600 font-medium p-4 rounded-lg mb-8 mx-6 error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <p class="bg-green-50 text-green-600 font-medium p-4 rounded-lg mb-8 mx-6 success-message"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php
        if (isset($pdo) && $pdo !== null) {
            try {
                $stmt = $pdo->query("SELECT id, name, price, pet_type, pet_age, stock, image FROM products");
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo '<p class="text-red-600 text-center text-lg mx-6 error-message">Error fetching products: ' . htmlspecialchars($e->getMessage()) . '</p>';
                $products = [];
            }
            $pdo = null;
        } else {
            echo '<p class="text-red-600 text-center text-lg mx-6 error-message">Database connection failed.</p>';
            $products = [];
        }
        ?>
        <?php if (!empty($products)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8 px-4 sm:px-6">
                <?php foreach ($products as $product): ?>
                    <div class="bg-[#e6f4ea] p-10 rounded-3xl shadow-lg hover:shadow-xl transition-all duration-300 product-card flex flex-col h-full">
                        <?php 
                        $imageSrc = !empty($product['image']) && file_exists($product['image']) ? htmlspecialchars($product['image']) : './assets/images/placeholder.jpg';
                        ?>
                        <div class="relative w-full h-80 overflow-hidden rounded-xl mb-8 border border-gray-200 bg-white">
                            <img 
                                src="<?php echo $imageSrc; ?>" 
                                alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                class="w-full h-full object-contain object-center transition-transform duration-300 hover:scale-110 hover:shadow-lg"
                                loading="lazy"
                            />
                        </div>
                        <div class="flex flex-col flex-grow">
                            <h4 class="text-3xl font-semibold text-green-700 mb-4"><?php echo htmlspecialchars($product['name']); ?></h4>
                            <p class="text-2xl font-bold text-green-600 mb-4 price">LKR <?php echo number_format($product['price'], 2); ?></p>
                            <p class="text-gray-600 text-lg mb-4">Pet Type: <?php echo htmlspecialchars($product['pet_type']); ?></p>
                            <p class="text-gray-600 text-lg mb-4">Pet Age: <?php echo htmlspecialchars($product['pet_age']); ?></p>
                            <p class="text-gray-600 text-lg mb-6">Stock: <?php echo htmlspecialchars($product['stock']); ?></p>
                            <div class="mt-auto flex flex-col space-y-4">
                                <a href="product_details.php?id=<?php echo $product['id']; ?>" class="bg-gradient-to-r from-green-500 to-green-600 text-white px-8 py-4 rounded-full hover:scale-105 transition-all duration-200 text-center font-bold text-xl">
                                    View Details
                                </a>
                                <form method="POST" action="products.php">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="add_to_cart" value="1">
                                    <button 
                                        type="submit" 
                                        class="bg-gradient-to-r from-green-500 to-green-600 text-white px-8 py-4 rounded-full hover:scale-105 transition-all duration-200 w-full flex items-center justify-center font-bold text-xl"
                                        title="Add to Cart"
                                        <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>
                                    >
                                        <svg class="w-7 h-7 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.6 6.4M17 13l2.6 6.4M9 20a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"></path>
                                        </svg>
                                        <?php echo $product['stock'] <= 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-gray-600 text-lg mx-6 error-message">No products available.</p>
        <?php endif; ?>
    </section>

    <footer class="bg-gradient-to-b from-green-800 to-green-900 text-white py-16">
        <div class="px-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <div class="text-center lg:text-left">
                    <h4 class="text-2xl font-semibold mb-6">Saththar Feeds</h4>
                    <p class="text-base text-gray-200">Smart Pet Shop Management for Your Animals</p>
                </div>
                <div class="text-center">
                    <h4 class="text-2xl font-semibold mb-6">Connect With Us</h4>
                    <div class="flex justify-center space-x-8">
                        <a href="#" class="hover:text-green-400 transition-all duration-200 hover:scale-110" aria-label="Facebook">
                            <svg class="w-9 h-9" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.563V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"></path>
                            </svg>
                        </a>
                        <a href="#" class="hover:text-green-400 transition-all duration-200 hover:scale-110" aria-label="Twitter">
                            <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"></path>
                            </svg>
                        </a>
                        <a href="#" class="hover:text-green-400 transition-all duration-200 hover:scale-110" aria-label="Contact">
                            <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="text-center lg:text-right">
                    <h4 class="text-2xl font-semibold mb-6">Newsletter</h4>
                    <form id="newsletter-form" class="flex justify-center lg:justify-end">
                        <input type="email" id="newsletter-email" placeholder="Enter your email" class="p-4 rounded-l-lg text-gray-900 bg-gray-100 border-none focus:ring-2 focus:ring-green-500 w-72" aria-label="Newsletter email" />
                        <button type="submit" class="bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-r-lg hover:scale-105 transition-all duration-200 text-lg">Subscribe</button>
                    </form>
                    <p id="newsletter-error" class="text-red-300 text-base mt-3 hidden">Please enter a valid email.</p>
                </div>
            </div>
            <p class="mt-12 text-center text-base text-gray-300">© 2025 Saththar Feeds. All rights reserved. | Today: <?php echo date('F d, Y, h:i A', strtotime('04:22 PM +0530')); ?></p>
        </div>
    </footer>

    <script>
        document.getElementById('newsletter-form').addEventListener('submit', function(event) {
            event.preventDefault();
            const emailInput = document.getElementById('newsletter-email');
            const errorMessage = document.getElementById('newsletter-error');
            const email = emailInput.value.trim();
            const emailRegex = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/;
            
            if (!emailRegex.test(email)) {
                errorMessage.classList.remove('hidden');
                return;
            }
            
            errorMessage.classList.add('hidden');
            alert('Subscribed successfully!');
            emailInput.value = '';
        });
    </script>
</body>
</html>