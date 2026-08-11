<?php
// --- cart/pay.php ---

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/user_auth.php';

$conn     = get_db_connection();
$user_id  = $_SESSION['user_id'];
$order_id = (int)($_GET['order_id'] ?? 0);

$stmt = $conn->prepare(
    "SELECT id, total_amount, status, payment_ref, created_at
     FROM orders WHERE id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: " . BASE_URL . "user/orders.php");
    exit();
}

$done_statuses = ['paid', 'shipped', 'delivered'];
if (in_array($order['status'], $done_statuses, true)) {
    header("Location: " . BASE_URL . "user/orders.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) die("Invalid request.");

    $ref = trim(sanitize_input($_POST['reference'] ?? ''));

    if ($ref === '' || strlen($ref) > 100) {
        $error = 'Please enter the transaction reference from your transfer receipt.';
    } else {
        $stmt = $conn->prepare("UPDATE orders SET payment_ref = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $ref, $order_id, $user_id);
        $stmt->execute();

        set_flash('success', "Payment reference received for Order #{$order_id}. We'll verify it and mark your order as paid shortly.");
        header("Location: " . BASE_URL . "user/orders.php");
        exit();
    }
}

$page_title = 'Pay for Order — Glow Co.';
include ROOT_PATH . 'includes/header.php';
$csrf = generate_csrf_token();
?>

<section class="page-hero" style="text-align:left;">
  <p class="section-eyebrow">Transfer payment</p>
</section>

<div class="cart-page" style="padding-top:0;">
  <div class="cart-layout">
    <div>
      <h1>Pay for Order #<?= (int)$order['id'] ?></h1>
      <p class="sub">Transfer the exact total to any of these accounts, then enter the transaction reference from your receipt.</p>

      <div style="display:flex;flex-direction:column;gap:16px;margin-top:28px;max-width:520px;">
        <?php foreach (get_bank_accounts() as $acct): ?>
          <div style="background:var(--white);border:1px solid var(--pink-soft);border-radius:var(--radius-lg);padding:20px 24px;box-shadow:var(--shadow);">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
              <div>
                <p style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--text-soft);margin-bottom:4px;"><?= htmlspecialchars($acct['bank']) ?></p>
                <p style="font-family:var(--font-display);font-size:1.25rem;font-weight:600;color:var(--plum);letter-spacing:.04em;"><?= htmlspecialchars($acct['number']) ?></p>
                <p style="color:var(--text-soft);font-size:.85rem;"><?= htmlspecialchars($acct['name']) ?></p>
              </div>
              <button type="button" class="qty-btn"
                      title="Copy account number"
                      onclick="navigator.clipboard.writeText(this.dataset.num).then(()=>{this.textContent='✓';setTimeout(()=>this.textContent='⧉',1500)})"
                      data-num="<?= htmlspecialchars($acct['number']) ?>"
                      style="width:auto;padding:8px 16px;border-radius:30px;">⧉ Copy</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div style="background:var(--pink-soft);border-radius:var(--radius);padding:18px 22px;margin-top:20px;max-width:520px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
        <span style="color:var(--plum);font-weight:600;">Amount to transfer</span>
        <span style="font-family:var(--font-display);font-size:1.4rem;font-weight:600;color:var(--plum);">₦<?= number_format($order['total_amount'], 2) ?></span>
      </div>

      <?php if ($order['payment_ref']): ?>
        <div class="flash flash--info" style="margin-top:20px;max-width:520px;">
          Reference submitted: <strong><?= htmlspecialchars($order['payment_ref']) ?></strong> — we'll confirm it shortly.
        </div>
      <?php else: ?>
        <form method="POST" style="max-width:520px;margin-top:28px;">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

          <?php if ($error): ?>
            <div class="flash flash--error"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <div>
            <label>Transaction Reference *</label>
            <input type="text" name="reference" required maxlength="100"
                   value="<?= htmlspecialchars($_POST['reference'] ?? '') ?>"
                   placeholder="e.g. 1143124586 or the reference on your receipt">
            <p style="font-size:.78rem;color:var(--text-soft);margin-top:6px;">Found on your transfer receipt or bank alert.</p>
          </div>

          <button type="submit" class="btn-primary" style="width:100%;margin-top:8px;">
            I Have Transferred — Confirm Payment
          </button>
        </form>
      <?php endif; ?>
    </div>

    <div class="cart-summary">
      <h2>What happens next?</h2>
      <div style="display:flex;flex-direction:column;gap:16px;color:var(--text-soft);font-size:.88rem;line-height:1.6;">
        <div><strong style="color:var(--plum);">1.</strong> Transfer the total to OPay or Access Bank above.</div>
        <div><strong style="color:var(--plum);">2.</strong> Enter the reference from your receipt.</div>
        <div><strong style="color:var(--plum);">3.</strong> We verify your transfer and mark the order paid.</div>
        <div><strong style="color:var(--plum);">4.</strong> We ship within 24 hours of confirmation.</div>
      </div>
      <a href="<?= BASE_URL ?>user/orders.php"
         style="display:block;text-align:center;margin-top:20px;font-size:.85rem;color:var(--text-soft);">
        ← My orders
      </a>
    </div>
  </div>
</div>

<?php include ROOT_PATH . 'includes/footer.php'; ?>
