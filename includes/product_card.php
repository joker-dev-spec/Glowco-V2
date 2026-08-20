<?php
// --- includes/product_card.php ---
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__) . '/');

$base          = BASE_URL;
$logged_in     = is_logged_in();
$csrf_token    = $GLOBALS['csrf'] ?? generate_csrf_token();
$show_wishlist = $GLOBALS['show_wishlist'] ?? true;
$extra_class   = $GLOBALS['card_extra_class'] ?? '';
$in_stock      = (int)($p['stock'] ?? 0) > 0;
?>
<div class="product-card <?= $extra_class ?>">
  <a href="<?= $base ?>pages/product.php?id=<?= $p['id'] ?>" class="product-img-wrap">
    <?php if (!empty($p['image_path'])): ?>
      <img src="<?= $base . htmlspecialchars($p['image_path']) ?>"
           alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy"
           onerror="this.parentElement.innerHTML='<div class=\'product-img-fallback\'>🧴</div>'">
    <?php else: ?>
      <div class="product-img-fallback">🧴</div>
    <?php endif; ?>
    <?php if ((int)($p['stock'] ?? 0) > 0 && (int)$p['stock'] <= 5): ?>
      <span class="badge-low-stock">Only <?= (int)$p['stock'] ?> left</span>
    <?php endif; ?>
  </a>

  <div class="product-info">
    <h3>
      <a href="<?= $base ?>pages/product.php?id=<?= $p['id'] ?>">
        <?= htmlspecialchars($p['name']) ?>
      </a>
    </h3>
    <?php if (!empty($p['description'])): ?>
      <p class="product-desc"><?= htmlspecialchars(mb_substr($p['description'], 0, 70)) ?>…</p>
    <?php endif; ?>

    <div class="product-price-row">
      <span class="product-price">₦<?= number_format((float)$p['price'], 0) ?></span>
      <?php if ($show_wishlist && $logged_in): ?>
        <form method="POST" action="<?= $base ?>wishlist/add.php" style="display:inline;">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
          <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
          <button type="submit" class="wish-btn" title="Save to wishlist">♡</button>
        </form>
      <?php endif; ?>
    </div>

    <?php if ($in_stock): ?>
      <form method="POST" action="<?= $base ?>cart/add.php" class="add-to-cart-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
        <input type="hidden" name="quantity" value="1">
        <button type="submit" class="btn-add-cart-full">Add to Cart</button>
      </form>
    <?php else: ?>
      <div class="out-of-stock-full">Out of Stock</div>
    <?php endif; ?>

    <?= $GLOBALS['card_extra_html'] ?? '' ?>
  </div>
</div>
