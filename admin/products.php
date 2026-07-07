<?php
// --- admin/products.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/admin_auth.php';

$conn   = get_db_connection();
$result = $conn->query(
    "SELECT id, name, price, stock, category, image_path FROM products ORDER BY created_at DESC"
);

$page_title = 'Products — Glow Co. Admin';
include ROOT_PATH . 'includes/admin_header.php';
?>

<div class="admin-main">
  <h1>Products</h1>
  <a href="<?= BASE_URL ?>admin/add_product.php" class="btn-primary">+ Add Product</a>

  <table class="admin-table" style="margin-top:24px;">
    <thead>
      <tr>
        <th>Image</th>
        <th>Name</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Category</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td>
            <?php if ($row['image_path']): ?>
              <img src="<?= BASE_URL . htmlspecialchars($row['image_path']) ?>"
                   alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px;display:block;">
            <?php else: ?>
              <div style="width:48px;height:48px;background:var(--pink-soft);border-radius:8px;display:flex;align-items:center;justify-content:center;">🧴</div>
            <?php endif; ?>
          </td>
          <td style="font-weight:600;color:var(--plum);"><?= htmlspecialchars($row['name']) ?></td>
          <td>₦<?= number_format($row['price'], 2) ?></td>
          <td>
            <span style="color:<?= $row['stock'] > 0 ? '#2e7d32' : '#c62828' ?>;font-weight:600;">
              <?= $row['stock'] ?>
            </span>
          </td>
          <td><?= htmlspecialchars($row['category'] ?? '—') ?></td>
          <td>
            <a href="edit_product.php?id=<?= $row['id'] ?>">Edit</a>
            <a href="delete_product.php?id=<?= $row['id'] ?>"
               onclick="return confirm('Delete <?= htmlspecialchars($row['name']) ?>? This cannot be undone.')"
               style="color:#c62828;border-color:#c62828;">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include ROOT_PATH . 'includes/admin_footer.php'; ?>