<?php
// --- cart/verify_payment.php ---

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'config/paystack.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$ref = sanitize_input($_GET['reference'] ?? '');

if (empty($ref)) {
    header("Location: " . BASE_URL . "cart/cart.php");
    exit();
}

$tx = verify_paystack_transaction($ref);

if ($tx && ($tx['status'] ?? '') === 'success') {
    $order_id = (int)($tx['metadata']['order_id'] ?? 0);
    $conn     = get_db_connection();

    $stmt = $conn->prepare(
        "UPDATE orders SET status = 'paid', paystack_ref = ? WHERE id = ? AND status = 'pending'"
    );
    $stmt->bind_param("si", $ref, $order_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0 && is_logged_in()) {
        $user_id = $_SESSION['user_id'];
        $stmt    = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Reduce stock for each purchased item
        $stmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order_items = $stmt->get_result();

        $dec = $conn->prepare("UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?");
        while ($item = $order_items->fetch_assoc()) {
            $dec->bind_param("ii", $item['quantity'], $item['product_id']);
            $dec->execute();
        }
    }

    set_flash('success', "Payment confirmed. Order #{$order_id} is being processed.");
    header("Location: " . BASE_URL . "user/orders.php");
} else {
    set_flash('error', 'Payment verification failed. Contact support if you were charged.');
    header("Location: " . BASE_URL . "cart/cart.php");
}
exit();
