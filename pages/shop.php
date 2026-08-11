<?php
// --- pages/shop.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/config.php';
session_start();

$conn = get_db_connection();
$sort = sanitize_input($_GET['sort'] ?? 'newest');
$q    = sanitize_input($_GET['q'] ?? '');

$allowed_sorts = [
    'newest'     => 'created_at DESC',
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
    'name'       => 'name ASC',
];
$order_clause = $allowed_sorts[$sort] ?? 'created_at DESC';

// Pull perfumes and body lotions as separate result sets
if ($q !== '') {
    $like = '%' . $conn->real_escape_string($q) . '%';

    $stmt_perfumes = $conn->prepare(
        "SELECT id, name, price, image_path, stock, description
         FROM products
         WHERE category = 'Perfume'
         AND (name LIKE ? OR description LIKE ?)
         ORDER BY {$order_clause}"
    );
    $stmt_perfumes->bind_param("ss", $like, $like);
    $stmt_perfumes->execute();
    $perfumes = $stmt_perfumes->get_result();

    $stmt_lotions = $conn->prepare(
        "SELECT id, name, price, image_path, stock, description
         FROM products
         WHERE category = 'Body Lotion'
         AND (name LIKE ? OR description LIKE ?)
         ORDER BY {$order_clause}"
    );
    $stmt_lotions->bind_param("ss", $like, $like);
    $stmt_lotions->execute();
    $lotions = $stmt_lotions->get_result();
} else {
    $perfumes = $conn->query(
        "SELECT id, name, price, image_path, stock, description
         FROM products WHERE category = 'Perfume'
         ORDER BY {$order_clause}"
    );
    $lotions = $conn->query(
        "SELECT id, name, price, image_path, stock, description
         FROM products WHERE category = 'Body Lotion'
         ORDER BY {$order_clause}"
    );
}

$page_title = 'Shop — Glow Co.';
include ROOT_PATH . 'includes/header.php';
$csrf = generate_csrf_token();
?>

<section class="shop-hero">
  <p class="section-eyebrow">The collection</p>
  <h1>Shop <em>everything.</em></h1>
  <p>Premium body creams, perfumes &amp; lotions made with natural butters and botanical oils.</p>

  <form method="GET" action="shop.php" class="shop-search">
    <input type="text" name="q" placeholder="Search products..."
           value="<?= htmlspecialchars($q) ?>">
    <button type="submit">Search</button>
  </form>

  <form method="GET" style="display:flex;justify-content:center;margin-top:16px;">
    <?php if ($q): ?>
      <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
    <?php endif; ?>
    <select name="sort" onchange="this.form.submit()"
            style="width:auto;padding:10px 18px;border-radius:50px;border:1.5px solid var(--pink-soft);font-size:.85rem;background:var(--white);color:var(--text);cursor:pointer;">
      <option value="newest"     <?= $sort === 'newest'     ? 'selected' : '' ?>>Newest</option>
      <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Price: Low to High</option>
      <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
      <option value="name"       <?= $sort === 'name'       ? 'selected' : '' ?>>Name A–Z</option>
    </select>
  </form>
</section>

<?php
// Reusable product card renderer
function render_product_card($p) {
    $base = BASE_URL;
    $logged_in = is_logged_in();
    $csrf_token = $GLOBALS['csrf'] ?? generate_csrf_token();
    ob_start();
?>
  <div class="product-card">
    <div class="product-img-wrap">
      <?php if ($p['image_path']): ?>
        <a href="<?= $base ?>pages/product.php?id=<?= $p['id'] ?>">
          <img src="<?= $base . htmlspecialchars($p['image_path']) ?>"
               alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy"
               onerror="this.parentElement.innerHTML='<div style=\'width:100%;height:100%;background:var(--pink-soft);display:flex;align-items:center;justify-content:center;font-size:3rem;\'>🧴</div>'">
        </a>
      <?php else: ?>
        <a href="<?= $base ?>pages/product.php?id=<?= $p['id'] ?>">
          <div style="width:100%;height:100%;background:var(--pink-soft);display:flex;align-items:center;justify-content:center;font-size:3rem;">🧴</div>
        </a>
      <?php endif; ?>
      <?php if ($p['stock'] > 0): ?>
        <div class="product-overlay">
          <form method="POST" action="<?= $base ?>cart/add.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="quick-add">Quick Add</button>
          </form>
        </div>
      <?php endif; ?>
    </div>
    <div class="product-info">
      <h3>
        <a href="<?= $base ?>pages/product.php?id=<?= $p['id'] ?>"
           style="color:var(--plum);"><?= htmlspecialchars($p['name']) ?></a>
      </h3>
      <?php if (!empty($p['description'])): ?>
        <p class="product-desc"><?= htmlspecialchars(substr($p['description'], 0, 80)) ?>...</p>
      <?php endif; ?>
      <div class="product-footer">
        <span class="product-price">₦<?= number_format($p['price'], 2) ?></span>
        <div class="product-actions">
          <?php if ($p['stock'] > 0): ?>
            <?php if ($logged_in): ?>
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
              <button type="submit" class="icon-cart" title="Add to cart">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
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
<?php
    return ob_get_clean();
}
?>

<!-- ── PERFUMES SECTION ───────────────────────────────────────────── -->
<section class="products-section">
  <div class="category-header">
    <h2>Perfumes</h2>
  </div>

  <?php if ($perfumes->num_rows === 0): ?>
    <div style="text-align:center;padding:40px 20px;color:var(--text-soft);">
      <p>No perfumes found<?= $q ? ' for "' . htmlspecialchars($q) . '"' : '' ?>.</p>
    </div>
  <?php else: ?>
    <div class="product-grid">
      <?php while ($p = $perfumes->fetch_assoc()): ?>
        <?= render_product_card($p) ?>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</section>

<!-- ── BODY LOTIONS SECTION ──────────────────────────────────────── -->
<section class="products-section" style="padding-top:0;">
  <div class="category-header">
    <h2>Body Lotions</h2>
  </div>

  <?php if ($lotions->num_rows === 0): ?>
    <div style="text-align:center;padding:40px 20px;color:var(--text-soft);">
      <p>No body lotions found<?= $q ? ' for "' . htmlspecialchars($q) . '"' : '' ?>.</p>
    </div>
  <?php else: ?>
    <div class="product-grid">
      <?php while ($p = $lotions->fetch_assoc()): ?>
        <?= render_product_card($p) ?>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</section>

<?php include ROOT_PATH . 'includes/footer.php'; ?>