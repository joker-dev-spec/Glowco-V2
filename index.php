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
    <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:center;">
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
    <div class="product-card">
      <div class="product-img-wrap">
        <?php if ($p['image_path']): ?>
          <a href="<?= BASE_URL ?>pages/product.php?id=<?= $p['id'] ?>">
            <img src="<?= BASE_URL . htmlspecialchars($p['image_path']) ?>"
                 alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
          </a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>pages/product.php?id=<?= $p['id'] ?>">
            <div style="width:100%;height:100%;background:var(--pink-soft);display:flex;align-items:center;justify-content:center;font-size:3rem;">🧴</div>
          </a>
        <?php endif; ?>
        <div class="product-overlay">
          <form method="POST" action="<?= BASE_URL ?>cart/add.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="quick-add">Quick Add</button>
          </form>
        </div>
      </div>
      <div class="product-info">
        <h3><a href="<?= BASE_URL ?>pages/product.php?id=<?= $p['id'] ?>"
               style="color:var(--plum);"><?= htmlspecialchars($p['name']) ?></a></h3>
        <?php if (!empty($p['description'])): ?>
          <p class="product-desc"><?= htmlspecialchars(substr($p['description'], 0, 80)) ?>...</p>
        <?php endif; ?>
        <div class="product-footer">
          <span class="product-price">₦<?= number_format($p['price'], 2) ?></span>
          <div class="product-actions">
            <?php if (is_logged_in()): ?>
              <form method="POST" action="<?= BASE_URL ?>wishlist/add.php" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <button type="submit" class="wish-btn" title="Save to wishlist">♡</button>
              </form>
            <?php endif; ?>
            <form method="POST" action="<?= BASE_URL ?>cart/add.php" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
              <input type="hidden" name="quantity" value="1">
              <button type="submit" class="icon-cart" title="Add to cart">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                  <line x1="3" y1="6" x2="21" y2="6"/>
                  <path d="M16 10a4 4 0 01-8 0"/>
                </svg>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
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