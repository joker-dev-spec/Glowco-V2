<?php
// --- cart/update.php ---

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/user_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "cart/cart.php");
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Invalid request. Please try again.');
    header("Location: " . BASE_URL . "cart/cart.php");
    exit();
}

$id     = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

$conn = get_db_connection();

$stmt = $conn->prepare(
    "SELECT ci.quantity, p.stock FROM cart_items ci
     JOIN products p ON ci.product_id = p.id
     WHERE ci.id = ? AND ci.user_id = ?"
);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    header("Location: " . BASE_URL . "cart/cart.php");
    exit();
}

if ($action === 'inc') {
    $new_qty = min((int)$item['quantity'] + 1, max(1, (int)$item['stock']));
    $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $new_qty, $id, $user_id);
    $stmt->execute();
} elseif ($action === 'dec') {
    $new_qty = (int)$item['quantity'] - 1;
    if ($new_qty < 1) {
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        set_flash('info', 'Item removed from your cart.');
    } else {
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $new_qty, $id, $user_id);
        $stmt->execute();
    }
}

header("Location: " . BASE_URL . "cart/cart.php");
exit();
