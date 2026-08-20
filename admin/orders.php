<?php

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/admin_auth.php';

$conn           = get_db_connection();
$valid_statuses = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
$status_filter  = sanitize_input($_GET['status'] ?? '');

if ($status_filter && in_array($status_filter, $valid_statuses)) {
    $stmt = $conn->prepare(
        "SELECT o.id, u.name, u.email, o.total_amount, o.status, o.payment_ref, o.created_at
         FROM orders o JOIN users u ON o.user_id = u.id
         WHERE o.status = ? ORDER BY o.created_at DESC"
    );
    $stmt->bind_param("s", $status_filter);
    $stmt->execute();
    $orders = $stmt->get_result();
} else {
    $orders = $conn->query(
        "SELECT o.id, u.name, u.email, o.total_amount, o.status, o.payment_ref, o.created_at
         FROM orders o JOIN users u ON o.user_id = u.id
         ORDER BY o.created_at DESC"
    );
}

$page_title = 'Orders — Glow Co. Admin';
include ROOT_PATH . 'includes/admin_header.php';
?>

<div class="admin-main">
  <h1>Orders</h1>

  <div class="filter-bar" style="margin-bottom:24px;">
    <a href="orders.php" class="<?= !$status_filter ? 'active' : '' ?>">All</a>
    <?php foreach ($valid_statuses as $s): ?>
      <a href="?status=<?= $s ?>" class="<?= $status_filter === $s ? 'active' : '' ?>">
        <?= ucfirst($s) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <table class="admin-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Customer</th>
        <th>Email</th>
        <th>Total</th>
        <th>Status</th>
        <th>Reference</th>
        <th>Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($orders->num_rows === 0): ?>
        <tr>
          <td colspan="8" style="text-align:center;padding:40px;color:var(--text-soft);">
            No orders found<?= $status_filter ? ' with status "' . $status_filter . '"' : '' ?>.
          </td>
        </tr>
      <?php else: ?>
        <?php while ($row = $orders->fetch_assoc()): ?>
          <tr>
            <td style="font-weight:700;">#<?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td style="color:var(--text-soft);font-size:.82rem;"><?= htmlspecialchars($row['email']) ?></td>
            <td style="font-weight:600;">₦<?= number_format($row['total_amount'], 0) ?></td>
            <td><span class="status status--<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
            <td style="color:var(--text-soft);font-size:.78rem;"><?= htmlspecialchars($row['payment_ref'] ?? '—') ?></td>
            <td style="font-size:.82rem;"><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
            <td>
              <a href="order_detail.php?id=<?= $row['id'] ?>">View</a>
              <a href="update_status.php?id=<?= $row['id'] ?>">Status</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include ROOT_PATH . 'includes/admin_footer.php'; ?>