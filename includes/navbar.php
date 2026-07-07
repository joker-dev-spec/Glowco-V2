<?php
// --- includes/navbar.php ---
?>
<nav class="navbar">
    <a href="<?= BASE_URL ?>" class="navbar__brand">Glowco</a>
    <ul class="navbar__links">
        <li><a href="<?= BASE_URL ?>pages/shop.php">Shop</a></li>
        <?php if (is_logged_in()): ?>
            <li><a href="<?= BASE_URL ?>cart/cart.php">Cart</a></li>
            <li><a href="<?= BASE_URL ?>wishlist/view.php">Wishlist</a></li>
            <li><a href="<?= BASE_URL ?>user/dashboard.php">My Account</a></li>
            <?php if (is_admin()): ?>
                <li><a href="<?= BASE_URL ?>admin/dashboard.php">Admin</a></li>
            <?php endif; ?>
            <li><a href="<?= BASE_URL ?>auth/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="<?= BASE_URL ?>auth/login.php">Login</a></li>
            <li><a href="<?= BASE_URL ?>auth/register.php">Register</a></li>
        <?php endif; ?>
        <li>
            <form method="GET" action="<?= BASE_URL ?>pages/search.php" class="navbar__search">
                <input type="text" name="q" placeholder="Search products">
                <button type="submit">Go</button>
            </form>
        </li>
    </ul>
</nav>