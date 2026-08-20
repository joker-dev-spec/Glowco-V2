<?php
// --- wishlist/view.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/user_auth.php';

$conn    = get_db_connection();
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT w.id AS wishlist_id, p.id AS product_id, p.name, p.price, p.image_path, p.stock, p.description
     FROM wishlist w
     JOIN products p ON w.product_id = p.id
     WHERE w.user_id = ?
     ORDER BY w.added_at DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$items = $stmt->get_result();
$rows  = $items->fetch_all(MYSQLI_ASSOC);

$page_title = 'My Wishlist — Glow Co.';
include ROOT_PATH . 'includes/header.php';
$csrf = generate_csrf_token();
?>

<div class="wishlist-page">
  <p class="section-eyebrow">Saved for later</p>
  <h1>Your Wishlist</h1>
  <?php if (!empty($rows)): ?>
    <p class="sub"><?= count($rows) ?> saved item<?= count($rows) > 1 ? 's' : '' ?></p>
  <?php endif; ?>

  <?php if (empty($rows)): ?>
    <div class="empty-wish">
      <div style="font-size:4rem;margin-bottom:16px;">🤍</div>
      <h2>Your wishlist is empty</h2>
      <p style="margin-bottom:28px;font-size:.95rem;">Tap the ♡ on any product to save it here.</p>
      <a href="<?= BASE_URL ?>pages/shop.php" class="btn-primary">Browse products</a>
    </div>
  <?php else: ?>
    <div class="product-grid" style="margin-top:32px;">
      <?php foreach ($rows as $row): ?>
        <div class="product-card">
          <div class="product-img-wrap">
            <?php if ($row['image_path']): ?>
              <a href="<?= BASE_URL ?>pages/product.php?id=<?= $row['product_id'] ?>">
                <img src="<?= BASE_URL . htmlspecialchars($row['image_path']) ?>"
                     alt="<?= htmlspecialchars($row['name']) ?>"
                     onerror="this.style.background='var(--pink-soft)';this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2280%22 height=%2280%22%3E%3Crect fill=%22%23FDE0E8%22 width=%2280%22 height=%2280%22/%3E%3Ctext x=%2250%25%22 y=%2255%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-size=%2228%22%3E🧴%3C/text%3E%3C/svg%3E'">
              </a>
            <?php else: ?>
              <div style="width:100%;height:100%;background:var(--pink-soft);display:flex;align-items:center;justify-content:center;font-size:3rem;">🧴</div>
            <?php endif; ?>
          </div>
          <div class="product-info">
            <h3><a href="<?= BASE_URL ?>pages/product.php?id=<?= $row['product_id'] ?>"
                   style="color:var(--plum);"><?= htmlspecialchars($row['name']) ?></a></h3>
            <?php if (!empty($row['description'])): ?>
              <p class="product-desc"><?= htmlspecialchars(substr($row['description'], 0, 70)) ?>...</p>
            <?php endif; ?>
            <div class="product-footer">
              <span class="product-price">₦<?= number_format($row['price'], 0) ?></span>
              <div class="product-actions">
                <?php if ($row['stock'] > 0): ?>
                  <form method="POST" action="<?= BASE_URL ?>cart/add.php" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
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
            <form method="POST" action="<?= BASE_URL ?>wishlist/remove.php"
                  style="display:block;margin-top:10px;" data-inline>
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="id" value="<?= (int)$row['wishlist_id'] ?>">
              <button type="submit"
                      style="background:none;border:none;padding:0;font-size:.78rem;color:var(--pink-deep);font-weight:600;text-decoration:underline;">
                Remove
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div style="margin-top:32px;text-align:right;">
      <a href="<?= BASE_URL ?>pages/shop.php" class="btn-primary"
         style="background:transparent;border:1.5px solid var(--plum);color:var(--plum);">
        Continue Shopping
      </a>
    </div>
  <?php endif; ?>
</div>

<?php include ROOT_PATH . 'includes/footer.php'; ?>