<?php
// --- auth/reset_password.php ---

require_once dirname(__DIR__) . "/config/config.php";
session_start();

$token = sanitize_input($_GET['token'] ?? '');
$error = '';

if (empty($token)) { header("Location: login.php"); exit(); }

$conn = get_db_connection();
$stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) { $error = "This reset link is invalid or has expired."; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) die("Invalid request.");
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashed = hash_password($password);
        $stmt   = $conn->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
        $stmt->bind_param("si", $hashed, $user['id']);
        $stmt->execute();
        set_flash('success', 'Password reset. You can now log in.');
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>New Password — Glow Co.</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <h2>New Password</h2>
    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($user): ?>
      <form method="POST" action="reset_password.php?token=<?= htmlspecialchars($token) ?>">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <div>
          <label>New Password</label>
          <input type="password" name="password" placeholder="Min. 8 characters" required minlength="8">
        </div>
        <div>
          <label>Confirm Password</label>
          <input type="password" name="confirm" placeholder="Repeat password" required>
        </div>
        <button type="submit">Set New Password</button>
      </form>
    <?php endif; ?>
    <p><a href="login.php">Back to Login</a></p>
  </div>
</div>
<div class="toast" id="toast"></div>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>