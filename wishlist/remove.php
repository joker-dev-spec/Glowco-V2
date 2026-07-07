<?php
// --- wishlist/remove.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/user_auth.php';

$id      = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];
$conn    = get_db_connection();

$stmt = $conn->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();

set_flash('success', 'Removed from wishlist.');
header("Location: view.php");
exit();