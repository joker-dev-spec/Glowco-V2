<?php
// --- pages/product.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/config.php';
session_start();

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
?>

<section class="page-hero" style="padding:140px 40px 40px;text-align:left;">
  <p class="section-eyebrow"><?= htmlspecialchars($product['category'] ?? 'Product') ?></p>
</section>

<section style="max-width:1100px;margin:0 auto;padding:0 40px 100px;display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:start;">
  <div>
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
        ₦<?= number_format($product['price'], 2) ?>
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

<?php include ROOT_PATH . 'includes/footer.php'; ?>