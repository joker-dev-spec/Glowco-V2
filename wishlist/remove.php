<?php
// --- wishlist/remove.php ---

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/user_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "wishlist/view.php");
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Invalid request. Please try again.');
    header("Location: " . BASE_URL . "wishlist/view.php");
    exit();
}

$id      = (int)($_POST['id'] ?? 0);
$user_id = $_SESSION['user_id'];
$conn    = get_db_connection();

$stmt = $conn->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();

set_flash('success', 'Removed from wishlist.');
header("Location: " . BASE_URL . "wishlist/view.php");
exit();
