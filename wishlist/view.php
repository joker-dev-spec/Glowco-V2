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
        <?php
          $p = $row;
          $p['id'] = $row['product_id'];
          $GLOBALS['show_wishlist'] = false;
          $GLOBALS['card_extra_html'] = '
            <form method="POST" action="' . BASE_URL . 'wishlist/remove.php"
                  style="display:block;margin-top:8px;" data-inline>
              <input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf) . '">
              <input type="hidden" name="id" value="' . (int)$row['wishlist_id'] . '">
              <button type="submit"
                      style="background:none;border:none;padding:0;font-size:.75rem;color:var(--pink-deep);font-weight:600;text-decoration:underline;cursor:pointer;">
                Remove
              </button>
            </form>';
          include ROOT_PATH . 'includes/product_card.php';
        ?>
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