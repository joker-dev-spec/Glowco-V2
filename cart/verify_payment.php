<?php
// --- cart/verify_payment.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'config/paystack.php';
session_start();

$ref = sanitize_input($_GET['reference'] ?? '');

if (empty($ref)) {
    header("Location: " . BASE_URL . "cart/cart.php");
    exit();
}

$url = "https://api.paystack.co/transaction/verify/" . rawurlencode($ref);
$ch  = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (
    isset($data['data']['status']) &&
    $data['data']['status'] === 'success'
) {
    $order_id = (int)($data['data']['metadata']['order_id'] ?? 0);
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
    }

    set_flash('success', "Payment confirmed. Order #{$order_id} is being processed.");
    header("Location: " . BASE_URL . "user/orders.php");
} else {
    set_flash('error', 'Payment verification failed. Contact support if you were charged.');
    header("Location: " . BASE_URL . "cart/cart.php");
}
exit();