<?php
// --- user/orders.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/user_auth.php';

$conn    = get_db_connection();
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT id, total_amount, status, paystack_ref, created_at
     FROM orders WHERE user_id = ? ORDER BY created_at DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();

$page_title = 'My Orders — Glow Co.';
include ROOT_PATH . 'includes/header.php';
?>

<div class="user-dashboard">
  <p class="section-eyebrow">Order history</p>
  <h1>My Orders</h1>

  <?php if ($orders->num_rows === 0): ?>
    <div style="text-align:center;padding:80px 20px;background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow);margin-top:40px;">
      <div style="font-size:4rem;margin-bottom:16px;">📦</div>
      <h2 style="color:var(--plum);margin-bottom:8px;">No orders found</h2>
      <p style="color:var(--text-soft);margin-bottom:28px;">You haven't placed any orders yet.</p>
      <a href="<?= BASE_URL ?>pages/shop.php" class="btn-primary">Start shopping</a>
    </div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:20px;margin-top:40px;">
      <?php while ($order = $orders->fetch_assoc()): ?>
        <div class="order-card">
          <div class="order-card__header">
            <span>Order #<?= $order['id'] ?></span>
            <span class="status status--<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
            <span style="color:var(--text-soft);font-size:.82rem;"><?= date('M j, Y', strtotime($order['created_at'])) ?></span>
            <span>₦<?= number_format($order['total_amount'], 2) ?></span>
          </div>

          <?php
          $stmt2 = $conn->prepare(
              "SELECT p.name, oi.quantity, oi.price_at_purchase, p.image_path
               FROM order_items oi JOIN products p ON oi.product_id = p.id
               WHERE oi.order_id = ?"
          );
          $stmt2->bind_param("i", $order['id']);
          $stmt2->execute();
          $order_items = $stmt2->get_result();
          ?>
          <ul class="order-card__items">
            <?php while ($item = $order_items->fetch_assoc()): ?>
              <li>
                <?php if ($item['image_path']): ?>
                  <img src="<?= BASE_URL . htmlspecialchars($item['image_path']) ?>"
                       alt="<?= htmlspecialchars($item['name']) ?>">
                <?php endif; ?>
                <span><?= htmlspecialchars($item['name']) ?> &times;<?= $item['quantity'] ?></span>
                <span style="margin-left:auto;">₦<?= number_format($item['price_at_purchase'], 2) ?></span>
              </li>
            <?php endwhile; ?>
          </ul>

          <?php if ($order['paystack_ref']): ?>
            <div style="padding:12px 20px;font-size:.78rem;color:var(--text-soft);border-top:1px solid var(--pink-soft);">
              Ref: <?= htmlspecialchars($order['paystack_ref']) ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<?php include ROOT_PATH . 'includes/footer.php'; ?>