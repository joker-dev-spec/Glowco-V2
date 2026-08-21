<?php
// --- pages/product.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/config.php';
secure_session_start();

$id   = (int)($_GET['id'] ?? 0);
$conn = get_db_connection();

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) { header("Location: shop.php"); exit(); }

$page_title = htmlspecialchars($product['name']) . ' — Glow Co.';
include ROOT_PATH . 'includes/header.php';
$csrf = generate_csrf_token();

// --- Reviews data ---
$avg_rating = 0;
$review_count = 0;
$reviews = [];

$rStmt = $conn->prepare("SELECT ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS cnt FROM reviews WHERE product_id = ?");
$rStmt->bind_param("i", $id);
$rStmt->execute();
$summary = $rStmt->get_result()->fetch_assoc();
$avg_rating  = (float)($summary['avg_rating'] ?? 0);
$review_count = (int)($summary['cnt'] ?? 0);

$revStmt = $conn->prepare("SELECT r.rating, r.comment, r.created_at, u.name AS user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$revStmt->bind_param("i", $id);
$revStmt->execute();
$reviews = $revStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Has current user already reviewed?
$user_reviewed = false;
if (is_logged_in()) {
    $chk = $conn->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ? LIMIT 1");
    $uid = (int)$_SESSION['user_id'];
    $chk->bind_param("ii", $id, $uid);
    $chk->execute();
    $user_reviewed = $chk->get_result()->num_rows > 0;
}

// --- Related products (same category, exclude current) ---
$related = [];
$cat = $product['category'] ?? '';
if ($cat !== '') {
    $relStmt = $conn->prepare("SELECT * FROM products WHERE category = ? AND id != ? ORDER BY RAND() LIMIT 4");
    $relStmt->bind_param("si", $cat, $id);
    $relStmt->execute();
    $related = $relStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<section class="page-hero" style="text-align:left;">
  <p class="section-eyebrow"><?= htmlspecialchars($product['category'] ?? 'Product') ?></p>
</section>

<section class="product-detail">
  <div class="zoom-container">
    <?php if ($product['image_path']): ?>
      <img src="<?= BASE_URL . htmlspecialchars($product['image_path']) ?>"
           alt="<?= htmlspecialchars($product['name']) ?>"
           style="width:100%;border-radius:20px;box-shadow:var(--shadow-lg);object-fit:cover;aspect-ratio:1/1;">
    <?php else: ?>
      <div style="width:100%;aspect-ratio:1/1;background:var(--pink-soft);border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:6rem;">🧴</div>
    <?php endif; ?>
  </div>

  <div style="display:flex;flex-direction:column;gap:20px;padding-top:20px;">
    <div>
      <span style="display:inline-block;background:var(--pink-soft);color:var(--pink-deep);font-size:.72rem;font-weight:600;padding:4px 14px;border-radius:50px;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px;">
        <?= htmlspecialchars($product['category'] ?? '') ?>
      </span>
      <h1 style="font-size:clamp(1.8rem,4vw,2.8rem);color:var(--plum);margin-bottom:8px;"><?= htmlspecialchars($product['name']) ?></h1>
      <p style="font-family:var(--font-display);font-size:2rem;font-weight:600;color:var(--plum);">
        ₦<?= number_format($product['price'], 0) ?>
      </p>
    </div>

    <?php if (!empty($product['description'])): ?>
      <p style="color:var(--text-soft);line-height:1.8;font-size:.95rem;"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
    <?php endif; ?>

    <?php if ($product['stock'] > 0): ?>
      <p style="font-size:.85rem;color:#2e7d32;font-weight:500;">✓ <?= $product['stock'] ?> in stock</p>

      <form method="POST" action="<?= BASE_URL ?>cart/add.php" style="display:flex;gap:12px;align-items:center;flex-direction:row;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
        <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>"
               style="width:80px;text-align:center;padding:12px;border:1.5px solid var(--pink-soft);border-radius:12px;font-size:.95rem;">
        <button type="submit" class="btn-primary" style="flex:1;">Add to Cart</button>
      </form>

      <?php if (is_logged_in()): ?>
        <form method="POST" action="<?= BASE_URL ?>wishlist/add.php">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
          <button type="submit" class="btn-primary"
                  style="background:transparent;border:1.5px solid var(--pink);color:var(--plum);width:100%;">
            ♡ Save to Wishlist
          </button>
        </form>
      <?php endif; ?>
    <?php else: ?>
      <p style="color:var(--pink-deep);font-weight:600;font-size:.9rem;text-transform:uppercase;letter-spacing:.06em;">Out of Stock</p>
    <?php endif; ?>

    <div style="border-top:1px solid var(--pink-soft);padding-top:20px;display:flex;flex-direction:column;gap:10px;">
      <div style="display:flex;align-items:center;gap:10px;font-size:.85rem;color:var(--text-soft);">
        <span></span><span>Free shipping on orders over ₦15,000</span>
      </div>
      <div style="display:flex;align-items:center;gap:10px;font-size:.85rem;color:var(--text-soft);">
        <span></span><span>Dermatologist tested &amp; approved</span>
      </div>
      <div style="display:flex;align-items:center;gap:10px;font-size:.85rem;color:var(--text-soft);">
        <span></span><span>Cruelty free &amp; paraben free</span>
      </div>
    </div>
  </div>
</section>

<!-- ===== Reviews Section ===== -->
<section class="reviews-section" style="max-width:720px;margin:40px auto;padding:0 24px;">
  <h2 style="font-size:1.6rem;color:var(--plum);margin-bottom:20px;">Customer Reviews</h2>

  <div class="reviews-summary" style="display:flex;align-items:center;gap:16px;margin-bottom:28px;padding:20px;background:var(--pink-soft);border-radius:16px;">
    <div style="font-size:2.4rem;font-weight:700;color:var(--plum);line-height:1;"><?= $avg_rating > 0 ? number_format($avg_rating, 1) : '–' ?></div>
    <div>
      <div style="font-size:1.1rem;color:#f5a623;letter-spacing:2px;">
        <?php for ($s = 1; $s <= 5; $s++): ?>
          <?= $s <= round($avg_rating) ? '★' : '☆' ?>
        <?php endfor; ?>
      </div>
      <div style="font-size:.85rem;color:var(--text-soft);margin-top:2px;">
        <?= $review_count ?> <?= $review_count === 1 ? 'review' : 'reviews' ?>
      </div>
    </div>
  </div>

  <?php if (empty($reviews)): ?>
    <p style="color:var(--text-soft);font-size:.9rem;margin-bottom:32px;">No reviews yet. Be the first to review this product!</p>
  <?php else: ?>
    <div class="reviews-list" style="display:flex;flex-direction:column;gap:20px;margin-bottom:32px;">
      <?php foreach ($reviews as $rev): ?>
        <div class="review-card" style="padding:16px;border:1px solid var(--pink-soft);border-radius:14px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
            <strong style="color:var(--plum);font-size:.95rem;"><?= htmlspecialchars($rev['user_name']) ?></strong>
            <span style="font-size:.8rem;color:var(--text-soft);"><?= date('M j, Y', strtotime($rev['created_at'])) ?></span>
          </div>
          <div style="font-size:.95rem;color:#f5a623;letter-spacing:1px;margin-bottom:6px;">
            <?php for ($s = 1; $s <= 5; $s++): ?>
              <?= $s <= $rev['rating'] ? '★' : '☆' ?>
            <?php endfor; ?>
          </div>
          <?php if (!empty($rev['comment'])): ?>
            <p style="color:var(--text-soft);font-size:.9rem;line-height:1.6;"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (is_logged_in() && !$user_reviewed): ?>
    <a href="<?= BASE_URL ?>user/write_review.php?product=<?= (int)$product['id'] ?>" class="btn-primary" style="display:inline-block;">Write a Review</a>
  <?php elseif (is_logged_in() && $user_reviewed): ?>
    <p style="color:var(--text-soft);font-size:.9rem;">You've already reviewed this product. <a href="<?= BASE_URL ?>user/write_review.php?product=<?= (int)$product['id'] ?>" style="color:var(--pink-deep);">Update your review</a></p>
  <?php else: ?>
    <a href="<?= BASE_URL ?>auth/login.php" class="btn-primary" style="display:inline-block;">Log in to review</a>
  <?php endif; ?>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Star rating select
  const starBtns = document.querySelectorAll('.star-btn');
  const ratingInput = document.getElementById('reviewRating');
  let selectedVal = 0;

  starBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      selectedVal = parseInt(this.dataset.val);
      ratingInput.value = selectedVal;
      starBtns.forEach(function (b) {
        b.style.color = parseInt(b.dataset.val) <= selectedVal ? '#f5a623' : '#ddd';
      });
    });
  });

  // AJAX submit
  const form = document.getElementById('reviewForm');
  if (!form) return;

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    if (parseInt(ratingInput.value) < 1 || parseInt(ratingInput.value) > 5) {
      const msg = document.getElementById('reviewMsg');
      msg.style.display = 'block';
      msg.style.color = '#e74c3c';
      msg.textContent = 'Please select a star rating.';
      return;
    }

    const data = new FormData(form);

    fetch('<?= BASE_URL ?>reviews_ajax.php', {
      method: 'POST',
      body: data
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
      const msg = document.getElementById('reviewMsg');
      msg.style.display = 'block';
      if (res.success) {
        msg.style.color = '#2e7d32';
        msg.textContent = 'Review submitted! Refreshing…';
        setTimeout(function () { location.reload(); }, 1200);
      } else {
        msg.style.color = '#e74c3c';
        msg.textContent = res.message || 'Something went wrong.';
      }
    })
    .catch(function () {
      const msg = document.getElementById('reviewMsg');
      msg.style.display = 'block';
      msg.style.color = '#e74c3c';
      msg.textContent = 'Network error. Please try again.';
    });
  });
});
</script>

<?php if (!empty($related)): ?>
<section class="related-section" style="max-width:960px;margin:50px auto;padding:0 24px;">
  <h2 style="font-size:1.6rem;color:var(--plum);margin-bottom:20px;">You May Also Like</h2>
  <div class="product-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:20px;">
    <?php foreach ($related as $rel): ?>
      <a href="<?= BASE_URL ?>pages/product.php?id=<?= $rel['id'] ?>" style="text-decoration:none;color:inherit;background:#fff;border-radius:16px;overflow:hidden;box-shadow:var(--shadow-sm);transition:transform .2s,box-shadow .2s;" onmouseenter="this.style.transform='translateY(-4px)';this.style.boxShadow='var(--shadow-md)'" onmouseleave="this.style.transform='';this.style.boxShadow='var(--shadow-sm)'">
        <?php if (!empty($rel['image_path'])): ?>
          <img src="<?= BASE_URL . htmlspecialchars($rel['image_path']) ?>" alt="<?= htmlspecialchars($rel['name']) ?>" style="width:100%;aspect-ratio:1/1;object-fit:cover;">
        <?php else: ?>
          <div style="width:100%;aspect-ratio:1/1;background:var(--pink-soft);display:flex;align-items:center;justify-content:center;font-size:3rem;">🧴</div>
        <?php endif; ?>
        <div style="padding:14px;">
          <h3 style="font-size:.95rem;color:var(--plum);margin:0 0 6px;"><?= htmlspecialchars($rel['name']) ?></h3>
          <p style="font-weight:600;color:var(--plum);margin:0;">₦<?= number_format($rel['price'], 0) ?></p>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php include ROOT_PATH . 'includes/footer.php'; ?>
