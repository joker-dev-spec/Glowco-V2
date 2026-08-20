<?php
// --- pages/reviews_ajax.php ---
header('Content-Type: application/json');

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/config.php';
secure_session_start();

$conn = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $product_id = (int)($_GET['product_id'] ?? 0);
    if ($product_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid product ID.']);
        exit();
    }

    $stmt = $conn->prepare(
        "SELECT r.rating, r.comment, r.created_at, u.name AS user_name
         FROM reviews r
         JOIN users u ON r.user_id = u.id
         WHERE r.product_id = ?
         ORDER BY r.created_at DESC"
    );
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $reviews = array_map(function ($r) {
        return [
            'user_name'  => $r['user_name'],
            'rating'     => (int)$r['rating'],
            'comment'    => $r['comment'],
            'created_at' => $r['created_at'],
        ];
    }, $rows);

    echo json_encode($reviews);
    exit();
}

if ($method === 'POST') {
    if (!is_logged_in()) {
        http_response_code(401);
        echo json_encode(['error' => 'You must be logged in to submit a review.']);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $product_id = (int)($input['product_id'] ?? 0);
    $rating     = (int)($input['rating'] ?? 0);
    $comment    = trim($input['comment'] ?? '');
    $csrf_token = $input['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token.']);
        exit();
    }

    if ($product_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid product ID.']);
        exit();
    }

    if ($rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode(['error' => 'Rating must be between 1 and 5.']);
        exit();
    }

    if (mb_strlen($comment) > 500) {
        http_response_code(400);
        echo json_encode(['error' => 'Comment must be 500 characters or fewer.']);
        exit();
    }

    $user_id = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare(
        "INSERT INTO reviews (product_id, user_id, rating, comment)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), created_at = CURRENT_TIMESTAMP"
    );
    $stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save review.']);
        exit();
    }

    echo json_encode(['success' => true, 'message' => 'Review submitted successfully.']);
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed.']);
