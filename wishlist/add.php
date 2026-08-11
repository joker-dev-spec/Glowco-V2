<?php
// --- wishlist/add.php ---

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
$user_id    = $_SESSION['user_id'];

if ($product_id <= 0) {
    header("Location: " . BASE_URL . "pages/shop.php");
    exit();
}

$conn = get_db_connection();
$stmt = $conn->prepare(
    "INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)"
);
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();

set_flash('success', 'Added to wishlist.');
header("Location: " . BASE_URL . "wishlist/view.php");
exit();
