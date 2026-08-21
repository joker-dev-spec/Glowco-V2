<?php
// --- admin/reset_user_password.php ---
// Generates a one-time temporary password for a customer so the owner can
// fulfil manual reset requests (self-service reset is disabled).

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/admin_auth.php';

$id   = (int)($_GET['id'] ?? 0);
$conn = get_db_connection();

$stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || $user['role'] !== 'customer') {
    set_flash('error', 'Only customer accounts can be reset here.');
    header("Location: users.php");
    exit();
}

$new_password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) die("Invalid request.");

    $new_password = bin2hex(random_bytes(6)); // 12-char temporary password
    $hashed       = hash_password($new_password);

    $stmt = $conn->prepare(
        "UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?"
    );
    $stmt->bind_param("si", $hashed, $id);
    $stmt->execute();

    error_log("Admin reset password for user #{$id} ({$user['email']})");
}

$page_title = 'Reset Password — Glow Co. Admin';
include ROOT_PATH . 'includes/admin_header.php';
?>

<div class="admin-main">
  <h1>Reset Customer Password</h1>
  <a href="<?= BASE_URL ?>admin/users.php" style="font-size:.85rem;color:var(--text-soft);display:inline-block;margin-bottom:24px;">← Back to customers</a>

  <?php if ($new_password): ?>
    <div class="flash flash--success" style="margin-bottom:20px;">
      Temporary password created for <strong><?= htmlspecialchars($user['name']) ?></strong>
      (<?= htmlspecialchars($user['email']) ?>).
    </div>
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:28px;box-shadow:var(--shadow);max-width:480px;text-align:center;">
      <p style="font-size:.78rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-soft);margin-bottom:8px;">Temporary password</p>
      <p style="font-family:monospace;font-size:1.5rem;font-weight:700;color:var(--plum);letter-spacing:.06em;"><?= htmlspecialchars($new_password) ?></p>
      <p style="font-size:.8rem;color:var(--text-soft);margin-top:16px;line-height:1.6;">
        Share this with the customer now — it is shown only once.<br>
        They can change it after logging in.
      </p>
    </div>
  <?php else: ?>
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:28px;box-shadow:var(--shadow);max-width:480px;">
      <p style="color:var(--plum);font-weight:600;margin-bottom:4px;"><?= htmlspecialchars($user['name']) ?></p>
      <p style="color:var(--text-soft);font-size:.85rem;margin-bottom:20px;"><?= htmlspecialchars($user['email']) ?></p>
      <p style="font-size:.85rem;color:var(--text-soft);line-height:1.6;margin-bottom:20px;">
        This replaces their current password with a random temporary one and clears any pending reset link.
        Copy the result and send it to them.
      </p>
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <button type="submit" class="btn-primary" style="width:100%;">Generate Temporary Password</button>
      </form>
    </div>
  <?php endif; ?>
</div>

<?php include ROOT_PATH . 'includes/admin_footer.php'; ?>
