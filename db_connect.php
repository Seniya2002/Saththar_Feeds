<?php
// db_connect.php
try {
    $host = '127.0.0.1';
    $port = '3306'; // Default MySQL/MariaDB port
    $dbname = 'saththar_feeds_db';
    $username = 'root'; // Update for production
    $password = ''; // Update for production (empty for XAMPP default)

    // Initialize PDO with UTF-8 and persistent connection
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true // Reuse connections to reduce overhead
        )
    );
    error_log("Database connection successful: $host:$port, $dbname, script: " . (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'unknown'));
} catch (PDOException $e) {
    // Log detailed error with script context
    $errorMessage = "Database connection failed in " . (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'unknown') . ": " . $e->getMessage();
    error_log($errorMessage);

    // Set session error if session is active
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['db_error'] = $errorMessage;
    }

    $pdo = null;

    // Return JSON error for AJAX requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'Database connection failed. Please try again later.'));
        if (ob_get_length()) {
            ob_end_clean();
        }
        exit;
    }
}

// Security note: For production, set a strong password for the database user and update $username/$password accordingly.
?>