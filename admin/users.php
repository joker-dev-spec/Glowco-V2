<?php

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/admin_auth.php';

$conn = get_db_connection();

$users = $conn->query(
    "SELECT u.id, u.name, u.email, u.role, u.created_at,
            (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count
     FROM users u
     ORDER BY u.created_at DESC"
);

$page_title = 'Users — Glow Co. Admin';
include ROOT_PATH . 'includes/admin_header.php';
?>

<div class="admin-main">
  <h1>Customers</h1>

  <table class="admin-table" style="margin-top:20px;">
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Orders</th>
        <th>Joined</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php while ($u = $users->fetch_assoc()): ?>
        <tr>
          <td style="font-weight:600;color:var(--plum);"><?= htmlspecialchars($u['name']) ?></td>
          <td style="color:var(--text-soft);font-size:.85rem;"><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="status <?= $u['role'] === 'admin' ? 'status--paid' : 'status--pending' ?>"><?= ucfirst($u['role']) ?></span></td>
          <td><?= (int)$u['order_count'] ?></td>
          <td style="font-size:.82rem;"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
          <td>
            <?php if ($u['role'] === 'customer'): ?>
              <a href="reset_user_password.php?id=<?= (int)$u['id'] ?>" style="font-size:.78rem;color:var(--pink-deep);font-weight:600;">Reset password</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include ROOT_PATH . 'includes/admin_footer.php'; ?>
