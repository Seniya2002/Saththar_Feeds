<?php
     session_start();
     require_once __DIR__ . '/db_connect.php';

     header('Content-Type: application/json');

     $response = array('success' => false, 'message' => 'Unknown error occurred.');

     error_log("get_notifications.php: Script started");

     if (!isset($_SESSION['customer_id'])) {
         $response['message'] = 'Unauthorized access. Please log in.';
         error_log("get_notifications.php: Unauthorized access");
         echo json_encode($response);
         exit;
     }

     if (!$pdo) {
         $response['message'] = isset($_SESSION['db_error']) ? $_SESSION['db_error'] : 'Database connection failed.';
         error_log("get_notifications.php: DB connection failed - " . $response['message']);
         echo json_encode($response);
         exit;
     }

     try {
         error_log("get_notifications.php: Preparing query for customer_id: " . $_SESSION['customer_id']);
         $stmt = $pdo->prepare("SELECT id, user_id, message, created_at, order_id, order_status FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
         $stmt->execute([$_SESSION['customer_id']]);
         $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
         error_log("get_notifications.php: Query executed, rows fetched: " . count($notifications));

         $response = array(
             'success' => true,
             'notifications' => $notifications,
             'count' => count($notifications)
         );
     } catch (PDOException $e) {
         $response['message'] = 'Database error: ' . $e->getMessage();
         error_log("get_notifications.php: Database error - " . $e->getMessage());
     }

     error_log("get_notifications.php: Sending response - " . json_encode($response));
     echo json_encode($response);
     ?>