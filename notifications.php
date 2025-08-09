<?php
     session_start();
     require_once __DIR__ . '/db_connect.php';

     if (!isset($pdo)) {
         die("Database connection not established. Please check db_connect.php.");
     }

     $username = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? $_SESSION['username'] : null;
     $user_id = $_SESSION['customer_id'] ?? null;

     if (!$username) {
         header("Location: login.php");
         exit;
     }

     // Fetch notifications for the user
     $notifications = [];
     try {
         $stmt = $pdo->prepare("SELECT id, message, created_at, order_id, order_status FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
         $stmt->execute([$user_id]);
         $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
     } catch (PDOException $e) {
         error_log("Notification fetch failed: " . $e->getMessage());
     }
     ?>

     <!DOCTYPE html>
     <html lang="en">
     <head>
         <meta charset="UTF-8">
         <meta name="viewport" content="width=device-width, initial-scale=1.0">
         <meta name="description" content="Saththar Feeds - Notifications">
         <title>Saththar Feeds - Notifications</title>
         <link rel="shortcut icon" href="./assets/images/logo/image.png" type="image/x-icon">
         <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
         <link rel="stylesheet" href="style.css">
         <style>
             body {
                 font-family: 'Poppins', sans-serif;
                 background: #f9f9f9;
                 color: #333;
                 margin: 0;
                 padding: 20px;
                 min-height: 100vh;
             }
             .container {
                 max-width: 1200px;
                 margin: 0 auto;
                 padding: 20px;
                 background: #fff;
                 border-radius: 8px;
                 box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                 text-align: center;
             }
             h1 {
                 color: #4a2c2a;
                 font-size: 2rem;
                 margin-bottom: 20px;
             }
             .popup {
                 display: none;
                 position: fixed;
                 top: 0;
                 left: 0;
                 width: 100%;
                 height: 100%;
                 background: rgba(0, 0, 0, 0.5);
                 z-index: 1000;
                 justify-content: center;
                 align-items: center;
             }
             .popup-content {
                 background: #fff;
                 padding: 20px;
                 border-radius: 8px;
                 text-align: center;
                 max-width: 400px;
                 width: 90%;
                 box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
             }
             .notification {
                 background: #d4edda;
                 color: #155724;
                 padding: 15px;
                 border-radius: 5px;
                 margin-bottom: 20px;
             }
             .notification-list {
                 max-width: 800px;
                 margin: 0 auto;
                 text-align: left;
             }
             .notification-item {
                 background: #f1f1f1;
                 padding: 10px;
                 margin-bottom: 10px;
                 border-radius: 5px;
                 cursor: pointer;
             }
             .download-btn {
                 background: #28a745;
                 color: #fff;
                 padding: 10px 20px;
                 border: none;
                 border-radius: 5px;
                 text-decoration: none;
                 font-weight: 500;
             }
             .download-btn:hover {
                 background: #218838;
             }
             .close-btn {
                 background: #dc3545;
                 color: #fff;
                 padding: 5px 10px;
                 border: none;
                 border-radius: 5px;
                 cursor: pointer;
                 margin-top: 10px;
             }
             .close-btn:hover {
                 background: #c82333;
             }
             .loading {
                 display: none;
                 font-style: italic;
                 color: #666;
             }
         </style>
     </head>
     <body>
         <div class="container">
             <h1>Notifications</h1>
             <?php if (!empty($notifications)): ?>
                 <div class="notification-list">
                     <?php foreach ($notifications as $notification): ?>
                         <div class="notification-item" onclick="showPopup(<?php echo $notification['order_id']; ?>, '<?php echo addslashes($notification['message']); ?>')">
                             <p><strong>Order #<?php echo htmlspecialchars($notification['order_id']); ?></strong>: <?php echo htmlspecialchars($notification['message']); ?></p>
                             <p><small><?php echo htmlspecialchars($notification['created_at']); ?></small></p>
                         </div>
                     <?php endforeach; ?>
                 </div>
             <?php else: ?>
                 <p>No notifications found.</p>
             <?php endif; ?>
         </div>

         <div id="popup" class="popup">
             <div class="popup-content">
                 <div id="notification" class="notification">Loading notification...</div>
                 <a id="downloadLink" href="#" class="download-btn" style="display: none;">Download Bill</a>
                 <div id="loading" class="loading">Generating bill...</div>
                 <button class="close-btn" onclick="closePopup()">Close</button>
             </div>
         </div>

         <script>
             function showPopup(orderId, message) {
                 document.getElementById('popup').style.display = 'flex';
                 fetchNotification(orderId, message);
             }

             function closePopup() {
                 const orderId = document.getElementById('downloadLink').getAttribute('data-order-id');
                 document.getElementById('popup').style.display = 'none';
                 fetch('clear_notification.php', {
                     method: 'POST',
                     headers: { 'Content-Type': 'application/json' },
                     body: JSON.stringify({ order_id: orderId }),
                     credentials: 'include'
                 })
                 .then(response => response.json())
                 .then(data => {
                     if (data.success) {
                         window.location.reload(); // Refresh to update notification list
                     }
                 })
                 .catch(error => console.error('Error clearing notification:', error));
             }

             function fetchNotification(orderId, message) {
                 const loading = document.getElementById('loading');
                 const notification = document.getElementById('notification');
                 const downloadLink = document.getElementById('downloadLink');
                 loading.style.display = 'block';
                 notification.textContent = message || 'Loading notification...';
                 downloadLink.style.display = 'none';
                 downloadLink.setAttribute('data-order-id', orderId);

                 fetch(`generate_bill.php?order_id=${orderId}`, { credentials: 'include' })
                     .then(response => response.json())
                     .then(data => {
                         loading.style.display = 'none';
                         if (data.success) {
                             notification.textContent = message || `Order #${orderId} has been approved.`;
                             downloadLink.href = data.pdf_path;
                             downloadLink.download = `bill_${orderId}.pdf`;
                             downloadLink.style.display = 'inline-block';
                         } else {
                             notification.textContent = 'Error: ' + data.message;
                         }
                     })
                     .catch(error => {
                         loading.style.display = 'none';
                         notification.textContent = 'Error fetching notification: ' + error.message;
                     });
             }

             // Close popup when clicking outside
             document.getElementById('popup').addEventListener('click', function(e) {
                 if (e.target === this) closePopup();
             });
         </script>
     </body>
     </html>