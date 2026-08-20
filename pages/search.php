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

  <form method="GET" action="search.php" style="max-width:480px;margin:24px auto 0;display:flex;background:var(--white);border-radius:50px;overflow:hidden;border:1.5px solid var(--pink-soft);box-shadow:var(--shadow);">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search products..."
           style="flex:1;border:none;outline:none;padding:14px 20px;font-size:.9rem;font-family:var(--font-body);background:transparent;color:var(--text);">
    <button type="submit" style="background:var(--pink-deep);color:var(--white);border:none;padding:14px 22px;font-size:.9rem;font-weight:500;cursor:pointer;">Search</button>
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
        <?php include ROOT_PATH . 'includes/product_card.php'; ?>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</section>

<?php include ROOT_PATH . 'includes/footer.php'; ?>
