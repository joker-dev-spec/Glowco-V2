<?php
// --- user/reviews.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/user_auth.php';

$conn    = get_db_connection();
$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare(
        "SELECT r.id, r.rating, r.comment, r.created_at,
                p.id AS product_id, p.name AS product_name, p.image_path
         FROM reviews r
         JOIN products p ON r.product_id = p.id
         WHERE r.user_id = ?
         ORDER BY r.created_at DESC"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    // Reviews table not present yet (run database/migrate_reviews.sql)
    $reviews = [];
}

$page_title = 'My Reviews — Glow Co.';
include ROOT_PATH . 'includes/header.php';
?>

<div class="wishlist-page">
  <p class="section-eyebrow">Your feedback</p>
  <h1>My Reviews</h1>

  <?php if (empty($reviews)): ?>
    <div class="empty-wish">
      <div style="font-size:4rem;margin-bottom:16px;">⭐</div>
      <h2>No reviews yet</h2>
      <p style="margin-bottom:28px;font-size:.95rem;">You haven't reviewed any products yet. Browse products and share your experience!</p>
      <a href="<?= BASE_URL ?>pages/shop.php" class="btn-primary">Browse products</a>
    </div>
  <?php else: ?>
    <p class="sub"><?= count($reviews) ?> review<?= count($reviews) > 1 ? 's' : '' ?></p>
    <div style="display:flex;flex-direction:column;gap:16px;margin-top:24px;">
      <?php foreach ($reviews as $rev): ?>
        <div style="background:var(--white);border-radius:var(--radius-lg);padding:20px;box-shadow:var(--shadow);display:flex;gap:16px;align-items:flex-start;">
          <?php if (!empty($rev['image_path'])): ?>
            <a href="<?= BASE_URL ?>pages/product.php?id=<?= $rev['product_id'] ?>">
              <img src="<?= BASE_URL . htmlspecialchars($rev['image_path']) ?>"
                   alt="<?= htmlspecialchars($rev['product_name']) ?>"
                   style="width:64px;height:64px;object-fit:cover;border-radius:10px;background:var(--pink-soft);flex-shrink:0;">
            </a>
          <?php else: ?>
            <div style="width:64px;height:64px;background:var(--pink-soft);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;">🧴</div>
          <?php endif; ?>
          <div style="flex:1;min-width:0;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;">
              <a href="<?= BASE_URL ?>pages/product.php?id=<?= $rev['product_id'] ?>"
                 style="font-weight:600;color:var(--plum);font-size:.95rem;text-decoration:none;">
                <?= htmlspecialchars($rev['product_name']) ?>
              </a>
              <span style="font-size:.78rem;color:var(--text-soft);"><?= date('M j, Y', strtotime($rev['created_at'])) ?></span>
            </div>
            <div style="font-size:.95rem;color:#f5a623;letter-spacing:1px;margin:4px 0;">
              <?php for ($s = 1; $s <= 5; $s++): ?>
                <?= $s <= $rev['rating'] ? '★' : '☆' ?>
              <?php endfor; ?>
            </div>
            <?php if (!empty($rev['comment'])): ?>
              <p style="color:var(--text-soft);font-size:.88rem;line-height:1.6;margin:0;"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include ROOT_PATH . 'includes/footer.php'; ?>
