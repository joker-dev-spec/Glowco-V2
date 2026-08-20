<?php
// --- pages/product_ajax.php ---
header('Content-Type: application/json');

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/config.php';
secure_session_start();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { echo json_encode(null); exit(); }

$conn = get_db_connection();
$stmt = $conn->prepare(
    "SELECT id, name, price, image_path, stock, description, category
     FROM products WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) { echo json_encode(null); exit(); }

$product['price_formatted'] = '₦' . number_format((float)$product['price'], 0);
$product['image_url'] = $product['image_path'] ? BASE_URL . $product['image_path'] : null;
$product['url'] = BASE_URL . 'pages/product.php?id=' . $product['id'];
$product['csrf'] = generate_csrf_token();

echo json_encode($product);
