<?php
// --- cart/cart.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/user_auth.php';

$conn    = get_db_connection();
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT ci.id, ci.quantity, p.id AS product_id, p.name, p.price, p.image_path
     FROM cart_items ci
     JOIN products p ON ci.product_id = p.id
     WHERE ci.user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$items = $stmt->get_result();

$total = 0;
$rows  = [];
while ($row = $items->fetch_assoc()) {
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total += $row['subtotal'];
    $rows[] = $row;
}

$page_title = 'Your Cart — Glow Co.';
include ROOT_PATH . 'includes/header.php';
?>

<div class="cart-page">
  <p class="section-eyebrow">Shopping bag</p>
  <h1>Your Cart</h1>

  <?php if (empty($rows)): ?>
    <div class="empty-cart" style="padding:80px 20px;text-align:center;">
      <div style="font-size:4rem;margin-bottom:16px;">🛍️</div>
      <h2>Your cart is empty</h2>
      <p style="margin-bottom:28px;color:var(--text-soft);">Looks like you haven't added anything yet.</p>
      <a href="<?= BASE_URL ?>pages/shop.php" class="btn-primary">Browse products</a>
    </div>
  <?php else: ?>
    <div class="cart-layout">
      <div>
        <div class="cart-items-list">
          <?php foreach ($rows as $row): ?>
            <div class="cart-item">
              <img src="<?= $row['image_path'] ? BASE_URL . htmlspecialchars($row['image_path']) : '' ?>"
                   alt="<?= htmlspecialchars($row['name']) ?>"
                   onerror="this.style.background='var(--pink-soft)';this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2280%22 height=%2280%22%3E%3Crect fill=%22%23FDE0E8%22 width=%2280%22 height=%2280%22/%3E%3Ctext x=%2250%25%22 y=%2255%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-size=%2228%22%3E🧴%3C/text%3E%3C/svg%3E'">
              <div class="cart-item-info">
                <h3><?= htmlspecialchars($row['name']) ?></h3>
                <p>₦<?= number_format($row['price'], 2) ?> each</p>
              </div>
              <div class="qty-controls">
                <span class="qty-display">Qty: <?= $row['quantity'] ?></span>
              </div>
              <div style="font-weight:600;color:var(--plum);min-width:90px;text-align:right;">
                ₦<?= number_format($row['subtotal'], 2) ?>
              </div>
              <a href="<?= BASE_URL ?>cart/remove.php?id=<?= $row['id'] ?>"
                 class="remove-btn" title="Remove">✕</a>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if ($total < 15000): ?>
          <div style="margin-top:20px;padding:16px 20px;background:var(--pink-soft);border-radius:var(--radius);font-size:.88rem;color:var(--plum);">
            🚚 Add ₦<?= number_format(15000 - $total, 2) ?> more for free shipping
          </div>
        <?php else: ?>
          <div style="margin-top:20px;padding:16px 20px;background:rgba(92,184,92,.1);border-radius:var(--radius);font-size:.88rem;color:#2e7d32;">
            🎉 You've unlocked free shipping!
          </div>
        <?php endif; ?>
      </div>

      <div class="cart-summary">
        <h2>Order Summary</h2>
        <div class="summary-row">
          <span>Subtotal</span>
          <span>₦<?= number_format($total, 2) ?></span>
        </div>
        <div class="summary-row">
          <span>Shipping</span>
          <span style="color:<?= $total >= 15000 ? '#2e7d32' : 'var(--text-soft)' ?>">
            <?= $total >= 15000 ? 'Free' : '₦1,500' ?>
          </span>
        </div>
        <div class="summary-row total">
          <span>Total</span>
          <span>₦<?= number_format($total >= 15000 ? $total : $total + 1500, 2) ?></span>
        </div>

        <a href="<?= BASE_URL ?>cart/checkout.php" class="btn-primary"
           style="display:block;text-align:center;margin-top:20px;">
          Proceed to Checkout
        </a>
        <a href="<?= BASE_URL ?>pages/shop.php"
           style="display:block;text-align:center;margin-top:12px;font-size:.85rem;color:var(--text-soft);">
          Continue shopping
        </a>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php include ROOT_PATH . 'includes/footer.php'; ?>