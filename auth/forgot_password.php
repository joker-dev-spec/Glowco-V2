<?php
// --- auth/forgot_password.php ---
// Self-service reset is disabled: there is no mail infrastructure on the
// deployment, so reset links must never be rendered on-screen (that would let
// anyone take over any account just by knowing the email address).
// Password resets are handled manually by the shop owner from the admin panel.

require_once dirname(__DIR__) . "/config/config.php";
secure_session_start();
header("Referrer-Policy: no-referrer");
header('Cache-Control: no-store, no-cache, must-revalidate');
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

    <div class="flash flash--info" style="margin:0 0 20px;border-radius:var(--radius);">
      For your security, password resets are handled by our team.
      Send us a message and we'll set up a temporary password for you.
    </div>

    <a href="<?= BASE_URL ?>pages/contact.php" class="btn-primary"
       style="display:block;text-align:center;">
      Contact Support
    </a>

    <p style="margin-top:16px;"><a href="login.php">Back to Login</a></p>
  </div>
</div>
<div class="toast" id="toast"></div>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
