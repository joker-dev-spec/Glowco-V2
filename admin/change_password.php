<?php

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/admin_auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) die("Invalid request.");

    $current  = $_POST['current'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || !verify_password($current, $user['password_hash'])) {
        $error = "Your current password is incorrect.";
    } elseif (strlen($password) < 8) {
        $error = "New password must be at least 8 characters.";
    } elseif ($password !== $confirm) {
        $error = "New passwords do not match.";
    } else {
        $hash = hash_password($password);
        $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->bind_param("si", $hash, $_SESSION['user_id']);
        $stmt->execute();
        set_flash('success', 'Your password has been updated.');
        header("Location: dashboard.php");
        exit();
    }
}

$page_title = 'Change Password — Glow Co. Admin';
include ROOT_PATH . 'includes/admin_header.php';
?>

<div class="admin-main">
  <h1>Change Password</h1>
  <a href="<?= BASE_URL ?>admin/dashboard.php" style="font-size:.85rem;color:var(--text-soft);display:inline-block;margin-bottom:24px;">← Back to dashboard</a>

  <?php if ($error): ?>
    <div class="flash flash--error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="admin-form">
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

      <div>
        <label>Current Password *</label>
        <input type="password" name="current" required>
      </div>

      <div>
        <label>New Password *</label>
        <input type="password" name="password" required minlength="8" placeholder="Min. 8 characters">
      </div>

      <div>
        <label>Confirm New Password *</label>
        <input type="password" name="confirm" required>
      </div>

      <button type="submit" class="btn-primary" style="width:100%;margin-top:8px;">Update Password</button>
    </form>
  </div>
</div>

<?php include ROOT_PATH . 'includes/admin_footer.php'; ?>
