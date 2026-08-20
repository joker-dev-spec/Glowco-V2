<?php
// --- includes/product_card.php ---
// Expects $p with: id, name, price, image_path, stock, description
// Optional: $show_wishlist (bool), $extra_class (string), $remove_btn (HTML)
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__) . '/');

$base        = BASE_URL;
$logged_in   = is_logged_in();
$csrf_token  = $GLOBALS['csrf'] ?? generate_csrf_token();
$show_wishlist = $GLOBALS['show_wishlist'] ?? true;
$extra_class = $GLOBALS['card_extra_class'] ?? '';
?>
<div class="product-card <?= $extra_class ?>">
  <div class="product-img-wrap">
    <?php if (!empty($p['image_path'])): ?>
      <a href="<?= $base ?>pages/product.php?id=<?= $p['id'] ?>">
        <img src="<?= $base . htmlspecialchars($p['image_path']) ?>"
             alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy"
             onerror="this.parentElement.innerHTML='<div class=\'product-img-fallback\'>🧴</div>'">
      </a>
    <?php else: ?>
      <a href="<?= $base ?>pages/product.php?id=<?= $p['id'] ?>">
        <div class="product-img-fallback">🧴</div>
      </a>
    <?php endif; ?>
  </div>

  <div class="product-info">
    <h3>
      <a href="<?= $base ?>pages/product.php?id=<?= $p['id'] ?>">
        <?= htmlspecialchars($p['name']) ?>
      </a>
    </h3>
    <?php if (!empty($p['description'])): ?>
      <p class="product-desc"><?= htmlspecialchars(mb_substr($p['description'], 0, 70)) ?>…</p>
    <?php endif; ?>

    <div class="product-footer">
      <span class="product-price">₦<?= number_format((float)$p['price'], 0) ?></span>
      <div class="product-actions">
        <?php if ((int)$p['stock'] > 0): ?>
          <?php if ($show_wishlist && $logged_in): ?>
            <form method="POST" action="<?= $base ?>wishlist/add.php" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
              <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
              <button type="submit" class="wish-btn" title="Save to wishlist">♡</button>
            </form>
          <?php endif; ?>
          <form method="POST" action="<?= $base ?>cart/add.php" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="btn-add-cart" title="Add to cart">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 01-8 0"/>
              </svg>
              Add
            </button>
          </form>
        <?php else: ?>
          <span class="out-of-stock">Out of Stock</span>
        <?php endif; ?>
      </div>
    </div>
    <?= $GLOBALS['card_extra_html'] ?? '' ?>
  </div>
</div>
