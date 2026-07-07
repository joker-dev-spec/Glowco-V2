<?php
// --- includes/footer.php ---
?>
<footer>
  <div class="footer-inner">
    <div class="footer-brand">
      <a href="<?= BASE_URL ?>" class="logo">
        <div class="logo-img-wrap">
          <img src="<?= BASE_URL ?>assets/images/logo.jpeg" alt="Glow Co."
               onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
          <div class="logo-mark" style="display:none;">G</div>
        </div>
        low Co<span class="logo-dot">.</span>
      </a>
      <p>Premium body creams, perfumes<br>&amp; lotions for every skin type.</p>
    </div>
    <div class="footer-links">
      <h4>Shop</h4>
      <a href="<?= BASE_URL ?>pages/shop.php">All Products</a>
      <a href="<?= BASE_URL ?>cart/cart.php">Your Cart</a>
      <?php if (is_logged_in()): ?>
        <a href="<?= BASE_URL ?>wishlist/view.php">Wishlist</a>
      <?php endif; ?>
    </div>
    <div class="footer-links">
      <h4>Company</h4>
      <a href="<?= BASE_URL ?>pages/about.php">About Us</a>
      <a href="<?= BASE_URL ?>pages/contact.php">Contact</a>
      <a href="<?= BASE_URL ?>user/orders.php">Track Order</a>
    </div>
    <div class="footer-links">
      <h4>Follow Us</h4>
      <a href="https://wa.me/qr/IEDMJTDBM3L6D1" target="_blank">WhatsApp</a>
      <a href="https://www.instagram.com/okunborjanet85/" target="_blank">Instagram</a>
      <a href="https://www.tiktok.com" target="_blank">TikTok</a>
    </div>
  </div>
  <div class="footer-bottom">
    <p>&copy; <?= date('Y') ?> Glow Co. All rights reserved.</p>
  </div>
</footer>

<div class="toast" id="toast"></div>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>
  window.addEventListener('scroll', () => {
    document.getElementById('header').classList.toggle('scrolled', window.scrollY > 50);
  });
</script>
</body>
</html>