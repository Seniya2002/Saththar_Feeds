<?php
     header('Content-Type: application/json');
     session_start();
     if (!isset($_SESSION['admin_loggedin']) || !$_SESSION['admin_loggedin']) {
         echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
         exit;
     }

     $conn = new mysqli('localhost', 'root', '', 'saththar_feeds_db');
     if ($conn->connect_error) {
         file_put_contents('error_log.txt', date('Y-m-d H:i:s') . ' - Connection failed: ' . $conn->connect_error . PHP_EOL, FILE_APPEND);
         echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
         exit;
     }

     $data = json_decode(file_get_contents('php://input'), true);
     $id = $data['id'] ?? null;

     if (!$id) {
         echo json_encode(['success' => false, 'message' => 'Product ID is required.']);
         exit;
     }

     $stmt = $conn->prepare('DELETE FROM products WHERE id = ?');
     $stmt->bind_param('i', $id);
     if ($stmt->execute()) {
         echo json_encode(['success' => true]);
     } else {
         file_put_contents('error_log.txt', date('Y-m-d H:i:s') . ' - Delete failed: ' . $conn->error . PHP_EOL, FILE_APPEND);
         echo json_encode(['success' => false, 'message' => 'Failed to delete product: ' . $conn->error]);
     }
     $stmt->close();
     $conn->close();
     ?>