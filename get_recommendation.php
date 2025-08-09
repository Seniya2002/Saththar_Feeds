<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$animal = isset($input['animal']) ? $input['animal'] : '';
$age_range = isset($input['age_range']) ? $input['age_range'] : '';

if (empty($animal) || empty($age_range)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$fastapi_url = 'http://localhost:8000/recommend';
$ch = curl_init($fastapi_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['animal' => $animal, 'age_range' => $age_range]));
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch recommendation from AI service']);
    exit;
}

$fastapi_response = json_decode($response, true);
error_log("FastAPI Response: " . json_encode($fastapi_response));
if (!isset($fastapi_response['recommendation'])) {
    echo json_encode(['success' => false, 'message' => $fastapi_response['detail'] ?? 'No recommendation available']);
    exit;
}

$recommendation = $fastapi_response['recommendation'];
$food_name = trim($recommendation['Food']); // Trim whitespace
$vitamin_name = trim($recommendation['Vitamin']) !== 'N/A' ? trim($recommendation['Vitamin']) : 'No Vitamin Recommended'; // Trim whitespace

$food_product = null;
try {
    $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE LOWER(name) = LOWER(?) AND pet_type = ? AND pet_age = ? LIMIT 1");
    $stmt->execute([$food_name, $animal, $age_range]);
    $food_product = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("Food Query: name=$food_name, pet_type=$animal, pet_age=$age_range, SQL: " . $stmt->queryString . ", Result: " . json_encode($food_product));
} catch (PDOException $e) {
    error_log("Database error for food: " . $e->getMessage());
}

$vitamin_product = null;
if ($vitamin_name !== 'No Vitamin Recommended') {
    try {
        $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE LOWER(name) = LOWER(?) AND pet_type = ? AND pet_age = ? LIMIT 1");
        $stmt->execute([$vitamin_name, $animal, $age_range]);
        $vitamin_product = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("Vitamin Query: name=$vitamin_name, pet_type=$animal, pet_age=$age_range, SQL: " . $stmt->queryString . ", Result: " . json_encode($vitamin_product));
    } catch (PDOException $e) {
        error_log("Database error for vitamin: " . $e->getMessage());
    }
}

$recommendations = [];
if ($food_product) {
    $recommendations[] = [
        'id' => $food_product['id'],
        'name' => $food_product['name'],
        'price' => $food_product['price'] ?? 'N/A',
        'image' => $food_product['image'] ? "http://localhost/saththar_feeds/{$food_product['image']}" : 'http://localhost/saththar_feeds/uploads/products/default.jpg',
        'type' => 'Food',
        'age_range' => $recommendation['Age Range'],
        'Match Confidence (%)' => 100
    ];
} else {
    $recommendations[] = [
        'id' => "mock-food-{$animal}-{$age_range}",
        'name' => $food_name,
        'price' => 'N/A',
        'image' => 'http://localhost/saththar_feeds/uploads/products/default.jpg',
        'type' => 'Food',
        'age_range' => $recommendation['Age Range'],
        'Match Confidence (%)' => 100
    ];
}

if ($vitamin_product) {
    $image_path = $vitamin_product['image'] ? "http://localhost/saththar_feeds/{$vitamin_product['image']}" : 'http://localhost/saththar_feeds/uploads/products/default.jpg';
    $file_exists = file_exists($_SERVER['DOCUMENT_ROOT'] . "/saththar_feeds/{$vitamin_product['image']}");
    error_log("Vitamin Image Check: path=$image_path, file_exists=$file_exists");
    $recommendations[] = [
        'id' => $vitamin_product['id'],
        'name' => $vitamin_product['name'],
        'price' => $vitamin_product['price'] ?? 'N/A',
        'image' => $image_path,
        'type' => 'Vitamin',
        'age_range' => $recommendation['Age Range'],
        'Match Confidence (%)' => 100
    ];
} elseif ($vitamin_name !== 'No Vitamin Recommended') {
    $recommendations[] = [
        'id' => "mock-vitamin-{$animal}-{$age_range}",
        'name' => $vitamin_name,
        'price' => 'N/A',
        'image' => 'http://localhost/saththar_feeds/uploads/products/default.jpg',
        'type' => 'Vitamin',
        'age_range' => $recommendation['Age Range'],
        'Match Confidence (%)' => 100
    ];
}

$response_data = ['success' => true, 'products' => $recommendations];
error_log("Final Response: " . json_encode($response_data));
echo json_encode($response_data);
?>