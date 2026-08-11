<?php
// --- user/dashboard.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/user_auth.php';

$conn    = get_db_connection();
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT name, email, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare(
    "SELECT id, total_amount, status, created_at FROM orders
     WHERE user_id = ? ORDER BY created_at DESC LIMIT 5"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_orders = $stmt->get_result();

$order_count = $conn->prepare("SELECT COUNT(*) AS c FROM orders WHERE user_id = ?");
$order_count->bind_param("i", $user_id);
$order_count->execute();
$total_orders = $order_count->get_result()->fetch_assoc()['c'];

$page_title = 'My Account — Glow Co.';
include ROOT_PATH . 'includes/header.php';
?>

<div class="user-dashboard">
  <p class="section-eyebrow">Your account</p>
  <h1>Welcome back, <?= htmlspecialchars(strtok($user['name'] ?? 'there', ' ') ?: 'there') ?>.</h1>

  <div style="display:grid;grid-template-columns:300px 1fr;gap:40px;margin-top:48px;align-items:start;">

    <div style="background:var(--white);border-radius:var(--radius-lg);padding:32px;box-shadow:var(--shadow);text-align:center;">
      <div style="width:72px;height:72px;background:var(--pink-soft);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:2rem;">
        <?= strtoupper(substr($user['name'] ?? 'G', 0, 1)) ?>
      </div>
      <h3 style="font-size:1.3rem;color:var(--plum);margin-bottom:4px;"><?= htmlspecialchars($user['name']) ?></h3>
      <p style="font-size:.85rem;color:var(--text-soft);margin-bottom:20px;"><?= htmlspecialchars($user['email']) ?></p>
      <p style="font-size:.78rem;color:var(--text-soft);">Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>

      <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--pink-soft);">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;">
          <span style="font-size:.85rem;color:var(--text-soft);">Total Orders</span>
          <span style="font-weight:700;color:var(--plum);font-family:var(--font-display);font-size:1.2rem;"><?= $total_orders ?></span>
        </div>
      </div>

      <div style="display:flex;flex-direction:column;gap:10px;margin-top:20px;">
        <a href="<?= BASE_URL ?>user/orders.php" class="btn-primary" style="text-align:center;">View All Orders</a>
        <a href="<?= BASE_URL ?>wishlist/view.php" class="btn-primary"
           style="background:transparent;border:1.5px solid var(--pink);color:var(--plum);text-align:center;">
          My Wishlist
        </a>
        <a href="<?= BASE_URL ?>auth/logout.php"
           style="display:block;font-size:.82rem;color:var(--text-soft);margin-top:4px;text-align:center;">
          Sign out
        </a>
      </div>
    </div>

    <div>
      <h2 style="font-size:1.5rem;color:var(--plum);margin-bottom:24px;">Recent Orders</h2>

      <?php if ($recent_orders->num_rows === 0): ?>
        <div style="background:var(--white);border-radius:var(--radius-lg);padding:48px 32px;box-shadow:var(--shadow);text-align:center;">
          <div style="font-size:3rem;margin-bottom:16px;">🛍️</div>
          <h3 style="color:var(--plum);margin-bottom:8px;">No orders yet</h3>
          <p style="color:var(--text-soft);margin-bottom:24px;">Start shopping and your orders will appear here.</p>
          <a href="<?= BASE_URL ?>pages/shop.php" class="btn-primary">Browse products</a>
        </div>
      <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:16px;">
          <?php while ($order = $recent_orders->fetch_assoc()): ?>
            <div class="order-card">
              <div class="order-card__header">
                <span>Order #<?= $order['id'] ?></span>
                <span class="status status--<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                <span style="color:var(--text-soft);font-size:.82rem;"><?= date('M j, Y', strtotime($order['created_at'])) ?></span>
                <span>₦<?= number_format($order['total_amount'], 2) ?></span>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
        <div style="margin-top:20px;text-align:right;">
          <a href="<?= BASE_URL ?>user/orders.php" style="font-size:.88rem;color:var(--pink-deep);font-weight:600;">
            View all orders →
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include ROOT_PATH . 'includes/footer.php'; ?>