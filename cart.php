<?php
session_start();
require_once __DIR__ . '/db_connect.php';

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Initialize response array for AJAX
$response = array(
    'success' => false,
    'message' => 'Invalid request.',
    'cart_count' => array_sum(array_map(function($item) { return $item['quantity']; }, $_SESSION['cart'])),
    'cart' => array_values($_SESSION['cart'])
);

// Check database connection early
if (!$pdo) {
    $response['message'] = isset($_SESSION['db_error']) ? $_SESSION['db_error'] : 'Database connection failed. Please try again later.';
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    header('Content-Type: application/json');
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['message'] = 'Invalid JSON input: ' . json_last_error_msg();
        echo json_encode($response);
        exit;
    }

    $action = isset($input['action']) ? $input['action'] : null;
    $product_id = isset($input['product_id']) ? (int)$input['product_id'] : null;
    $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;

    if (!$pdo) {
        $response['message'] = isset($_SESSION['db_error']) ? $_SESSION['db_error'] : 'Database connection failed during action.';
        echo json_encode($response);
        exit;
    }

    try {
        if ($action === 'add' && $product_id) {
            $stmt = $pdo->prepare("SELECT id, name, price, stock, pet_type, pet_age, image FROM products WHERE id = ? LIMIT 1");
            $stmt->execute(array($product_id));
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                if ($quantity > 0 && $quantity <= $product['stock']) {
                    if (isset($_SESSION['cart'][$product_id])) {
                        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
                    } else {
                        $_SESSION['cart'][$product_id] = array(
                            'quantity' => $quantity,
                            'name' => $product['name'],
                            'price' => $product['price'],
                            'pet_type' => $product['pet_type'],
                            'pet_age' => $product['pet_age'],
                            'image' => isset($product['image']) && $product['image'] ? $product['image'] : './assets/images/placeholder.jpg'
                        );
                    }
                    $response = array(
                        'success' => true,
                        'message' => 'Item added to cart.',
                        'cart_count' => array_sum(array_map(function($item) { return $item['quantity']; }, $_SESSION['cart'])),
                        'cart' => array_values($_SESSION['cart'])
                    );
                } else {
                    $response['message'] = 'Invalid quantity or insufficient stock.';
                }
            } else {
                $response['message'] = 'Product not found.';
            }
        } elseif ($action === 'update' && $product_id && $quantity >= 0) {
            if (isset($_SESSION['cart'][$product_id])) {
                $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ? LIMIT 1");
                $stmt->execute(array($product_id));
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($product && $quantity <= $product['stock']) {
                    if ($quantity === 0) {
                        unset($_SESSION['cart'][$product_id]);
                    } else {
                        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                    }
                    $response = array(
                        'success' => true,
                        'message' => 'Cart updated.',
                        'cart_count' => array_sum(array_map(function($item) { return $item['quantity']; }, $_SESSION['cart'])),
                        'cart' => array_values($_SESSION['cart'])
                    );
                } else {
                    $response['message'] = 'Invalid quantity or insufficient stock.';
                }
            } else {
                $response['message'] = 'Product not in cart.';
            }
        } elseif ($action === 'remove' && $product_id) {
            if (isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
                $response = array(
                    'success' => true,
                    'message' => 'Item removed from cart.',
                    'cart_count' => array_sum(array_map(function($item) { return $item['quantity']; }, $_SESSION['cart'])),
                    'cart' => array_values($_SESSION['cart'])
                );
            } else {
                $response['message'] = 'Product not in cart.';
            }
        } elseif ($action === 'make_request') {
            if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
                $response['message'] = 'Please log in to make a purchase request.';
                echo json_encode($response);
                exit;
            }
            if (empty($_SESSION['cart'])) {
                $response['message'] = 'Cart is empty.';
                echo json_encode($response);
                exit;
            }

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO requests (customer_id, customer_name, product_id, product_name, pet_type, pet_age, quantity, amount, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
            foreach ($_SESSION['cart'] as $product_id => $item) {
                $stmt->execute(array(
                    $_SESSION['user_id'],
                    $_SESSION['username'],
                    $product_id,
                    $item['name'],
                    $item['pet_type'],
                    $item['pet_age'],
                    $item['quantity'],
                    $item['price'] * $item['quantity']
                ));
            }
            $pdo->commit();
            $_SESSION['cart'] = array();
            $response = array(
                'success' => true,
                'message' => 'Purchase request submitted successfully!',
                'cart_count' => 0,
                'cart' => array()
            );
        } else {
            $response['message'] = 'Invalid action.';
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $response['message'] = 'Database error occurred: ' . $e->getMessage();
        error_log("Database error in cart.php: " . $e->getMessage() . ", script: " . (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'unknown'));
    }
    echo json_encode($response);
    exit;
}

// HTML fallback for non-AJAX requests
$cart_items = array();
$total = 0;
$error_message = '';
$success_message = '';

if (!$pdo) {
    $error_message = isset($_SESSION['db_error']) ? $_SESSION['db_error'] : 'Unable to connect to the database. Please try again later.';
}

if (!empty($_SESSION['cart']) && $pdo) {
    $product_ids = array_keys($_SESSION['cart']);
    if (!empty($product_ids)) {
        try {
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            $stmt = $pdo->prepare("SELECT id, name, price, stock, pet_type, pet_age, image FROM products WHERE id IN ($placeholders)");
            $stmt->execute($product_ids);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($products as $product) {
                $cart_items[$product['id']] = array(
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'quantity' => $_SESSION['cart'][$product['id']]['quantity'],
                    'stock' => $product['stock'],
                    'pet_type' => $product['pet_type'],
                    'pet_age' => $product['pet_age'],
                    'image' => isset($product['image']) && $product['image'] ? $product['image'] : './assets/images/placeholder.jpg'
                );
                $total += $product['price'] * $_SESSION['cart'][$product['id']]['quantity'];
            }
        } catch (PDOException $e) {
            $error_message = "Error fetching cart items: " . $e->getMessage();
            error_log("Error fetching cart items in cart.php: " . $e->getMessage());
        }
    }
}

// Handle form-based updates and removes (fallback for non-AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart']) && isset($_POST['quantities']) && $pdo) {
    foreach ($_POST['quantities'] as $product_id => $quantity) {
        $quantity = (int)$quantity;
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else if (isset($cart_items[$product_id]) && $quantity <= $cart_items[$product_id]['stock']) {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        } else {
            $error_message = "Invalid quantity for {$cart_items[$product_id]['name']}.";
        }
    }
    $success_message = "Cart updated successfully.";
    header("Location: cart.php?success=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item']) && isset($_POST['product_id']) && $pdo) {
    $product_id = (int)$_POST['product_id'];
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        $success_message = "Item removed from cart.";
    }
    header("Location: cart.php?success=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['make_request']) && !empty($_SESSION['cart']) && $pdo) {
    if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
        $error_message = "Please log in to make a purchase request.";
        header("Location: login.php");
        exit;
    }
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO requests (customer_id, customer_name, product_id, product_name, pet_type, pet_age, quantity, amount, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $stmt->execute(array(
                $_SESSION['user_id'],
                $_SESSION['username'],
                $product_id,
                $item['name'],
                $item['pet_type'],
                $item['pet_age'],
                $item['quantity'],
                $item['price'] * $item['quantity']
            ));
        }
        $pdo->commit();
        $_SESSION['cart'] = array();
        $success_message = "Purchase request submitted successfully!";
        header("Location: cart.php?success=1");
        exit;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error_message = "Error submitting request: " . $e->getMessage();
        error_log("Error submitting request in cart.php: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Saththar Feeds - Shopping Cart">
    <title>Saththar Feeds - Shopping Cart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="./assets/images/logo/image.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/@heroicons/react@2.0.18/solid.min.js" defer></script>
</head>
<body class="bg-gray-100 font-sans text-gray-900">
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="px-6">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <img src="./assets/images/logo/feeds.png" alt="Saththar Feeds Logo" class="h-14 transition-transform duration-200 hover:scale-110" />
                    <h1 class="text-3xl font-bold text-green-700 hidden sm:block">
                        <?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? 'Welcome, ' . htmlspecialchars($_SESSION['username']) : 'Saththar Feeds'; ?>
                    </h1>
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
                        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']): ?>
                            <a href="profile.php" class="text-gray-600 hover:text-green-500 transition-all duration-200 flex items-center hover:scale-110" title="Profile">
                                <svg class="w-8 h-8 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zm-4 7a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </a>
                        <?php endif; ?>
                        <a href="cart.php" class="text-gray-600 hover:text-green-500 transition-all duration-200 flex items-center relative hover:scale-110" title="Cart">
                            <svg class="w-8 h-8 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.6 6.4M17 13l2.6 6.4M9 20a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z" /></svg>
                            <?php if (!empty($_SESSION['cart'])): ?>
                                <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?php echo array_sum(array_map(function($item) { return $item['quantity']; }, $_SESSION['cart'])); ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="notifications.php" class="text-gray-600 hover:text-green-500 transition-all duration-200 flex items-center relative hover:scale-110" title="Notifications">
                            <svg class="w-8 h-8 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                            <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
                        </a>
                        <a href="<?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? 'logout.php' : 'login.php'; ?>" class="text-gray-600 hover:text-green-500 transition-all duration-200 flex items-center hover:scale-110" title="<?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? 'Logout' : 'Login'; ?>">
                            <svg class="w-8 h-8 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-6 0V7a3 3 0 016 0v1' : 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1'; ?>" /></svg>
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
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']): ?>
                        <a href="profile.php" class="block px-4 py-2 text-gray-600 hover:bg-green-50 hover:text-green-500 rounded-md transition-all duration-200 text-lg" title="Profile">Profile</a>
                    <?php endif; ?>
                    <a href="cart.php" class="block px-4 py-2 text-gray-600 hover:bg-green-50 hover:text-green-500 rounded-md transition-all duration-200 text-lg" title="Cart">Cart</a>
                    <a href="notifications.php" class="block px-4 py-2 text-gray-600 hover:bg-green-50 hover:text-green-500 rounded-md transition-all duration-200 text-lg" title="Notifications">Notifications</a>
                    <a href="<?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? 'logout.php' : 'login.php'; ?>" class="block px-4 py-2 text-gray-600 hover:bg-green-50 hover:text-green-500 rounded-md transition-all duration-200 text-lg" title="<?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? 'Logout' : 'Login'; ?>"><?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? 'Logout' : 'Login'; ?></a>
                </div>
            </div>
        </div>
    </nav>

    <section class="py-24 px-4 sm:px-6 bg-white">
        <h3 class="text-5xl font-bold text-center mb-16 text-green-800">Shopping Cart</h3>
        <?php if ($error_message): ?>
            <p class="bg-red-50 text-red-600 font-medium p-4 rounded-lg mb-8 mx-6 error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php elseif ($success_message): ?>
            <p class="bg-green-50 text-green-600 font-medium p-4 rounded-lg mb-8 mx-6 success-message"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <p class="text-center text-gray-600 font-medium p-4 rounded-lg bg-gray-50 mx-6">Your cart is empty.</p>
            <div class="text-center mt-8">
                <a href="products.php" class="bg-gradient-to-r from-green-500 to-green-600 text-white px-8 py-4 rounded-full hover:scale-105 transition-all duration-200 font-bold text-xl">
                    Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="max-w-4xl mx-auto">
                <form method="POST" action="cart.php" class="mb-8">
                    <div class="grid grid-cols-1 gap-6">
                        <?php foreach ($cart_items as $product_id => $item): ?>
                            <div class="bg-[#e6f4ea] p-10 rounded-3xl shadow-lg hover:shadow-xl transition-all duration-300 product-card">
                                <div class="relative w-full h-80 overflow-hidden rounded-xl mb-8 border border-gray-200 bg-white">
                                    <img 
                                        src="<?php echo htmlspecialchars($item['image']); ?>" 
                                        alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                        class="w-full h-full object-contain object-center transition-transform duration-300 hover:scale-110 hover:shadow-lg"
                                        loading="lazy"
                                    />
                                </div>
                                <h4 class="text-3xl font-semibold text-green-700 mb-4"><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p class="text-2xl font-bold text-green-600 mb-4 price">LKR <?php echo number_format($item['price'], 2); ?></p>
                                <p class="text-gray-600 text-lg mb-4">Pet Type: <?php echo htmlspecialchars($item['pet_type']); ?></p>
                                <p class="text-gray-600 text-lg mb-4">Pet Age: <?php echo htmlspecialchars($item['pet_age']); ?></p>
                                <p class="text-gray-600 text-lg mb-6">Stock: <?php echo htmlspecialchars($item['stock']); ?></p>
                                <div class="flex items-center mb-6">
                                    <label for="quantity_<?php echo $product_id; ?>" class="mr-4 text-gray-700 font-medium">Quantity:</label>
                                    <input 
                                        type="number" 
                                        id="quantity_<?php echo $product_id; ?>" 
                                        name="quantities[<?php echo $product_id; ?>]" 
                                        min="1" 
                                        max="<?php echo $item['stock']; ?>" 
                                        value="<?php echo $item['quantity']; ?>" 
                                        class="w-24 p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-200"
                                    >
                                </div>
                                <div class="flex flex-col space-y-4">
                                    <button 
                                        type="submit" 
                                        name="update_cart" 
                                        class="bg-gradient-to-r from-green-500 to-green-600 text-white px-8 py-4 rounded-full hover:scale-105 transition-all duration-200 font-bold text-xl"
                                    >
                                        Update Cart
                                    </button>
                                    <form method="POST" action="cart.php">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <input type="hidden" name="remove_item" value="1">
                                        <button 
                                            type="submit" 
                                            class="bg-red-500 text-white px-8 py-4 rounded-full hover:bg-red-600 hover:scale-105 transition-all duration-200 font-bold text-xl"
                                        >
                                            Remove Item
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-8 text-right">
                        <p class="text-2xl font-bold text-green-600 mb-4">Total: <?php echo number_format($total, 2); ?> LKR</p>
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="make_request" value="1">
                            <button 
                                type="submit" 
                                class="bg-gradient-to-r from-green-500 to-green-600 text-white px-8 py-4 rounded-full hover:scale-105 transition-all duration-200 font-bold text-xl"
                                <?php echo !isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] ? 'disabled' : ''; ?>
                            >
                                Make Request
                            </button>
                        </form>
                    </div>
                </form>
            </div>
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
            <p class="mt-12 text-center text-base text-gray-300">© 2025 Saththar Feeds. All rights reserved. | Today: <?php echo date('F d, Y, h:i A', strtotime('10:35 PM +0530')); ?></p>
        </div>
    </footer>

    <script>
        document.getElementById('menu-toggle').addEventListener('click', function() {
            var menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        document.addEventListener('click', function(event) {
            var menu = document.getElementById('mobile-menu');
            var toggle = document.getElementById('menu-toggle');
            if (!menu.contains(event.target) && !toggle.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });

        document.getElementById('newsletter-form').addEventListener('submit', function(event) {
            event.preventDefault();
            var emailInput = document.getElementById('newsletter-email');
            var errorMessage = document.getElementById('newsletter-error');
            var email = emailInput.value.trim();
            var emailRegex = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/;
            
            if (!emailRegex.test(email)) {
                errorMessage.classList.remove('hidden');
                return;
            }
            
            errorMessage.classList.add('hidden');
            emailInput.value = '';
            var successMessage = document.createElement('p');
            successMessage.className = 'bg-green-50 text-green-600 font-medium p-4 rounded-lg mb-8 mx-6 success-message';
            successMessage.textContent = 'Subscribed successfully!';
            document.querySelector('section').insertBefore(successMessage, document.querySelector('section > h3').nextSibling);
            setTimeout(function() { successMessage.remove(); }, 3000);
        });
    </script>
</body>
</html>