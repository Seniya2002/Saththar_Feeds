<?php
     session_start();
     require_once __DIR__ . '/db_connect.php';

     header('Content-Type: application/json');

     $response = array('success' => false, 'message' => 'Unknown error occurred.');

     error_log("send_notification.php: Script started");

     if (!isset($_SESSION['admin_loggedin']) || !$_SESSION['admin_loggedin']) {
         $response['message'] = 'Unauthorized access. Please log in as admin.';
         error_log("send_notification.php: Unauthorized access");
         echo json_encode($response);
         exit;
     }

     if (!$pdo) {
         $response['message'] = isset($_SESSION['db_error']) ? $_SESSION['db_error'] : 'Database connection failed.';
         error_log("send_notification.php: DB connection failed - " . $response['message']);
         echo json_encode($response);
         exit;
     }

     $input = json_decode(file_get_contents('php://input'), true);
     $order_id = $input['order_id'] ?? null;
     $customer_id = $input['customer_id'] ?? null;
     $message = $input['message'] ?? "Invoice generated for order #$order_id";
     $order_status = $input['order_status'] ?? 'approved';

     if (!$order_id || !$customer_id) {
         $response['message'] = 'Missing required fields: order_id or customer_id.';
         error_log("send_notification.php: Missing required fields - order_id: $order_id, customer_id: $customer_id");
         echo json_encode($response);
         exit;
     }

     try {
         $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, created_at, order_id, order_status) VALUES (?, ?, NOW(), ?, ?)");
         $stmt->execute([$customer_id, $message, $order_id, $order_status]);
         error_log("send_notification.php: Notification inserted for order_id: $order_id, customer_id: $customer_id");

         $response = array(
             'success' => true,
             'message' => 'Notification sent successfully.',
             'notification_id' => $pdo->lastInsertId()
         );
     } catch (PDOException $e) {
         $response['message'] = 'Database error: ' . $e->getMessage();
         error_log("send_notification.php: Database error - " . $e->getMessage());
     }

     error_log("send_notification.php: Sending response - " . json_encode($response));
     echo json_encode($response);
     ?>