<?php
// --- auth/forgot_password.php ---

session_start();
require_once dirname(__DIR__) . "/config/config.php";

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) die("Invalid request.");

    $email = sanitize_input($_POST['email'] ?? '');
    $conn  = get_db_connection();
    $stmt  = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    if ($stmt->get_result()->num_rows === 1) {
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $stmt    = $conn->prepare(
            "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?"
        );
        $stmt->bind_param("sss", $token, $expires, $email);
        $stmt->execute();

        $reset_link = BASE_URL . "auth/reset_password.php?token={$token}";
        $message    = "success|Reset link ready. <a href='{$reset_link}' style='color:var(--pink-deep);font-weight:600;'>Click here to reset your password.</a> In production this sends an email.";
    } else {
        $message = "info|If that email exists in our system, a reset link has been sent.";
    }
}

$parts   = $message ? explode('|', $message, 2) : [];
$msg_type = $parts[0] ?? '';
$msg_text = $parts[1] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password — Glow Co.</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <h2>Reset Password</h2>

    <?php if ($msg_text): ?>
      <div class="flash flash--<?= $msg_type === 'success' ? 'success' : 'info' ?>"
           style="margin:0 0 20px;border-radius:var(--radius);">
        <?= $msg_text ?>
      </div>
    <?php endif; ?>

    <?php if (!$msg_type || $msg_type !== 'success'): ?>
      <form method="POST" action="forgot_password.php">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <div>
          <label>Your Email</label>
          <input type="email" name="email" placeholder="your@email.com" required autofocus>
        </div>
        <button type="submit">Send Reset Link</button>
      </form>
    <?php else: ?>
      <a href="<?= BASE_URL ?>auth/login.php" class="btn-primary"
         style="display:block;text-align:center;margin-top:8px;">
        Back to Login
      </a>
    <?php endif; ?>

    <?php if (!$msg_type || $msg_type !== 'success'): ?>
      <p><a href="login.php">Back to Login</a></p>
    <?php endif; ?>
  </div>
</div>
<div class="toast" id="toast"></div>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>