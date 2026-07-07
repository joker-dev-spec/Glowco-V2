<?php
// --- cart/add.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/user_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity   = max(1, (int)($_POST['quantity'] ?? 1));
    $user_id    = $_SESSION['user_id'];

    if ($product_id > 0) {
        $conn = get_db_connection();
        $stmt = $conn->prepare(
            "INSERT INTO cart_items (user_id, product_id, quantity)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE quantity = quantity + ?"
        );
        $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $quantity);
        $stmt->execute();
    }
}

$referrer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . 'pages/shop.php';
header("Location: " . $referrer);
exit();