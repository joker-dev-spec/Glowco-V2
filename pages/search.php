<?php
// --- pages/search.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/config.php';
secure_session_start();

$q    = sanitize_input($_GET['q'] ?? '');
$conn = get_db_connection();

$results = [];
if ($q !== '') {
    $like = '%' . $conn->real_escape_string($q) . '%';
    $stmt = $conn->prepare(
        "SELECT id, name, price, image_path, stock, description FROM products
         WHERE name LIKE ? OR description LIKE ? OR category LIKE ?
         ORDER BY name ASC"
    );
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $results = $stmt->get_result();
}

$page_title = 'Search — Glow Co.';
include ROOT_PATH . 'includes/header.php';
$csrf = generate_csrf_token();
?>

<section class="page-hero">
  <p class="section-eyebrow">Search</p>
  <h1>Find your<br><em>perfect product.</em></h1>

  <form method="GET" action="search.php" class="shop-search" style="max-width:480px;margin:24px auto 0;">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search products...">
    <button type="submit">Search</button>
  </form>
</section>

<section class="products-section" style="padding-top:0;">
  <?php if ($q === ''): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--text-soft);">
      <p>Type something above to search our products.</p>
    </div>
  <?php elseif ($results->num_rows === 0): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--text-soft);">
      <p>No products found for "<?= htmlspecialchars($q) ?>".</p>
      <a href="<?= BASE_URL ?>pages/shop.php" class="btn-primary" style="margin-top:16px;">Browse all products</a>
    </div>
  <?php else: ?>
    <p style="text-align:center;color:var(--text-soft);margin-bottom:24px;">
      <?= $results->num_rows ?> result<?= $results->num_rows !== 1 ? 's' : '' ?> for "<?= htmlspecialchars($q) ?>"
    </p>
    <div class="product-grid">
      <?php while ($p = $results->fetch_assoc()): ?>
        <div class="product-card">
          <div class="product-img-wrap">
            <?php if ($p['image_path']): ?>
              <a href="<?= BASE_URL ?>pages/product.php?id=<?= $p['id'] ?>">
                <img src="<?= BASE_URL . htmlspecialchars($p['image_path']) ?>"
                     alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy"
                     onerror="this.parentElement.innerHTML='<div style=\'width:100%;height:100%;background:var(--pink-soft);display:flex;align-items:center;justify-content:center;font-size:3rem;\'>🧴</div>'">
              </a>
            <?php else: ?>
              <a href="<?= BASE_URL ?>pages/product.php?id=<?= $p['id'] ?>">
                <div style="width:100%;height:100%;background:var(--pink-soft);display:flex;align-items:center;justify-content:center;font-size:3rem;">🧴</div>
              </a>
            <?php endif; ?>
          </div>
          <div class="product-info">
            <h3>
              <a href="<?= BASE_URL ?>pages/product.php?id=<?= $p['id'] ?>"
                 style="color:var(--plum);"><?= htmlspecialchars($p['name']) ?></a>
            </h3>
            <?php if (!empty($p['description'])): ?>
              <p class="product-desc"><?= htmlspecialchars(substr($p['description'], 0, 80)) ?>...</p>
            <?php endif; ?>
            <div class="product-footer">
              <span class="product-price">₦<?= number_format($p['price'], 0) ?></span>
              <div class="product-actions">
                <?php if ($p['stock'] > 0): ?>
                  <form method="POST" action="<?= BASE_URL ?>cart/add.php" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="icon-cart" title="Add to cart">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 01-8 0"/>
                      </svg>
                    </button>
                  </form>
                <?php else: ?>
                  <span class="out-of-stock">Out of Stock</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</section>

<?php include ROOT_PATH . 'includes/footer.php'; ?>
