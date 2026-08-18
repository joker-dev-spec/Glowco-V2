<?php
// --- includes/header.php ---
if (!defined('ROOT_PATH')) require_once dirname(__DIR__) . '/config/config.php';
if (session_status() === PHP_SESSION_NONE) secure_session_start();
require_once ROOT_PATH . 'includes/flash.php';
$flash = get_flash();

$cart_count = 0;
$wishlist_count = 0;
if (is_logged_in()) {
    try {
        $conn = get_db_connection();
        $uid  = (int)$_SESSION['user_id'];
        $res  = $conn->query("SELECT COUNT(*) AS c FROM cart_items WHERE user_id = {$uid}");
        if ($res) $cart_count = (int)$res->fetch_assoc()['c'];
        $res  = $conn->query("SELECT COUNT(*) AS c FROM wishlist WHERE user_id = {$uid}");
        if ($res) $wishlist_count = (int)$res->fetch_assoc()['c'];
    } catch (Throwable $e) {}
}
function badge_count(int $n): string {
    return $n > 99 ? '99+' : (string)$n;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?? 'Glowco' ?></title>
  <link rel="icon" type="image/jpeg" href="<?= BASE_URL ?>assets/images/logo.jpeg">
  <meta name="description" content="Glow Co. — premium body creams, perfumes and lotions made with natural butters and botanical oils. Free shipping on orders over ₦15,000.">
  <meta property="og:title" content="<?= htmlspecialchars($page_title ?? 'Glow Co.') ?>">
  <meta property="og:description" content="Premium body creams, perfumes & lotions made with natural butters and botanical oils.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '')) ?>">
  <meta property="og:image" content="<?= BASE_URL ?>assets/images/logo.jpeg">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>

<header id="header" class="scrolled">
  <div class="header-inner">
    <a href="<?= BASE_URL ?>" class="logo">
      <div class="logo-img-wrap">
        <img src="<?= BASE_URL ?>assets/images/logo.jpeg" alt="Glow Co."
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="logo-mark" style="display:none;">G</div>
      </div>
      low Co<span class="logo-dot">.</span>
    </a>
    <nav>
      <ul>
        <li><a href="<?= BASE_URL ?>index.php" class="nav-link">Home</a></li>
        <li><a href="<?= BASE_URL ?>pages/shop.php" class="nav-link">Shop</a></li>
        <li><a href="<?= BASE_URL ?>pages/quiz.php" class="nav-link">Skin Quiz</a></li>
        <li><a href="<?= BASE_URL ?>pages/about.php" class="nav-link">About</a></li>
        <li><a href="<?= BASE_URL ?>pages/contact.php" class="nav-link">Contact</a></li>
        <?php if (is_logged_in()): ?>
          <li>
            <a href="<?= BASE_URL ?>wishlist/view.php" class="nav-cart" title="Wishlist">
              <div class="nav-icon-wrap">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                <?php if ($wishlist_count > 0): ?>
                  <span class="wishlist-count" style="display:flex;"><?= badge_count($wishlist_count) ?></span>
                <?php endif; ?>
              </div>
            </a>
          </li>
          <li>
            <a href="<?= BASE_URL ?>cart/cart.php" class="nav-cart">
              <div class="nav-icon-wrap">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                <?php if ($cart_count > 0): ?>
                  <span class="cart-count" style="display:flex;"><?= badge_count($cart_count) ?></span>
                <?php endif; ?>
              </div>
            </a>
          </li>
          <?php if (is_admin()): ?>
            <li><a href="<?= BASE_URL ?>admin/dashboard.php" class="nav-link">Admin</a></li>
          <?php endif; ?>
          <li><a href="<?= BASE_URL ?>user/dashboard.php" class="nav-link">Account</a></li>
          <li><a href="<?= BASE_URL ?>auth/logout.php" class="nav-link">Logout</a></li>
        <?php else: ?>
          <li><a href="<?= BASE_URL ?>auth/login.php" class="nav-link">Login</a></li>
          <li><a href="<?= BASE_URL ?>auth/register.php" class="nav-link">Register</a></li>
        <?php endif; ?>
      </ul>
    </nav>
    <button id="mobileMenuBtn" aria-label="Toggle menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<?php if ($flash): ?>
  <div class="flash flash--<?= htmlspecialchars($flash['type']) ?>"
       style="max-width:1200px;margin:80px auto 0;">
    <?= htmlspecialchars($flash['message']) ?>
  </div>
<?php endif; ?>