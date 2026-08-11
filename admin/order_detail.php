<?php

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/admin_auth.php';

$id   = (int)($_GET['id'] ?? 0);
$conn = get_db_connection();

$stmt = $conn->prepare(
    "SELECT o.*, u.name AS customer, u.email
     FROM orders o JOIN users u ON o.user_id = u.id
     WHERE o.id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    set_flash('error', 'Order not found.');
    header("Location: orders.php");
    exit();
}

$stmt = $conn->prepare(
    "SELECT p.name, oi.quantity, oi.price_at_purchase, p.image_path
     FROM order_items oi JOIN products p ON oi.product_id = p.id
     WHERE oi.order_id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$items = $stmt->get_result();

$page_title = "Order #{$id} — Glow Co. Admin";
include ROOT_PATH . 'includes/admin_header.php';
?>

<div class="admin-main">
  <h1>Order #<?= $id ?></h1>
  <a href="<?= BASE_URL ?>admin/orders.php" style="font-size:.85rem;color:var(--text-soft);display:inline-block;margin-bottom:24px;">← Back to orders</a>

  <div class="admin-order-grid" style="margin-bottom:24px;">
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:24px;box-shadow:var(--shadow);">
      <h2 style="font-size:1.1rem;color:var(--plum);margin-bottom:16px;">Order</h2>
      <div class="summary-row"><span>Status</span><span class="status status--<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></div>
      <div class="summary-row"><span>Total</span><span style="font-weight:700;color:var(--plum);">₦<?= number_format($order['total_amount'], 2) ?></span></div>
      <div class="summary-row"><span>Date</span><span><?= date('M j, Y g:ia', strtotime($order['created_at'])) ?></span></div>
      <div class="summary-row"><span>Payment Ref</span><span style="font-size:.78rem;"><?= htmlspecialchars($order['payment_ref'] ?? '—') ?></span></div>
      <div style="margin-top:20px;">
        <a href="<?= BASE_URL ?>admin/update_status.php?id=<?= $id ?>" class="btn-primary" style="font-size:.82rem;padding:10px 18px;">Update Status</a>
      </div>
    </div>

    <div style="background:var(--white);border-radius:var(--radius-lg);padding:24px;box-shadow:var(--shadow);">
      <h2 style="font-size:1.1rem;color:var(--plum);margin-bottom:16px;">Customer &amp; Delivery</h2>
      <div class="summary-row"><span>Customer</span><span style="font-weight:600;color:var(--plum);"><?= htmlspecialchars($order['customer']) ?></span></div>
      <div class="summary-row"><span>Email</span><span><?= htmlspecialchars($order['email']) ?></span></div>
      <div class="summary-row"><span>Phone</span><span><?= htmlspecialchars($order['shipping_phone'] ?? '—') ?></span></div>
      <div class="summary-row"><span>Address</span><span><?= htmlspecialchars($order['shipping_address'] ?? '—') ?></span></div>
      <div class="summary-row"><span>City / State</span><span><?= htmlspecialchars(trim(($order['shipping_city'] ?? '') . ' ' . ($order['shipping_state'] ?? '')) ?: '—') ?></span></div>
    </div>
  </div>

  <table class="admin-table">
    <thead>
      <tr>
        <th>Item</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($item = $items->fetch_assoc()): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:12px;">
              <?php if ($item['image_path']): ?>
                <img src="<?= BASE_URL . htmlspecialchars($item['image_path']) ?>" alt=""
                     style="width:40px;height:40px;object-fit:cover;border-radius:8px;background:var(--pink-soft);">
              <?php endif; ?>
              <?= htmlspecialchars($item['name']) ?>
            </div>
          </td>
          <td><?= (int)$item['quantity'] ?></td>
          <td>₦<?= number_format($item['price_at_purchase'], 2) ?></td>
          <td style="font-weight:600;">₦<?= number_format($item['price_at_purchase'] * $item['quantity'], 2) ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include ROOT_PATH . 'includes/admin_footer.php'; ?>
