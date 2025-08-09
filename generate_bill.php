<?php
session_start();

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
ob_start();

$response = ['success' => false, 'message' => ''];
error_log("generate_bill.php started at " . date('Y-m-d H:i:s'));

// File paths
$fpdf_path = __DIR__ . '/fpdf.php';
$db_connect_path = __DIR__ . '/db_connect.php';
$logo_path = __DIR__ . '/assets/images/logo/invoice_icon.png';
$signature_path = 'C:/xampp/htdocs/saththar_feeds/assets/images/logo/signature.png';

function send_json_response($response, $flush = true) {
    header('Content-Type: application/json');
    echo json_encode($response);
    if ($flush) {
        ob_end_flush();
    }
    exit;
}

// Check file existence
if (!file_exists($fpdf_path)) {
    error_log("Missing FPDF library: $fpdf_path");
    $response['message'] = 'FPDF library not found.';
    send_json_response($response);
}

if (!file_exists($db_connect_path)) {
    error_log("Missing database connection file: $db_connect_path");
    $response['message'] = 'Database connection file not found.';
    send_json_response($response);
}

require_once $fpdf_path;
require_once $db_connect_path;

try {
    $pdo->query("SELECT 1");
    error_log("Database connection successful");
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $response['message'] = 'Database connection failed: ' . $e->getMessage();
    send_json_response($response);
}

if (empty($_SESSION['admin_loggedin']) || !$_SESSION['admin_loggedin']) {
    error_log("Unauthorized access attempt");
    $response['message'] = 'Unauthorized access. Admin login required.';
    send_json_response($response);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$order_id = isset($input['order_id']) ? (int)$input['order_id'] : 0;
$product_name = isset($input['product_name']) ? trim($input['product_name']) : '';
$amount = isset($input['amount']) ? (float)$input['amount'] : 0;
$customer_name = isset($input['customer_name']) ? trim($input['customer_name']) : '';

if ($order_id <= 0) {
    error_log("Invalid order ID received: $order_id");
    $response['message'] = 'Invalid order ID.';
    send_json_response($response);
}

try {
    // Fetch order details including pet_age and unit price from products
    $stmt = $pdo->prepare("
        SELECT r.*, u.email, u.phone_number, u.address, p.price AS unit_price
        FROM requests r 
        LEFT JOIN users u ON r.customer_id = u.id 
        LEFT JOIN products p ON r.product_id = p.id 
        WHERE r.id = ? AND r.status = 'Approved'
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        error_log("Order ID $order_id not approved or not found");
        $response['message'] = 'Order not found or not approved.';
        send_json_response($response);
    }

    // Log fetched order data for debugging
    error_log("Order data: " . json_encode($order));

    // Validate input data
    if (
        !isset($order['product_name']) ||
        $order['product_name'] !== $product_name ||
        !isset($order['amount']) ||
        (float)$order['amount'] !== $amount ||
        !isset($order['customer_name']) ||
        $order['customer_name'] !== $customer_name ||
        !isset($order['unit_price'])
    ) {
        error_log("Input mismatch for Order ID $order_id. Expected: product_name=$product_name, amount=$amount, customer_name=$customer_name. Got: " . json_encode($order));
        $response['message'] = 'Input data mismatch or product price not found.';
        send_json_response($response);
    }

    // Extract age range from pet_age and ensure parentheses
    $pet_age = isset($order['pet_age']) ? $order['pet_age'] : 'N/A';
    if ($pet_age !== 'N/A') {
        // Try to extract content within parentheses (e.g., "Calf (0-6 months)" -> "0-6 months")
        if (preg_match('/\((.*?)\)/', $pet_age, $matches)) {
            $pet_age = $matches[1];
        } elseif (strpos($pet_age, ' ') !== false) {
            // Extract after first space (e.g., "Calf 0-6 months" -> "0-6 months")
            $pet_age = trim(substr($pet_age, strpos($pet_age, ' ')));
        } else {
            // Assume pet_age is already the age range (e.g., "0-6 months")
            $pet_age = trim($pet_age);
        }
        // Validate pet_age and add parentheses
        if (empty($pet_age) || strlen($pet_age) > 50) {
            error_log("Invalid pet_age format for Order ID $order_id: $pet_age");
            $pet_age = 'N/A';
        } else {
            $pet_age = "($pet_age)"; // Ensure parentheses, e.g., "(0-6 months)"
        }
    } else {
        $pet_age = '(N/A)'; // Fallback with parentheses
    }
    error_log("Processed pet_age: $pet_age");

    // Use unit_price from products table
    $items = [[
        'product_name' => $order['product_name'],
        'quantity' => isset($order['quantity']) ? (int)$order['quantity'] : 0,
        'price' => isset($order['unit_price']) ? (float)$order['unit_price'] : 0,
        'pet_type' => isset($order['pet_type']) ? $order['pet_type'] : 'N/A',
        'pet_age' => $pet_age
    ]];

    // Validate item data
    if ($items[0]['quantity'] <= 0 || $items[0]['price'] <= 0) {
        error_log("Invalid quantity or price for Order ID $order_id: " . json_encode($items[0]));
        $response['message'] = 'Invalid order quantity or price.';
        send_json_response($response);
    }

    // Calculate subtotal using unit price
    $subtotal = $items[0]['quantity'] * $items[0]['price'];
    // Verify subtotal matches the amount from requests
    if (abs($subtotal - $order['amount']) > 0.01) {
        error_log("Amount mismatch for Order ID $order_id. Calculated: $subtotal, Stored: {$order['amount']}");
        $response['message'] = 'Amount mismatch in order data.';
        send_json_response($response);
    }

    $invoice_data = [
        'order_id' => $order['id'],
        'order_date' => isset($order['created_at']) ? date('d/m/Y', strtotime($order['created_at'])) : date('d/m/Y'),
        'due_date' => isset($order['created_at']) ? date('d/m/Y', strtotime($order['created_at'] . ' +8 days')) : date('d/m/Y', strtotime('+8 days')),
        'name' => $order['customer_name'],
        'address' => isset($order['address']) ? $order['address'] : 'N/A',
        'email' => isset($order['email']) ? $order['email'] : 'N/A',
        'phone' => isset($order['phone_number']) ? $order['phone_number'] : 'N/A',
        'items' => $items,
        'subtotal' => $subtotal,
        'total' => $subtotal,
        'paid' => 0,
        'balance_due' => $subtotal
    ];

    class PDF extends FPDF {
        function Header() {
            $this->SetFillColor(144, 238, 144);
            $this->Rect(0, 0, 210, 35, 'F');

            $logo_path = __DIR__ . '/assets/images/logo/invoice_icon.png';
            if (file_exists($logo_path)) {
                $this->Image($logo_path, 10, 5, 35);
            } else {
                error_log("Logo missing: $logo_path");
                $this->SetFont('Arial', 'I', 10);
                $this->Cell(0, 10, 'Logo Missing', 0, 1, 'L');
            }

            $this->SetFont('Arial', 'B', 24);
            $this->SetTextColor(0, 100, 0);
            $this->SetXY(60, 10);
            $this->Cell(90, 15, 'INVOICE', 0, 0, 'C');
            $this->SetTextColor(0, 0, 0);
            $this->SetFont('Arial', '', 10);
            $this->SetXY(140, 10);
            $this->Cell(60, 6, 'Saththar Feeds,', 0, 1, 'R');
            $this->SetX(140);
            $this->Cell(60, 6, '253 Hirirpitiya Road,', 0, 1, 'R');
            $this->SetX(140);
            $this->Cell(60, 6, 'Wellawa, Kurunegalle,', 0, 1, 'R');
            $this->SetX(140);
            $this->Cell(60, 6, 'Sri Lanka.', 0, 1, 'R');
            $this->Ln(10);
        }

        function Footer() {
            $this->SetY(-40);
            $this->SetFont('Arial', 'I', 8);
            $this->SetTextColor(100, 100, 100);
            $this->Cell(0, 5, 'Thank you for your business!', 0, 1, 'C');
            $this->Cell(0, 5, 'Contact: +94762661014 | saththarfeeds@gmail.com', 0, 1, 'C');
            $this->SetLineWidth(0.5);
            $this->Line(10, $this->GetY(), 200, $this->GetY());
            $this->Ln(5);
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(0, 5, 'Terms: Payment due within 8 days of invoice date.', 0, 1, 'C');
        }
    }

    ob_start();
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 45);

    // Move content upward
    $pdf->SetY(40);

    // Bill To and Invoice Details
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(230, 255, 230);
    $pdf->Cell(95, 8, ' BILL TO ', 1, 0, 'L', true);
    $pdf->Cell(95, 8, ' INVOICE DETAILS ', 1, 1, 'L', true);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(95, 6, utf8_decode($invoice_data['name']), 0, 0, 'L');
    $pdf->Cell(95, 6, 'Invoice #: INV' . str_pad($invoice_data['order_id'], 5, '0', STR_PAD_LEFT), 0, 1, 'R');
    $pdf->Cell(95, 6, 'Address: ' . utf8_decode($invoice_data['address']), 0, 0, 'L');
    $pdf->Cell(95, 6, 'Date: ' . $invoice_data['order_date'], 0, 1, 'R');
    $pdf->Cell(95, 6, 'Email: ' . utf8_decode($invoice_data['email']), 0, 0, 'L');
    $pdf->Cell(95, 6, 'Due Date: ' . $invoice_data['due_date'], 0, 1, 'R');
    $pdf->Cell(95, 6, 'Phone: ' . utf8_decode($invoice_data['phone']), 0, 1, 'L');
    $pdf->Ln(10);

    // Order Items Table
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(144, 238, 144);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(190, 10, ' ORDER ITEMS ', 1, 1, 'C', true);

    // Table Header
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(200, 230, 200);
    $pdf->SetLineWidth(0.3);
    $pdf->Cell(50, 8, 'Description', 1, 0, 'L', true);
    $pdf->Cell(30, 8, 'Pet Type', 1, 0, 'L', true);
    $pdf->Cell(35, 8, 'Pet Age', 1, 0, 'L', true);
    $pdf->Cell(30, 8, 'Price', 1, 0, 'R', true);
    $pdf->Cell(20, 8, 'Qty', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Amount', 1, 1, 'R', true);

    // Table Rows
    $pdf->SetFont('Arial', '', 9);
    $row_index = 1;
    if (empty($invoice_data['items'])) {
        error_log("No items found in invoice_data for order ID {$invoice_data['order_id']}");
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->Cell(190, 7, 'No items available for this order.', 1, 1, 'C');
    } else {
        foreach ($invoice_data['items'] as $item) {
            // Validate item data
            if (
                !isset($item['product_name']) || !isset($item['pet_type']) ||
                !isset($item['pet_age']) || !isset($item['price']) || !isset($item['quantity'])
            ) {
                error_log("Invalid item data for row $row_index in order ID {$invoice_data['order_id']}: " . json_encode($item));
                continue; // Skip invalid items
            }

            $fill = ($row_index % 2 == 0) ? true : false; // Alternating row colors
            if ($fill) {
                $pdf->SetFillColor(245, 245, 245); // Light gray
            } else {
                $pdf->SetFillColor(255, 255, 255); // White
            }

            // Handle long product names with MultiCell
            $y_before = $pdf->GetY();
            $pdf->MultiCell(50, 7, utf8_decode($item['product_name']), 1, 'L', $fill);
            $y_after = $pdf->GetY();
            $cell_height = $y_after - $y_before;

            // Adjust other cells to match MultiCell height
            $pdf->SetXY(60, $y_before); // Move to next column (50)
            $pdf->Cell(30, $cell_height, utf8_decode($item['pet_type']), 1, 0, 'L', $fill);
            $pdf->Cell(35, $cell_height, utf8_decode($item['pet_age']), 1, 0, 'L', $fill);
            $pdf->Cell(30, $cell_height, 'LKR ' . number_format($item['price'], 2), 1, 0, 'R', $fill);
            $pdf->Cell(20, $cell_height, $item['quantity'], 1, 0, 'C', $fill);
            $pdf->Cell(25, $cell_height, 'LKR ' . number_format($item['price'] * $item['quantity'], 2), 1, 1, 'R', $fill);

            $pdf->SetY($y_after); // Ensure next row starts below MultiCell
            $row_index++;
        }
    }

    // Totals Section
    $pdf->Ln(5);
    $y_start = $pdf->GetY();
    $pdf->SetFillColor(230, 255, 230);
    $pdf->Rect(130, $y_start, 60, 32, 'F');

    $pdf->SetFont('Arial', 'B', 10);
    $totals = [
        'SUBTOTAL' => $invoice_data['subtotal'],
        'TOTAL' => $invoice_data['total'],
        'PAID' => $invoice_data['paid'],
        'BALANCE DUE' => $invoice_data['balance_due']
    ];

    foreach ($totals as $label => $value) {
        $pdf->SetX(130);
        $pdf->Cell(35, 8, $label, 1, 0, 'L');
        $pdf->Cell(25, 8, 'LKR ' . number_format($value, 2), 1, 1, 'R');
    }

    // Signature Section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(95, 8, 'Authorized Signature', 0, 0, 'L');
    $pdf->Cell(95, 8, 'Customer Signature', 0, 1, 'R');

    // Add signature image for Saththar Feeds
    if (file_exists($signature_path)) {
        $pdf->Image($signature_path, 10, $pdf->GetY(), 60, 20);
    } else {
        error_log("Signature image missing: $signature_path");
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(95, 6, 'Signature Missing', 0, 0, 'L');
    }

    // Customer signature line
    $pdf->SetLineWidth(0.3);
    $pdf->Line(110, $pdf->GetY(), 190, $pdf->GetY());
    $pdf->Ln(20);

    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(95, 6, 'Saththar Feeds', 0, 0, 'L');
    $pdf->Cell(95, 6, utf8_decode($invoice_data['name']), 0, 1, 'R');

    $pdf_output = $pdf->Output('S');
    ob_end_clean();

    if (!$pdf_output) {
        error_log("Empty PDF generated for order ID {$order_id}");
        $response['message'] = 'Failed to generate PDF.';
        send_json_response($response);
    }

    $pdf_base64 = base64_encode($pdf_output);
    error_log("PDF generation complete for order ID {$order_id}. Length: " . strlen($pdf_base64));

    // Insert notification
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$order['customer_id'] ?? 2, "Invoice generated for order #{$order['id']}"]);
    error_log("Notification inserted for user_id: " . ($order['customer_id'] ?? 2));

    $response['success'] = true;
    $response['pdf'] = $pdf_base64;

} catch (Exception $e) {
    error_log("Exception in generate_bill.php: " . $e->getMessage() . " at line " . $e->getLine());
    $response['message'] = 'Error generating bill: ' . $e->getMessage();
    send_json_response($response);
}

send_json_response($response);
?>