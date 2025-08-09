<?php
session_start();
require_once 'db_connect.php';

// Initialize error
$error = null;

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!isset($pdo) || !$pdo) {
        $error = "Database connection failed. Please try again later or contact support.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        try {
            // Check if email or username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $name]);

            if ($stmt->fetch()) {
                $error = "Email or username already exists";
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $hashed_password]);
                
                $_SESSION['success'] = "Registration Successful! Please login.";
                header("Location: login.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "An error occurred: " . $e->getMessage();
        }
    }
}

// Display success message from login redirect
$success = isset($_SESSION['success']) ? $_SESSION['success'] : null;
unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Register for Saththar Feeds Smart Pet Shop Management System">
    <title>Saththar Feeds - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="login.css">
    <link rel="shortcut icon" href="./assets/images/logo/image.png" type="image/x-icon">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</head>
<body class="bg-gradient-to-b from-green-50 to-white min-h-screen flex items-center justify-center px-4">
    <div class="bg-white p-8 rounded-2xl shadow-2xl max-w-md w-full login-container">
        <div class="flex justify-center mb-6">
            <img src="./assets/images/logo/feeds.png" alt="Saththar Feeds Logo" class="h-14 logo">
        </div>
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-4">Create Account</h2>
        <p class="text-gray-500 text-center mb-6">Join the Smart Pet Shop Management System</p>

        <?php if ($success): ?>
            <p class="text-green-600 bg-green-50 p-3 rounded-lg text-center mb-4 text-sm font-medium"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="text-red-600 bg-red-50 p-3 rounded-lg text-center mb-4 text-sm font-medium"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="register.php" class="space-y-5">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <div class="relative">
                    <input type="text" id="name" name="name" required
                        class="w-full p-3 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600 bg-gray-50 text-gray-900 placeholder-gray-400"
                        placeholder="Enter your name">
                    <ion-icon name="person-outline" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></ion-icon>
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <div class="relative">
                    <input type="email" id="email" name="email" required
                        class="w-full p-3 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600 bg-gray-50 text-gray-900 placeholder-gray-400"
                        placeholder="Enter your email">
                    <ion-icon name="mail-outline" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></ion-icon>
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" required
                        class="w-full p-3 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600 bg-gray-50 text-gray-900 placeholder-gray-400"
                        placeholder="Enter your password">
                    <ion-icon name="lock-closed-outline" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></ion-icon>
                </div>
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <div class="relative">
                    <input type="password" id="confirm_password" name="confirm_password" required
                        class="w-full p-3 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600 bg-gray-50 text-gray-900 placeholder-gray-400"
                        placeholder="Confirm your password">
                    <ion-icon name="lock-closed-outline" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></ion-icon>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-green-600 text-white p-3 rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:outline-none transition-all duration-300 font-medium text-sm glass-btn ripple-btn min-h-[3rem]">
                Register
            </button>
        </form>

        <p class="mt-6 text-center text-gray-500 text-sm">
            Already have an account?
            <a href="login.php" class="text-green-600 hover:text-green-700 font-medium transition-colors duration-200">Login</a>
        </p>
    </div>
</body>
</html>
