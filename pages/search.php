<?php
// --- pages/search.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/config.php';
session_start();

$q    = sanitize_input($_GET['q'] ?? '');
$conn = get_db_connection();

$results = [];
if ($q !== '') {
    $like = '%' . $conn->real_escape_string($q) . '%';
    $stmt = $conn->prepare(
        "SELECT id, name, price, image_path, stock FROM products
         WHERE name LIKE ? OR description LIKE ? OR category LIKE ?
         ORDER BY name ASC"
    );
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $results = $stmt->get_result();
}

$page_title = 'Search - Glowco';
include ROOT_PATH . 'includes/header.php';
$csrf = generate_csrf_token();
?>
<main>
    <h1>Search Results<?= $q ? ' for "' . htmlspecialchars($q) . '"' : '' ?></h1>

    <form method="GET" action="search.php" class="search-form">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search products">
        <button type="submit">Search</button>
    </form>

    <?php if ($q === ''): ?>
        <p>Enter a search term above.</p>
    <?php elseif ($results->num_rows === 0): ?>
        <p>No products found for "<?= htmlspecialchars($q) ?>".</p>
    <?php else: ?>
        <div class="product-grid">
        <?php while ($p = $results->fetch_assoc()): ?>
            <div class="product-card">
                <?php if ($p['image_path']): ?>
                    <a href="product.php?id=<?= $p['id'] ?>">
                        <img src="<?= BASE_URL . htmlspecialchars($p['image_path']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                    </a>
                <?php endif; ?>
                <h3><a href="product.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a></h3>
                <p>₦<?= number_format($p['price'], 2) ?></p>
                <?php if ($p['stock'] > 0): ?>
                    <form method="POST" action="<?= BASE_URL ?>cart/add.php">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit">Add to Cart</button>
                    </form>
                <?php else: ?>
                    <span class="out-of-stock">Out of Stock</span>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
        </div>
    <?php endif; ?>
</main>
<?php include ROOT_PATH . 'includes/footer.php'; ?>