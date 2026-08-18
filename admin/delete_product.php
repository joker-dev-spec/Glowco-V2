<?php

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: products.php");
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) die("Invalid request.");

$id   = (int)($_POST['id'] ?? 0);
$conn = get_db_connection();

$stmt = $conn->prepare("SELECT image_path FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if ($product) {
    if ($product['image_path'] && file_exists(ROOT_PATH . $product['image_path'])) {
        unlink(ROOT_PATH . $product['image_path']);
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    set_flash('success', 'Product deleted.');
} else {
    set_flash('error', 'Product not found.');
}

header("Location: products.php");
exit();