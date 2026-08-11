<?php
// --- cart/checkout.php ---

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . "includes/user_auth.php";
require_once ROOT_PATH . "config/paystack.php";

$conn    = get_db_connection();
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT ci.product_id, ci.quantity, p.price, p.name, p.stock
     FROM cart_items ci
     JOIN products p ON ci.product_id = p.id
     WHERE ci.user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($cart)) {
    header("Location: " . BASE_URL . "cart/cart.php");
    exit();
}

$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += (float)$item['price'] * (int)$item['quantity'];
}

$shipping = $subtotal >= 15000 ? 0 : 1500;
$total    = $subtotal + $shipping;

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $order_id = $conn->insert_id;

    $stmt = $conn->prepare(
        "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)"
    );
    foreach ($cart as $item) {
        $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        $stmt->execute();
    }

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    error_log("Checkout failed: " . $e->getMessage());
    set_flash('error', 'Checkout failed. Please try again.');
    header("Location: " . BASE_URL . "cart/cart.php");
    exit();
}

$email = $_SESSION['email'] ?? '';
if (empty($email)) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $email = $stmt->get_result()->fetch_assoc()['email'] ?? '';
    $_SESSION['email'] = $email;
}

$ref = initialize_paystack_transaction($email, $total, $order_id);
$redirect = $ref['authorization_url'] ?? (BASE_URL . "cart/cart.php");
header("Location: " . $redirect);
exit();
