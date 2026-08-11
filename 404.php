<?php
// --- 404.php ---
define('ROOT_PATH', __DIR__ . '/');
require_once ROOT_PATH . 'config/config.php';
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Page Not Found — Glow Co.</title>
  <link rel="icon" type="image/jpeg" href="<?= BASE_URL ?>assets/images/logo.jpeg">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper" style="padding:2rem 1rem;">
  <div class="auth-card" style="text-align:center;">
    <div style="font-size:4rem;margin-bottom:12px;">🔍</div>
    <h2 style="margin-bottom:12px;">Page not found</h2>
    <p style="color:var(--text-soft);margin-bottom:28px;">The page you're looking for doesn't exist or has moved.</p>
    <a href="<?= BASE_URL ?>" class="btn-primary" style="display:block;text-align:center;">Back to Home</a>
    <p style="margin-top:16px;"><a href="<?= BASE_URL ?>pages/shop.php" style="color:var(--pink-deep);font-weight:500;">Browse the shop</a></p>
  </div>
</div>
</body>
</html>
