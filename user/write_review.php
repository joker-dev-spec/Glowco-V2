<?php
// --- user/write_review.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/user_auth.php';

$conn    = get_db_connection();
$user_id = (int)$_SESSION['user_id'];
$csrf    = generate_csrf_token();

$products = $conn->query("SELECT id, name FROM products ORDER BY name ASC");

$selected_product = isset($_GET['product']) ? (int)$_GET['product'] : 0;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } else {
        $product_id = ($_POST['product_id'] ?? '') !== '' ? (int)$_POST['product_id'] : null;
        $rating     = (int)($_POST['rating'] ?? 0);
        $comment    = trim($_POST['comment'] ?? '');

        if ($product_id !== null) {
            $chk = $conn->prepare("SELECT id FROM products WHERE id = ?");
            $chk->bind_param("i", $product_id);
            $chk->execute();
            if (!$chk->get_result()->num_rows) {
                $error = 'Please choose a valid product.';
            }
        }

        if (!$error && $product_id !== null && ($rating < 1 || $rating > 5)) {
            $error = 'Please select a star rating.';
        }

        if (!$error && $comment === '') {
            $error = 'Please write your review or suggestion.';
        }

        if (!$error && mb_strlen($comment) > 500) {
            $error = 'Please keep it under 500 characters.';
        }

        if (!$error) {
            $stmt = $conn->prepare(
                "INSERT INTO reviews (product_id, user_id, rating, comment)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), created_at = CURRENT_TIMESTAMP"
            );
            $stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);
            $stmt->execute();
            set_flash('success', $product_id !== null ? 'Thanks! Your review has been posted.' : 'Thanks! Your suggestion has been received.');
            header('Location: ' . BASE_URL . 'user/reviews.php');
            exit();
        }

        $selected_product = $product_id ?? 0;
    }
}

$page_title = 'Write a Review — Glow Co.';
include ROOT_PATH . 'includes/header.php';
?>

<div class="wishlist-page">
  <p class="section-eyebrow">Share your experience</p>
  <h1>Write a Review</h1>
  <p class="sub">Review a specific product — or just tell us what's on your mind.</p>

  <?php if ($error): ?>
    <div class="flash flash--error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" class="review-form" style="max-width:560px;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

    <label style="display:block;font-size:.85rem;font-weight:500;color:var(--plum);margin-bottom:8px;">Product (optional)</label>
    <select name="product_id" id="productSelect"
            style="width:100%;padding:12px 14px;border:1.5px solid var(--pink-soft);border-radius:12px;font-size:.9rem;font-family:inherit;background:var(--white);margin-bottom:20px;">
      <option value="">General suggestion / feedback</option>
      <?php while ($p = $products->fetch_assoc()): ?>
        <option value="<?= (int)$p['id'] ?>" <?= $selected_product === (int)$p['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($p['name']) ?>
        </option>
      <?php endwhile; ?>
    </select>

    <div id="ratingRow" style="margin-bottom:20px;">
      <label style="display:block;font-size:.85rem;font-weight:500;color:var(--plum);margin-bottom:8px;">Your rating</label>
      <div class="review-rating-select">
        <?php for ($s = 1; $s <= 5; $s++): ?>
          <button type="button" class="star-btn" data-val="<?= $s ?>" style="background:none;border:none;font-size:1.6rem;cursor:pointer;color:#ddd;">★</button>
        <?php endfor; ?>
      </div>
      <input type="hidden" name="rating" id="reviewRating" value="<?= $selected_product ? 0 : '' ?>">
    </div>

    <label style="display:block;font-size:.85rem;font-weight:500;color:var(--plum);margin-bottom:8px;">Your review or suggestion</label>
    <textarea name="comment" rows="5" maxlength="500" required
              placeholder="What did you love? What can we do better?"
              style="width:100%;padding:12px;border:1.5px solid var(--pink-soft);border-radius:12px;font-size:.9rem;resize:vertical;font-family:inherit;margin-bottom:20px;"><?= htmlspecialchars($_POST['comment'] ?? '') ?></textarea>

    <button type="submit" class="btn-primary">Submit</button>
  </form>
</div>

<script>
(function () {
  var select = document.getElementById('productSelect');
  var row    = document.getElementById('ratingRow');
  var hidden = document.getElementById('reviewRating');
  var stars  = document.querySelectorAll('.star-btn');
  var picked = 0;

  function sync() {
    var hasProduct = select.value !== '';
    row.style.display = hasProduct ? 'block' : 'none';
    if (!hasProduct) { hidden.value = ''; }
  }

  stars.forEach(function (btn) {
    btn.addEventListener('click', function () {
      picked = parseInt(this.dataset.val);
      hidden.value = picked;
      stars.forEach(function (b) {
        b.style.color = parseInt(b.dataset.val) <= picked ? '#f5a623' : '#ddd';
      });
    });
  });

  select.addEventListener('change', sync);
  sync();
})();
</script>

<?php include ROOT_PATH . 'includes/footer.php'; ?>
