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
      <a href="<?= BASE_URL ?>pages/shop.php" class="btn-primary" style="margin-top:12px;display:inline-block;">Shop the collection</a>
    </div>
    <div class="about-img-placeholder">
      <img src="<?= BASE_URL ?>assets/images/about.jpeg"
           alt="Glow Co."
           onerror="this.style.display='none'">
    </div>
  </div>

  <div class="values-list">
    <div class="value-item">
      <div class="icon"></div>
      <h4>Natural First</h4>
      <p>Every product starts with natural butters, botanical extracts, and oils your skin recognizes.</p>
    </div>
    <div class="value-item">
      <div class="icon"></div>
      <h4>Dermatologist Tested</h4>
      <p>Formulated with skin professionals. Tested before every single launch — no exceptions.</p>
    </div>
    <div class="value-item">
      <div class="icon"></div>
      <h4>Cruelty Free</h4>
      <p>Never tested on animals. Never will be. Certified and committed.</p>
    </div>
    <div class="value-item">
      <div class="icon"></div>
      <h4>Conscious Packaging</h4>
      <p>Recyclable packaging, refillable options coming soon. We care about what happens after the jar.</p>
    </div>
  </div>
</section>

<?php include ROOT_PATH . 'includes/footer.php'; ?>