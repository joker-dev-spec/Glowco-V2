<?php
// --- includes/admin_header.php ---
if (session_status() === PHP_SESSION_NONE) session_start();
if (!defined('ROOT_PATH')) require_once dirname(__DIR__) . '/config/config.php';
$flash = get_flash();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?? 'Glowco Admin' ?></title>
  <link rel="icon" type="image/jpeg" href="<?= BASE_URL ?>assets/images/logo.jpeg">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body class="admin-layout">

<nav class="admin-nav">
  <div class="admin-nav__inner">
    <a href="<?= BASE_URL ?>admin/dashboard.php" class="admin-nav__brand">Glow Co. Admin</a>
    <ul>
      <li><a href="<?= BASE_URL ?>admin/dashboard.php">Dashboard</a></li>
      <li><a href="<?= BASE_URL ?>admin/products.php">Products</a></li>
      <li><a href="<?= BASE_URL ?>admin/orders.php">Orders</a></li>
      <li><a href="<?= BASE_URL ?>admin/users.php">Users</a></li>
      <li><a href="<?= BASE_URL ?>admin/messages.php">Messages</a></li>
      <li><a href="<?= BASE_URL ?>admin/change_password.php">Change Password</a></li>
      <li><a href="<?= BASE_URL ?>auth/logout.php">Logout</a></li>
    </ul>
  </div>
</nav>

<?php if ($flash): ?>
  <div class="flash flash--<?= htmlspecialchars($flash['type']) ?>"
       style="max-width:1200px;margin:1rem auto;">
    <?= htmlspecialchars($flash['message']) ?>
  </div>
<?php endif; ?>