<?php
// --- pages/shop.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/config.php';
secure_session_start();

$conn = get_db_connection();
$sort     = sanitize_input($_GET['sort'] ?? 'newest');
$q        = sanitize_input($_GET['q'] ?? '');
$category = sanitize_input($_GET['category'] ?? '');

$allowed_sorts = [
    'newest'     => 'created_at DESC',
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
    'name'       => 'name ASC',
];
$order_clause = $allowed_sorts[$sort] ?? 'created_at DESC';

$SAMPLE_LIMIT = 3;

// Determine which categories to show
$show_perfumes = ($category === '' || $category === 'Perfume');
$show_lotions  = ($category === '' || $category === 'Body Lotion');

function build_query($conn, $category, $like, $order_clause, $limit) {
    $where = $like !== null
        ? "AND (name LIKE ? OR description LIKE ?)"
        : "";
    $sql = "SELECT id, name, price, image_path, stock, description
            FROM products
            WHERE category = ? {$where}
            ORDER BY {$order_clause}";
    if ($limit) $sql .= " LIMIT " . (int)$limit;

    $stmt = $conn->prepare($sql);
    if ($like !== null) {
        $stmt->bind_param("sss", $category, $like, $like);
    } else {
        $stmt->bind_param("s", $category);
    }
    $stmt->execute();
    return $stmt->get_result();
}

$like = ($q !== '') ? '%' . $q . '%' : null;

$perfumes      = $show_perfumes ? build_query($conn, 'Perfume',     $like, $order_clause, $category ? null : $SAMPLE_LIMIT) : null;
$perfume_total = 0;
if ($show_perfumes && $perfumes) $perfume_total = $perfumes->num_rows;

$lotions      = $show_lotions ? build_query($conn, 'Body Lotion', $like, $order_clause, $category ? null : $SAMPLE_LIMIT) : null;
$lotion_total = 0;
if ($show_lotions && $lotions) $lotion_total = $lotions->num_rows;

// Count total in each category (for "view all" buttons)
$count_perfumes = $conn->query("SELECT COUNT(*) AS c FROM products WHERE category = 'Perfume'")->fetch_assoc()['c'];
$count_lotions  = $conn->query("SELECT COUNT(*) AS c FROM products WHERE category = 'Body Lotion'")->fetch_assoc()['c'];

$page_title = 'Shop — Glow Co.';
include ROOT_PATH . 'includes/header.php';
$csrf = generate_csrf_token();
?>

<section class="shop-hero">
  <p class="section-eyebrow">The collection</p>
  <h1>Shop <em>everything.</em></h1>
  <p>Premium body creams, perfumes &amp; lotions made with natural butters and botanical oils.</p>

  <!-- Floating Search Bar -->
  <div class="floating-search" id="floatingSearch">
    <div class="floating-search__inner">
      <svg class="floating-search__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
      </svg>
      <input type="text" id="floatingSearchInput" class="floating-search__input"
             placeholder="Search perfumes, lotions..."
             autocomplete="off" autofocus>
      <button type="button" class="floating-search__clear" id="searchClear"
              style="display:none;" aria-label="Clear search">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
      <button type="button" class="floating-search__submit" id="searchSubmit">Go</button>
    </div>
    <div class="floating-search__dropdown" id="searchDropdown"></div>
  </div>

  <form method="GET" style="display:flex;justify-content:center;margin-top:16px;">
    <?php if ($category): ?>
      <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
    <?php endif; ?>
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

<?php if ($show_perfumes): ?>
<!-- ── PERFUMES SECTION ───────────────────────────────────────────── -->
<section class="products-section">
  <div class="category-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <h2>Perfumes</h2>
    <?php if (!$category): ?>
      <a href="<?= BASE_URL ?>pages/shop.php?category=Perfume<?= $sort !== 'newest' ? '&sort=' . urlencode($sort) : '' ?>"
         class="btn-primary" style="font-size:.82rem;padding:10px 24px;">
        Shop Perfumes (<?= $count_perfumes ?>)
      </a>
    <?php endif; ?>
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
    <?php if ($category === 'Perfume'): ?>
      <div style="text-align:center;margin-top:32px;">
        <a href="<?= BASE_URL ?>pages/shop.php<?= $sort !== 'newest' ? '?sort=' . urlencode($sort) : '' ?>"
           class="btn-primary" style="background:transparent;border:1.5px solid var(--plum);color:var(--plum);">
          ← Back to All Products
        </a>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($show_lotions): ?>
<!-- ── BODY LOTIONS SECTION ──────────────────────────────────────── -->
<section class="products-section" style="padding-top:0;">
  <div class="category-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <h2>Body Lotions</h2>
    <?php if (!$category): ?>
      <a href="<?= BASE_URL ?>pages/shop.php?category=Body+Lotion<?= $sort !== 'newest' ? '&sort=' . urlencode($sort) : '' ?>"
         class="btn-primary" style="font-size:.82rem;padding:10px 24px;">
        Shop Body Lotions (<?= $count_lotions ?>)
      </a>
    <?php endif; ?>
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
    <?php if ($category === 'Body Lotion'): ?>
      <div style="text-align:center;margin-top:32px;">
        <a href="<?= BASE_URL ?>pages/shop.php<?= $sort !== 'newest' ? '?sort=' . urlencode($sort) : '' ?>"
           class="btn-primary" style="background:transparent;border:1.5px solid var(--plum);color:var(--plum);">
          ← Back to All Products
        </a>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</section>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const input     = document.getElementById('floatingSearchInput');
  const clear     = document.getElementById('searchClear');
  const submit    = document.getElementById('searchSubmit');
  const dropdown  = document.getElementById('searchDropdown');
  const base      = '<?= BASE_URL ?>';
  let debounce    = null;

  input.addEventListener('input', () => {
    clear.style.display = input.value.length ? 'flex' : 'none';
    clearTimeout(debounce);
    const q = input.value.trim();
    if (q.length < 2) { dropdown.classList.remove('active'); return; }
    debounce = setTimeout(() => fetchResults(q), 250);
  });

  clear.addEventListener('click', () => {
    input.value = '';
    clear.style.display = 'none';
    dropdown.classList.remove('active');
    input.focus();
  });

  submit.addEventListener('click', () => {
    const q = input.value.trim();
    if (q) window.location.href = base + 'pages/search.php?q=' + encodeURIComponent(q);
  });

  input.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
      const q = input.value.trim();
      if (q) window.location.href = base + 'pages/search.php?q=' + encodeURIComponent(q);
    }
  });

  document.addEventListener('click', e => {
    if (!e.target.closest('.floating-search')) dropdown.classList.remove('active');
  });

  function fetchResults(q) {
    fetch(base + 'pages/search_ajax.php?q=' + encodeURIComponent(q))
      .then(r => r.json())
      .then(data => renderDropdown(data, q))
      .catch(() => { dropdown.classList.remove('active'); });
  }

  function esc(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
  }

  function renderDropdown(data, q) {
    if (!data.results || !data.results.length) {
      dropdown.innerHTML = '<div class="search-dropdown__empty">No products found for "' + esc(q) + '"</div>';
      dropdown.classList.add('active');
      return;
    }
    let html = '';
    data.results.forEach(p => {
      const img = p.image
        ? '<img class="search-result__img" src="' + esc(p.image) + '" alt="' + esc(p.name) + '">'
        : '<div class="search-result__img search-result__img--placeholder">🧴</div>';
      const stockLabel = p.stock > 0
        ? '<span class="search-result__stock search-result__stock--in">In Stock</span>'
        : '<span class="search-result__stock search-result__stock--out">Out of Stock</span>';
      html += '<a class="search-result" href="' + esc(p.url) + '">'
            + img
            + '<div class="search-result__info">'
            +   '<div class="search-result__name">' + esc(p.name) + '</div>'
            +   '<div class="search-result__meta">' + esc(p.category) + '</div>'
            + '</div>'
            + '<span class="search-result__price">' + esc(p.price) + '</span>'
            + stockLabel
            + '</a>';
    });
    if (data.total > data.results.length) {
      html += '<a class="search-dropdown__footer" href="' + base + 'pages/search.php?q=' + encodeURIComponent(q) + '">'
            + 'View all ' + data.total + ' results →</a>';
    }
    dropdown.innerHTML = html;
    dropdown.classList.add('active');
  }
});
</script>

<?php include ROOT_PATH . 'includes/footer.php'; ?>
