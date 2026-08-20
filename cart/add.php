<?php
// --- cart/add.php ---

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/user_auth.php';

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function json_res($ok, $msg, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['success' => $ok, 'message' => $msg]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($is_ajax) json_res(false, 'Invalid request.', 405);
    header("Location: " . BASE_URL . "pages/shop.php");
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    if ($is_ajax) json_res(false, 'Invalid request. Please try again.', 403);
    set_flash('error', 'Invalid request. Please try again.');
    $back = $_SERVER['HTTP_REFERER'] ?? BASE_URL . 'pages/shop.php';
    if (!str_starts_with($back, BASE_URL) && !str_starts_with($back, '/')) {
        $back = BASE_URL . 'pages/shop.php';
    }
    header("Location: " . $back);
    exit();
}

$product_id = (int)($_POST['product_id'] ?? 0);
$quantity   = max(1, (int)($_POST['quantity'] ?? 1));
$user_id    = $_SESSION['user_id'];

if ($product_id <= 0) {
    if ($is_ajax) json_res(false, 'Invalid product.', 400);
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
    if ($is_ajax) json_res(false, 'Product not found.', 404);
    set_flash('error', 'Product not found.');
    header("Location: " . BASE_URL . "pages/shop.php");
    exit();
}

// Cap quantity at available stock
$quantity = min($quantity, max(1, (int)$product['stock']));

if ($product['stock'] <= 0) {
    $msg = htmlspecialchars($product['name']) . ' is out of stock.';
    if ($is_ajax) json_res(false, $msg);
    set_flash('error', $msg);
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

$success_msg = htmlspecialchars($product['name']) . ' added to your cart.';
if ($is_ajax) json_res(true, $success_msg);

set_flash('success', $success_msg);

$back = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . 'pages/shop.php');
if (!str_starts_with($back, BASE_URL) && !str_starts_with($back, '/')) {
    $back = BASE_URL . 'pages/shop.php';
}
header("Location: " . $back);
exit();
