<?php
// --- pages/search_ajax.php ---
// Lightweight JSON endpoint for live search suggestions.
header('Content-Type: application/json');

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/config.php';
secure_session_start();

$q = trim($_GET['q'] ?? '');
if (mb_strlen($q) < 2) {
    echo json_encode(['results' => [], 'total' => 0]);
    exit();
}

$conn = get_db_connection();
$like = '%' . $conn->real_escape_string($q) . '%';

$stmt = $conn->prepare(
    "SELECT id, name, price, image_path, stock, category
     FROM products
     WHERE name LIKE ? OR description LIKE ? OR category LIKE ?
     ORDER BY
       CASE WHEN name LIKE ? THEN 0 ELSE 1 END,
       created_at DESC
     LIMIT 8"
);
$primary = '%' . $q . '%';
$stmt->bind_param("ssss", $like, $like, $like, $primary);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$results = array_map(function ($r) use ($conn) {
    return [
        'id'       => (int)$r['id'],
        'name'     => $r['name'],
        'price'    => '₦' . number_format((float)$r['price'], 2),
        'image'    => $r['image_path'] ? BASE_URL . $r['image_path'] : null,
        'stock'    => (int)$r['stock'],
        'category' => $r['category'] ?? '',
        'url'      => BASE_URL . 'pages/product.php?id=' . $r['id'],
    ];
}, $rows);

$count_stmt = $conn->prepare(
    "SELECT COUNT(*) AS c FROM products WHERE name LIKE ? OR description LIKE ? OR category LIKE ?"
);
$count_stmt->bind_param("sss", $like, $like, $like);
$count_stmt->execute();
$total = (int)$count_stmt->get_result()->fetch_assoc()['c'];

echo json_encode(['results' => $results, 'total' => $total, 'query' => $q]);
