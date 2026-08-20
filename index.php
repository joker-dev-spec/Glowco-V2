<?php
// --- index.php ---

if (!defined('ROOT_PATH')) define('ROOT_PATH', __DIR__ . '/');
require_once ROOT_PATH . 'config/config.php';
secure_session_start();

$conn     = get_db_connection();
$featured = $conn->query(
    "SELECT id, name, price, image_path, description FROM products WHERE stock > 0 ORDER BY created_at DESC LIMIT 8"
);

$page_title = 'Glow Co. — Premium Body Creams';
// header.php outputs <header class="scrolled"> — strip scrolled for homepage so the transition fires
include ROOT_PATH . 'includes/header.php';
$csrf = generate_csrf_token();
?>

<section class="hero">
  <div class="hero-content">
    <p class="hero-eyebrow">Crafted for your skin</p>
    <h1 class="hero-title">Your skin<br><em>deserves</em><br>the ritual.</h1>
    <p class="hero-sub">Luxurious body creams, perfumes &amp; lotions made with natural butters, botanical oils, and ingredients that actually work.</p>
    <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:center;justify-content:center;">
      <a href="<?= BASE_URL ?>pages/shop.php" class="btn-primary">Shop the collection</a>
    </div>
  </div>
  <div class="hero-orbs">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
  </div>
  <div class="hero-scroll-hint">
    <span>Scroll</span>
    <div class="scroll-line"></div>
  </div>
</section>

<section class="benefits-strip">
  <div class="benefit"><span>🌿</span><span>100% Natural Butters</span></div>
  <div class="benefit"><span>🔬</span><span>Dermatologist Tested</span></div>
  <div class="benefit"><span>✅</span><span>Paraben Free</span></div>
  <div class="benefit"><span>🐰</span><span>Cruelty Free</span></div>
  <div class="benefit"><span>🚚</span><span>Free Shipping Over ₦15k</span></div>
</section>

<section class="products-section">
  <div class="section-header">
    <p class="section-eyebrow">Featured</p>
    <h2 class="section-title">Customer favourites</h2>
  </div>

  <div class="product-grid">
  <?php while ($p = $featured->fetch_assoc()): ?>
    <?php include ROOT_PATH . 'includes/product_card.php'; ?>
  <?php endwhile; ?>
  </div>

  <div style="text-align:center;margin-top:48px;">
    <a href="<?= BASE_URL ?>pages/shop.php" class="btn-primary">View all products</a>
  </div>
</section>

<section class="testimonials">
  <div class="section-header">
    <p class="section-eyebrow">Real Results</p>
    <h2 class="section-title">What our customers say</h2>
  </div>
  <div class="testimonial-grid">
    <div class="testimonial-card">
      <div class="stars">★★★★★</div>
      <p>"Completely transformed my skin. I've never felt this moisturized after just one week."</p>
      <span class="reviewer">— Amara O., Lagos</span>
    </div>
    <div class="testimonial-card">
      <div class="stars">★★★★★</div>
      <p>"Everyone asks what I'm using. Worth every kobo."</p>
      <span class="reviewer">— Chidinma E., Abuja</span>
    </div>
    <div class="testimonial-card">
      <div class="stars">★★★★★</div>
      <p>"Finally a body cream that doesn't break me out. Exactly what sensitive skin needs."</p>
      <span class="reviewer">— Fatima B., Port Harcourt</span>
    </div>
  </div>
</section>

<section class="newsletter">
  <div class="newsletter-inner">
    <p class="section-eyebrow">Stay in the glow</p>
    <h2>Get 10% off your first order</h2>
    <p class="newsletter-sub">Join our community for skincare tips, new launches, and exclusive deals.</p>
    <form class="newsletter-form" id="newsletterForm">
      <input type="email" placeholder="Enter your email address" required>
      <button type="submit">Claim discount</button>
    </form>
  </div>
</section>

<?php include ROOT_PATH . 'includes/footer.php'; ?>

<script>
document.getElementById('newsletterForm').addEventListener('submit', e => {
  e.preventDefault();
  const toast = document.getElementById('toast');
  toast.textContent = '🎉 Welcome! Your 10% code: GLOW10';
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 4000);
  e.target.reset();
});
</script>