<?php

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/admin_auth.php';

$conn = get_db_connection();

$total_products = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
$total_orders   = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
$total_users    = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'customer'")->fetch_assoc()['c'];
$revenue        = $conn->query("SELECT COALESCE(SUM(total_amount),0) AS r FROM orders WHERE status = 'paid'")->fetch_assoc()['r'];

$recent_orders = $conn->query(
    "SELECT o.id, u.name, o.total_amount, o.status, o.created_at
     FROM orders o JOIN users u ON o.user_id = u.id
     ORDER BY o.created_at DESC LIMIT 10"
);

$page_title = 'Dashboard — Glow Co. Admin';
include ROOT_PATH . 'includes/admin_header.php';
?>

<div class="admin-main">
  <h1>Dashboard</h1>

  <div class="stat-cards">
    <div class="stat-card">
      <h3>Products</h3>
      <p><?= $total_products ?></p>
    </div>
    <div class="stat-card">
      <h3>Orders</h3>
      <p><?= $total_orders ?></p>
    </div>
    <div class="stat-card">
      <h3>Customers</h3>
      <p><?= $total_users ?></p>
    </div>
    <div class="stat-card">
      <h3>Revenue</h3>
      <p style="font-size:1.4rem;">₦<?= number_format($revenue, 0) ?></p>
    </div>
  </div>

  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h2 style="font-size:1.3rem;color:var(--plum);">Recent Orders</h2>
    <a href="<?= BASE_URL ?>admin/orders.php" class="btn-primary" style="font-size:.82rem;padding:8px 18px;">View all</a>
  </div>

  <table class="admin-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Customer</th>
        <th>Total</th>
        <th>Status</th>
        <th>Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $recent_orders->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td>₦<?= number_format($row['total_amount'], 2) ?></td>
          <td><span class="status status--<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
          <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
          <td><a href="update_status.php?id=<?= $row['id'] ?>">Update</a></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <div style="display:flex;gap:16px;margin-top:32px;">
    <a href="<?= BASE_URL ?>admin/add_product.php" class="btn-primary">+ Add Product</a>
    <a href="<?= BASE_URL ?>admin/products.php" class="btn-primary"
       style="background:transparent;border:1.5px solid var(--plum);color:var(--plum);">Manage Products</a>
  </div>
</div>

<?php include ROOT_PATH . 'includes/admin_footer.php'; ?>