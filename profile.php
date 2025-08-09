<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header("Location: login.php");
    exit;
}

require_once 'db_connect.php'; // Database connection using PDO

$username = $_SESSION['username'];
$email = '';
$first_name = '';
$last_name = '';
$phone_number = '';
$profile_photo = 'Uploads/default_profile.jpg'; // Default profile photo
$error = '';
$success = '';

// Check if database connection is established
if (!isset($pdo) || $pdo === null) {
    $error = "Database connection failed. Please try again later.";
} else {
    // Fetch user data
    try {
        $stmt = $pdo->prepare("SELECT email, first_name, last_name, phone_number, profile_photo FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        if ($row) {
            $email = $row['email'];
            $first_name = $row['first_name'] ?: '';
            $last_name = $row['last_name'] ?: '';
            $phone_number = $row['phone_number'] ?: '';
            $profile_photo = $row['profile_photo'] ?: $profile_photo;
        } else {
            $error = "User not found.";
        }
    } catch (Exception $e) {
        $error = "Error fetching user data: " . $e->getMessage();
    }

    // Handle profile update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
        $new_email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $new_first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
        $new_last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
        $new_phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
        $new_password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

        try {
            $update_query = "UPDATE users SET email = ?, first_name = ?, last_name = ?, phone_number = ?";
            $params = [$new_email, $new_first_name, $new_last_name, $new_phone_number];
            if ($new_password) {
                $update_query .= ", password = ?";
                $params[] = $new_password;
            }
            $update_query .= " WHERE username = ?";
            $params[] = $username;

            $stmt = $pdo->prepare($update_query);
            $stmt->execute($params);
            $success = "Profile updated successfully!";
            $email = $new_email;
            $first_name = $new_first_name;
            $last_name = $new_last_name;
            $phone_number = $new_phone_number;
        } catch (Exception $e) {
            $error = "Error updating profile: " . $e->getMessage();
        }
    }

    // Handle profile photo upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
        $upload_dir = 'Uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = basename($_FILES['profile_photo']['name']);
        $target_file = $upload_dir . time() . '_' . $file_name;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES['profile_photo']['tmp_name']);
        if ($check === false) {
            $error = 'File is not a valid image.';
        } elseif ($_FILES['profile_photo']['size'] > 5000000) {
            $error = 'File is too large. Maximum size is 5MB.';
        } elseif (!in_array($image_file_type, ['jpg', 'png', 'jpeg'])) {
            $error = 'Only JPG, JPEG, PNG files are allowed.';
        } else {
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file)) {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE username = ?");
                    $stmt->execute([$target_file, $username]);
                    $profile_photo = $target_file;
                    $success = "Profile photo updated successfully!";
                } catch (Exception $e) {
                    $error = "Error updating photo in database: " . $e->getMessage();
                }
            } else {
                $error = "Error uploading file to server.";
            }
        }
    }
}

// Close connection only if it exists
if (isset($pdo) && $pdo !== null) {
    $pdo = null; // PDO connections are closed by setting to null or letting it go out of scope
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Saththar Feeds - User Profile">
    <title>Saththar Feeds - User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="./assets/images/logo/image.png" type="image/x-icon">
</head>
<body class="bg-gray-50 font-sans text-gray-900">
    <nav id="navbar" class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
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
                            <svg class="w-8 h-8 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9 5v6h4v-6m2-2h.01" />
                            </svg>
                        </a>
                        <a href="products.php" class="text-gray-600 hover:text-green-500 transition-all duration-200 flex items-center hover:scale-110" title="Shop">
                            <svg class="w-8 h-8 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18l-1.68 9H5.68L3 3zm2 12h14m-7 4h.01" />
                            </svg>
                        </a>
                        <a href="cart.php" class="text-gray-600 hover:text-green-500 transition-all duration-200 flex items-center relative hover:scale-110" title="Cart">
                            <svg class="w-8 h-8 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.6 6.4M17 13l2.6 6.4M9 20a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z" />
                            </svg>
                            <?php if (!empty($_SESSION['cart'])): ?>
                                <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?php echo count($_SESSION['cart']); ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="notifications.php" class="text-gray-600 hover:text-green-500 transition-all duration-200 flex items-center relative hover:scale-110" title="Notifications">
                            <svg class="w-8 h-8 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <?php $notificationCount = 0; if ($notificationCount > 0): ?>
                                <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?php echo $notificationCount; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="logout.php" class="text-gray-600 hover:text-green-500 transition-all duration-200 flex items-center hover:scale-110" title="Logout">
                            <svg class="w-8 h-8 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-6 0V7a3 3 0 016 0v1" />
                            </svg>
                        </a>
                    </div>
                    <div class="md:hidden">
                        <button id="menu-toggle" class="text-gray-600 hover:text-green-500 focus:outline-none">
                            <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                            </svg>
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
                    <a href="logout.php" class="block px-4 py-2 text-gray-600 hover:bg-green-50 hover:text-green-500 rounded-md transition-all duration-200 text-lg" title="Logout">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <section class="container mx-auto py-24 px-4 sm:px-6 bg-white">
        <div class="max-w-5xl mx-auto bg-[#e6f4ea] p-8 rounded-3xl shadow-lg hover:shadow-xl transition-all duration-300">
            <h2 class="text-3xl font-bold text-green-800 mb-6">Welcome, <?php echo htmlspecialchars($username); ?></h2>

            <?php if ($error): ?>
                <p class="bg-red-50 text-red-600 font-medium p-4 rounded-lg mb-8 text-center"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <?php if ($success): ?>
                <p class="bg-green-50 text-green-600 font-medium p-4 rounded-lg mb-8 text-center"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-8">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                    <div class="relative w-40 h-40 overflow-hidden rounded-full border-4 border-green-200">
                        <img 
                            src="<?php echo htmlspecialchars($profile_photo); ?>" 
                            alt="Profile" 
                            class="w-full h-full object-cover object-center transition-transform duration-300 hover:scale-110 bg-gray-100"
                        />
                    </div>
                    <div class="text-center sm:text-left">
                        <label class="block text-lg font-medium text-gray-700 mb-2">Profile Photo</label>
                        <input 
                            type="file" 
                            name="profile_photo" 
                            accept="image/jpeg,image/png" 
                            class="block w-full text-base text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:text-base file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 hover:file:scale-105 transition-all duration-200"
                        >
                        <button type="submit" class="mt-3 text-base text-green-600 font-medium hover:text-green-700 hover:scale-105 transition-all duration-200">Upload New Photo</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-lg font-medium text-gray-700 mb-2">Username</label>
                        <input 
                            type="text" 
                            value="<?php echo htmlspecialchars($username); ?>" 
                            class="w-full p-3 border border-gray-200 rounded-lg bg-gray-100 cursor-not-allowed text-lg" 
                            disabled
                        >
                    </div>
                    <div>
                        <label class="block text-lg font-medium text-gray-700 mb-2">Email</label>
                        <input 
                            type="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($email); ?>" 
                            required 
                            class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-200 text-lg"
                        >
                    </div>
                    <div>
                        <label class="block text-lg font-medium text-gray-700 mb-2">First Name</label>
                        <input 
                            type="text" 
                            name="first_name" 
                            value="<?php echo htmlspecialchars($first_name); ?>" 
                            class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-200 text-lg"
                        >
                    </div>
                    <div>
                        <label class="block text-lg font-medium text-gray-700 mb-2">Last Name</label>
                        <input 
                            type="text" 
                            name="last_name" 
                            value="<?php echo htmlspecialchars($last_name); ?>" 
                            class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-200 text-lg"
                        >
                    </div>
                    <div>
                        <label class="block text-lg font-medium text-gray-700 mb-2">Phone Number</label>
                        <input 
                            type="text" 
                            name="phone_number" 
                            value="<?php echo htmlspecialchars($phone_number); ?>" 
                            class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-200 text-lg"
                        >
                    </div>
                    <div>
                        <label class="block text-lg font-medium text-gray-700 mb-2">New Password (optional)</label>
                        <input 
                            type="password" 
                            name="password" 
                            placeholder="Enter new password" 
                            class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-200 text-lg"
                        >
                    </div>
                </div>

                <div class="pt-4">
                    <button 
                        type="submit" 
                        name="update_profile" 
                        class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white px-8 py-4 rounded-full hover:scale-105 transition-all duration-200 text-xl font-bold"
                    >
                        Update Profile
                    </button>
                </div>
            </form>
        </div>
    </section>

    <footer class="bg-gradient-to-b from-green-800 to-green-900 text-white py-16">
        <div class="container mx-auto px-6">
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
            <p class="mt-12 text-center text-base text-gray-300">© 2025 Saththar Feeds. All rights reserved. | Today: <?php echo date('F d, Y, h:i A', time()); ?></p>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('menu-toggle').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobile-menu');
            const toggle = document.getElementById('menu-toggle');
            if (!menu.contains(event.target) && !toggle.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Newsletter form validation
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