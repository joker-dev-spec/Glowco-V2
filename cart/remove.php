<?php
// --- cart/remove.php ---

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/user_auth.php';

$id      = (int)($_REQUEST['id'] ?? 0);
$user_id = $_SESSION['user_id'];
$conn    = get_db_connection();

$stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();

set_flash('success', 'Item removed from your cart.');
header("Location: " . BASE_URL . "cart/cart.php");
exit();
