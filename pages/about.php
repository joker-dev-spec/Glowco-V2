<?php

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/config.php';
secure_session_start();

$page_title = 'About — Glow Co.';
include ROOT_PATH . 'includes/header.php';
?>

<section class="page-hero">
  <p class="section-eyebrow">Our Story</p>
  <h1>Built on the belief<br><em>skin is sacred.</em></h1>
  <p>We started Glow Co. because we couldn't find body creams that were luxurious, clean, and actually made for our skin. So we made them ourselves.</p>
</section>

<section class="about-section">
  <div class="about-grid">
    <div class="about-text">
      <h2>From a kitchen to a collection</h2>
      <p>Glow Co. was born in Lagos — one small batch of shea butter, two hands, and a vision for what skincare could feel like.</p>
      <p>Every formula is developed with dermatologists, tested on real skin, and made without parabens, sulphates, or synthetic fragrance. We believe your skin deserves ingredients you can actually pronounce.</p>
      <p>Today we ship across Nigeria, but the ethos stays the same: premium ingredients, honest formulas, visible results.</p>
    </div>
    <div class="about-img-placeholder">
      <img src="<?= BASE_URL ?>assets/images/about.jpeg"
           alt="Glow Co."
           onerror="this.style.display='none'">
    </div>
  </div>
</section>

<section class="about-section journey-section">
  <h2 style="text-align:center;margin-bottom:32px;">Our Journey</h2>
  <div class="values-list">
    <div class="value-item">
      <h4>2023</h4>
      <p>Founded in Lagos</p>
    </div>
    <div class="value-item">
      <h4>2024</h4>
      <p>First collection launched</p>
    </div>
    <div class="value-item">
      <h4>2025</h4>
      <p>Expanded to 6 products</p>
    </div>
    <div class="value-item">
      <h4>2026</h4>
      <p>Nationwide shipping</p>
    </div>
  </div>
</section>

<section class="about-section">
  <h2 style="text-align:center;margin-bottom:32px;">Our Values</h2>
  <div class="values-list">
    <div class="value-item">
      <div class="icon">🌿</div>
      <h4>Natural First</h4>
      <p>Every product starts with natural butters, botanical extracts, and oils your skin recognizes.</p>
    </div>
    <div class="value-item">
      <div class="icon">🧪</div>
      <h4>Dermatologist Tested</h4>
      <p>Formulated with skin professionals. Tested before every single launch — no exceptions.</p>
    </div>
    <div class="value-item">
      <div class="icon">🐰</div>
      <h4>Cruelty Free</h4>
      <p>Never tested on animals. Never will be. Certified and committed.</p>
    </div>
    <div class="value-item">
      <div class="icon">♻️</div>
      <h4>Conscious Packaging</h4>
      <p>Recyclable packaging, refillable options coming soon. We care about what happens after the jar.</p>
    </div>
  </div>
</section>

<section class="about-section">
  <div class="about-grid">
    <div class="about-img-placeholder">
      <img src="<?= BASE_URL ?>assets/images/founder.jpeg"
           alt="Founder of Glow Co."
           onerror="this.onerror=null;this.src='data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22500%22><rect fill=%22%23FDE0E8%22 width=%22400%22 height=%22500%22/><text x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-size=%2272%22>✨</text></svg>'">
    </div>
    <div class="about-text">
      <h2>Meet Our Founder</h2>
      <p>Janet Okunbor founded Glow Co. with a simple conviction: African skin deserves world-class care. Growing up in Lagos, she watched her mother and sisters struggle to find products that were both luxurious and genuinely good for their skin. After years of researching botanical ingredients and working with dermatologists, she launched Glow Co. from her kitchen — hand-pouring every jar and personally testing every formula. Today, her mission remains unchanged: to create clean, effective skincare that makes every person feel confident in their own skin.</p>
    </div>
  </div>
</section>

<section class="about-section">
  <h2 style="text-align:center;margin-bottom:32px;">What Our Customers Say</h2>
  <div class="testimonial-grid">
    <div class="testimonial-card">
      <div class="stars">★★★★★</div>
      <p>"I've tried so many body creams and nothing compares to Glow Co. My skin has never felt this soft and nourished. I'm a customer for life!"</p>
      <span class="reviewer">— Adaeze O., Lagos</span>
    </div>
    <div class="testimonial-card">
      <div class="stars">★★★★★</div>
      <p>"Finally, a brand that understands our skin. The shea butter blend is incredible — it absorbs so quickly and leaves the most beautiful glow."</p>
      <span class="reviewer">— Folake M., Abuja</span>
    </div>
    <div class="testimonial-card">
      <div class="stars">★★★★★</div>
      <p>"I bought the full set as a gift for my sister and she was obsessed. The packaging is gorgeous and the quality is unmatched. We're both hooked!"</p>
      <span class="reviewer">— Ngozi E., Port Harcourt</span>
    </div>
  </div>
</section>

<section class="about-section" style="text-align:center;padding:48px 24px;">
  <h2>Ready to glow?</h2>
  <p style="margin:12px 0 24px;">Discover the collection your skin has been waiting for.</p>
  <a href="<?= BASE_URL ?>pages/shop.php" class="btn-primary" style="display:inline-block;">Shop the Collection</a>
</section>

<?php include ROOT_PATH . 'includes/footer.php'; ?>
