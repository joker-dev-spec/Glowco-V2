<?php

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/admin_auth.php';

$id   = (int)($_GET['id'] ?? 0);
$conn = get_db_connection();

$stmt = $conn->prepare("SELECT id, status, total_amount, payment_ref FROM orders WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    set_flash('error', 'Order not found.');
    header("Location: orders.php");
    exit();
}

$valid_statuses = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) die("Invalid request.");
    $new_status = $_POST['status'] ?? '';

    if (!in_array($new_status, $valid_statuses)) {
        $error = "Invalid status selected.";
    } else {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $id);
        if ($stmt->execute()) {
            if ($new_status === 'paid' && $order['status'] !== 'paid') {
                $stmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $items = $stmt->get_result();
                $dec   = $conn->prepare("UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?");
                while ($it = $items->fetch_assoc()) {
                    $dec->bind_param("ii", $it['quantity'], $it['product_id']);
                    $dec->execute();
                }
            }
            set_flash('success', "Order #$id updated to " . ucfirst($new_status) . ".");
            header("Location: orders.php");
            exit();
        }
        $error = "Update failed.";
    }
}

$page_title = "Update Order #$id — Glow Co. Admin";
include ROOT_PATH . 'includes/admin_header.php';
?>

<div class="admin-main">
  <h1>Update Order #<?= $id ?></h1>
  <a href="<?= BASE_URL ?>admin/orders.php" style="font-size:.85rem;color:var(--text-soft);display:inline-block;margin-bottom:24px;">← Back to orders</a>

  <div class="admin-form">
    <div style="background:var(--pink-soft);border-radius:var(--radius);padding:20px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
      <div>
        <p style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--text-soft);margin-bottom:4px;">Order Total</p>
        <p style="font-family:var(--font-display);font-size:1.5rem;font-weight:600;color:var(--plum);">₦<?= number_format($order['total_amount'], 2) ?></p>
      </div>
      <div style="text-align:right;">
        <p style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--text-soft);margin-bottom:4px;">Current Status</p>
        <span class="status status--<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
        <?php if (!empty($order['payment_ref'])): ?>
          <p style="font-size:.75rem;color:var(--text-soft);margin-top:8px;">Ref: <?= htmlspecialchars($order['payment_ref']) ?></p>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($error): ?>
      <div class="flash flash--error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <div>
        <label>New Status</label>
        <select name="status">
          <?php foreach ($valid_statuses as $s): ?>
            <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>>
              <?= ucfirst($s) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:flex;gap:12px;margin-top:8px;">
        <button type="submit" class="btn-primary" style="flex:1;">Update Status</button>
        <a href="orders.php" class="btn-primary"
           style="flex:1;text-align:center;background:transparent;border:1.5px solid var(--pink);color:var(--plum);">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>

<?php include ROOT_PATH . 'includes/admin_footer.php'; ?>