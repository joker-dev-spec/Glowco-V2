<?php
// --- cart/add.php ---

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/user_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "pages/shop.php");
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Invalid request. Please try again.');
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'pages/shop.php'));
    exit();
}

$product_id = (int)($_POST['product_id'] ?? 0);
$quantity   = max(1, (int)($_POST['quantity'] ?? 1));
$user_id    = $_SESSION['user_id'];

if ($product_id <= 0) {
    header("Location: " . BASE_URL . "pages/shop.php");
    exit();
}

$conn = get_db_connection();

// Check product exists and has stock
$stmt = $conn->prepare("SELECT stock, name FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    set_flash('error', 'Product not found.');
    header("Location: " . BASE_URL . "pages/shop.php");
    exit();
}

// Cap quantity at available stock
$quantity = min($quantity, max(1, (int)$product['stock']));

if ($product['stock'] <= 0) {
    set_flash('error', htmlspecialchars($product['name']) . ' is out of stock.');
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'pages/shop.php'));
    exit();
}

$stmt = $conn->prepare(
    "INSERT INTO cart_items (user_id, product_id, quantity)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE quantity = LEAST(quantity + VALUES(quantity), 100)"
);
$stmt->bind_param("iii", $user_id, $product_id, $quantity);
$stmt->execute();

set_flash('success', htmlspecialchars($product['name']) . ' added to your cart.');

$back = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . 'pages/shop.php');
if (!str_starts_with($back, BASE_URL) && !str_starts_with($back, '/')) {
    $back = BASE_URL . 'pages/shop.php';
}
header("Location: " . $back);
exit();
