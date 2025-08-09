<?php 
session_start();
require_once 'db_connect.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Check if request is from Postman or browser
$isApiRequest = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

// JSON Response function
function sendJsonResponse($status, $message) {
    header('Content-Type: application/json');
    echo json_encode(["status" => $status, "message" => $message]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        if (!isset($pdo) || !$pdo) {
            if ($isApiRequest) sendJsonResponse("fail", "Database connection failed.");
            else $error = "Database connection failed. Please try again.";
        }

        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];

            if ($isApiRequest) {
                sendJsonResponse("success", "Login successful");
            } else {
                header("Location: index.php");
                exit;
            }
        } else {
            if ($isApiRequest) {
                sendJsonResponse("fail", "Invalid email or password");
            } else {
                $error = "Invalid email or password";
            }
        }
    } catch (PDOException $e) {
        if ($isApiRequest) {
            sendJsonResponse("fail", "Database error: " . $e->getMessage());
        } else {
            $error = "An error occurred: " . $e->getMessage();
        }
    }
}

// Show HTML only if not an API request
if ($isApiRequest) exit;

// Display success message from registration
$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Saththar Feeds - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gradient-to-b from-green-50 to-white min-h-screen flex items-center justify-center px-4">
    <div class="bg-white p-8 rounded-2xl shadow-2xl max-w-md w-full login-container">
        <div class="flex justify-center mb-6">
            <img src="./assets/images/logo/feeds.png" alt="Saththar Feeds Logo" class="h-14 logo">
        </div>
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-4">Welcome Back</h2>
        <p class="text-gray-500 text-center mb-6">Access Your Smart Pet Shop Account</p>

        <?php if (isset($success)): ?>
            <p class="text-green-600 bg-green-50 p-3 rounded-lg text-center mb-4"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p class="text-red-600 bg-red-50 p-3 rounded-lg text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" id="email" name="email" required
                    class="w-full p-3 border rounded-lg"
                    placeholder="Enter your email" />
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full p-3 border rounded-lg"
                    placeholder="Enter your password" />
            </div>
            <button type="submit"
                class="w-full bg-green-600 text-white p-3 rounded-lg hover:bg-green-700">
                Login
            </button>
        </form>
        <p class="mt-6 text-center text-gray-500 text-sm">
            Don’t have an account? <a href="register.php" class="text-green-600 hover:text-green-700 font-medium">Register</a>
        </p>
    </div>
</body>
</html>
