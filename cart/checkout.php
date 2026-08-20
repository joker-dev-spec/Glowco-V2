<?php
// --- cart/checkout.php ---

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . "includes/user_auth.php";

$conn    = get_db_connection();
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT ci.product_id, ci.quantity, p.price, p.name, p.stock, p.image_path
     FROM cart_items ci
     JOIN products p ON ci.product_id = p.id
     WHERE ci.user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($cart)) {
    header("Location: " . BASE_URL . "cart/cart.php");
    exit();
}

$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += (float)$item['price'] * (int)$item['quantity'];
}

$shipping = $subtotal >= 15000 ? 0 : 1500;
$total    = $subtotal + $shipping;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) die("Invalid request.");

    $shipping_name    = sanitize_input($_POST['shipping_name'] ?? '');
    $shipping_phone   = sanitize_input($_POST['shipping_phone'] ?? '');
    $shipping_address = sanitize_input($_POST['shipping_address'] ?? '');
    $shipping_city    = sanitize_input($_POST['shipping_city'] ?? '');
    $shipping_state   = sanitize_input($_POST['shipping_state'] ?? '');

    if ($shipping_name === '' || $shipping_phone === '' || $shipping_address === '') {
        $error = 'Please enter your name, phone number and delivery address.';
    } elseif (!preg_match('/^[0-9+ ()-]{7,20}$/', $shipping_phone)) {
        $error = 'Please enter a valid phone number.';
    } else {
        $conn->begin_transaction();

        try {
            $reserve = $conn->prepare("SELECT stock FROM products WHERE id = ? FOR UPDATE");
            foreach ($cart as $item) {
                $reserve->bind_param("i", $item['product_id']);
                $reserve->execute();
                $res = $reserve->get_result()->fetch_assoc();
                if (!$res || (int)$res['stock'] < (int)$item['quantity']) {
                    throw new RuntimeException("insufficient_stock:" . $item['name']);
                }
            }

            $stmt = $conn->prepare(
                "INSERT INTO orders (user_id, total_amount, status, shipping_name, shipping_phone, shipping_address, shipping_city, shipping_state)
                 VALUES (?, ?, 'pending', ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("idsssss", $user_id, $total, $shipping_name, $shipping_phone, $shipping_address, $shipping_city, $shipping_state);
            $stmt->execute();
            $order_id = $conn->insert_id;

            $stmt = $conn->prepare(
                "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)"
            );
            foreach ($cart as $item) {
                $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmt->execute();
            }

            $dec = $conn->prepare("UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?");
            foreach ($cart as $item) {
                $dec->bind_param("ii", $item['quantity'], $item['product_id']);
                $dec->execute();
            }

            $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $conn->commit();
        } catch (RuntimeException $e) {
            $conn->rollback();
            if (str_starts_with($e->getMessage(), 'insufficient_stock:')) {
                set_flash('error', 'Not enough stock for "' . substr($e->getMessage(), 18) . '". Please reduce the quantity and try again.');
            } else {
                set_flash('error', 'Checkout failed. Please try again.');
            }
            header("Location: " . BASE_URL . "cart/cart.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Checkout failed: " . $e->getMessage());
            set_flash('error', 'Checkout failed. Please try again.');
            header("Location: " . BASE_URL . "cart/cart.php");
            exit();
        }

        set_flash('success', "Order #{$order_id} placed! Transfer the total and confirm payment to complete your order.");
        header("Location: " . BASE_URL . "cart/pay.php?order_id={$order_id}");
        exit();
    }
}

$page_title = 'Checkout — Glow Co.';
include ROOT_PATH . 'includes/header.php';
$csrf = generate_csrf_token();
?>

<section class="page-hero" style="text-align:left;">
  <p class="section-eyebrow">Almost there</p>
</section>

<div class="cart-page" style="padding-top:0;">
  <div class="cart-layout">
    <div>
      <h1>Delivery Details</h1>
      <p class="sub">Where should we send your order?</p>

      <?php if ($error): ?>
        <div class="flash flash--error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="checkout.php" style="max-width:520px;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

        <div>
          <label>Full Name *</label>
          <input type="text" name="shipping_name" required
                 value="<?= htmlspecialchars($_POST['shipping_name'] ?? ($_SESSION['name'] ?? '')) ?>"
                 placeholder="Your full name">
        </div>

        <div>
          <label>Phone Number *</label>
          <input type="tel" name="shipping_phone" required
                 value="<?= htmlspecialchars($_POST['shipping_phone'] ?? '') ?>"
                 placeholder="e.g. 08012345678">
        </div>

        <div>
          <label>Delivery Address *</label>
          <input type="text" name="shipping_address" required
                 value="<?= htmlspecialchars($_POST['shipping_address'] ?? '') ?>"
                 placeholder="House number, street, area">
        </div>

        <div class="form-row-2">
          <div>
            <label>City</label>
            <input type="text" name="shipping_city"
                   value="<?= htmlspecialchars($_POST['shipping_city'] ?? '') ?>"
                   placeholder="e.g. Ikeja">
          </div>
          <div>
            <label>State</label>
            <input type="text" name="shipping_state"
                   value="<?= htmlspecialchars($_POST['shipping_state'] ?? '') ?>"
                   placeholder="e.g. Lagos">
          </div>
        </div>

        <button type="submit" class="btn-primary" style="width:100%;margin-top:8px;">
          Place Order — Transfer ₦<?= number_format($total, 0) ?>
        </button>
        <p style="font-size:.78rem;color:var(--text-soft);margin-top:10px;text-align:center;">
          You'll get OPay &amp; Access Bank details to transfer to. No card needed.
        </p>
      </form>
    </div>

    <div class="cart-summary">
      <h2>Order Summary</h2>
      <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:16px;">
        <?php foreach ($cart as $item): ?>
          <div style="display:flex;gap:12px;align-items:center;">
            <?php if ($item['image_path']): ?>
              <img src="<?= BASE_URL . htmlspecialchars($item['image_path']) ?>"
                   alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px;background:var(--pink-soft);">
            <?php endif; ?>
            <div style="flex:1;font-size:.85rem;">
              <div style="color:var(--plum);font-weight:600;"><?= htmlspecialchars($item['name']) ?></div>
              <div style="color:var(--text-soft);font-size:.78rem;">Qty: <?= (int)$item['quantity'] ?></div>
            </div>
            <div style="font-size:.85rem;font-weight:600;color:var(--plum);">
              ₦<?= number_format($item['price'] * $item['quantity'], 0) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="summary-row">
        <span>Subtotal</span>
        <span>₦<?= number_format($subtotal, 0) ?></span>
      </div>
      <div class="summary-row">
        <span>Shipping</span>
        <span style="color:<?= $shipping === 0 ? '#2e7d32' : 'var(--text-soft)' ?>">
          <?= $shipping === 0 ? 'Free' : '₦' . number_format($shipping, 0) ?>
        </span>
      </div>
      <div class="summary-row total">
        <span>Total</span>
        <span>₦<?= number_format($total, 0) ?></span>
      </div>
      <a href="<?= BASE_URL ?>cart/cart.php"
         style="display:block;text-align:center;margin-top:12px;font-size:.85rem;color:var(--text-soft);">
        ← Back to cart
      </a>
    </div>
  </div>
</div>

<?php include ROOT_PATH . 'includes/footer.php'; ?>
