<?php
// Start session for tests
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/../db_connect.php';

// Optional: define test environment flag
define('TEST_ENV', true);
