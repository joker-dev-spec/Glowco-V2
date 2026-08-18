<?php
// --- user/change_password.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/user_auth.php';

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
        header("Location: " . BASE_URL . "user/dashboard.php");
        exit();
    }
}

$page_title = 'Change Password — Glow Co.';
include ROOT_PATH . 'includes/header.php';
$csrf = generate_csrf_token();
?>

<div class="user-dashboard">
  <p class="section-eyebrow">Security</p>
  <h1>Change Password</h1>

  <div style="max-width:520px;background:var(--white);border-radius:var(--radius-lg);padding:32px;box-shadow:var(--shadow);margin-top:32px;">
    <?php if ($error): ?>
      <div class="flash flash--error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

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

    <a href="<?= BASE_URL ?>user/dashboard.php"
       style="display:block;text-align:center;margin-top:16px;font-size:.85rem;color:var(--text-soft);">
      ← Back to account
    </a>
  </div>
</div>

<?php include ROOT_PATH . 'includes/footer.php'; ?>
