<?php

require_once dirname(__DIR__) . "/config/config.php";
require_once ROOT_PATH . "includes/user_auth.php";
require_once ROOT_PATH . "config/paystack.php";

$conn = get_db_connection();
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT ci.product_id, ci.quantity, p.price FROM cart_items ci
     JOIN products p ON ci.product_id = p.id WHERE ci.user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($cart)) {
    header("Location: cart.php");
    exit();
}

$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $order_id = $conn->insert_id;

    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
    foreach ($cart as $item) {
        $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        $stmt->execute();
    }

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    die("Checkout failed: " . $e->getMessage());
}

$ref = initialize_paystack_transaction($_SESSION['email'] ?? 'guest@glowco.test', $total, $order_id);
header("Location: " . $ref['authorization_url']);
exit();