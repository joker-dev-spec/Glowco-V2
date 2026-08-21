<?php

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

<!-- Quick View Modal -->
<div class="qv-overlay" id="qvOverlay">
  <div class="qv-modal" id="qvModal">
    <button class="qv-close" id="qvClose">&times;</button>
    <div class="qv-img" id="qvImg"></div>
    <div class="qv-body">
      <span class="qv-category" id="qvCategory"></span>
      <h2 class="qv-name" id="qvName"></h2>
      <div class="qv-price" id="qvPrice"></div>
      <p class="qv-desc" id="qvDesc"></p>
      <div class="qv-stock" id="qvStock"></div>
      <div class="qv-actions" id="qvActions"></div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>assets/js/main.js?v=<?= filemtime(ROOT_PATH . 'assets/js/main.js') ?>"></script>
<script>
  window.addEventListener('scroll', () => {
    document.getElementById('header').classList.toggle('scrolled', window.scrollY > 50);
  });

  // Quick View Modal
  document.addEventListener('DOMContentLoaded', () => {
    const overlay  = document.getElementById('qvOverlay');
    const modal    = document.getElementById('qvModal');
    const closeBtn = document.getElementById('qvClose');
    const base     = '<?= BASE_URL ?>';

    // Intercept clicks on product cards
    document.querySelectorAll('.product-card .product-img-wrap, .product-card .product-info h3 a').forEach(el => {
      el.addEventListener('click', e => {
        e.preventDefault();
        const card = el.closest('.product-card');
        const link = card.querySelector('.product-info h3 a');
        const url  = link ? link.getAttribute('href') : '';
        const id   = url.match(/id=(\d+)/);
        if (id) openQuickView(id[1]);
      });
    });

    closeBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    function closeModal() {
      overlay.classList.remove('open');
      document.body.style.overflow = '';
    }

    function openQuickView(productId) {
      fetch(base + 'pages/product_ajax.php?id=' + productId)
        .then(r => r.json())
        .then(p => {
          if (!p) return;
          document.getElementById('qvImg').innerHTML = p.image_url
            ? '<img src="' + esc(p.image_url) + '" alt="' + esc(p.name) + '">'
            : '<div class="product-img-fallback" style="height:100%;font-size:4rem;">🧴</div>';
          document.getElementById('qvCategory').textContent = p.category || '';
          document.getElementById('qvName').textContent = p.name;
          document.getElementById('qvPrice').textContent = p.price_formatted;
          document.getElementById('qvDesc').textContent = p.description || 'No description available.';
          const inStock = parseInt(p.stock) > 0;
          document.getElementById('qvStock').innerHTML = inStock
            ? '<span class="qv-stock--in">● In Stock</span>'
            : '<span class="qv-stock--out">● Out of Stock</span>';

          let actions = '';
          if (inStock) {
            actions += '<form method="POST" action="' + base + 'cart/add.php" style="flex:1;display:flex;">'
              + '<input type="hidden" name="csrf_token" value="' + esc(p.csrf) + '">'
              + '<input type="hidden" name="product_id" value="' + p.id + '">'
              + '<input type="hidden" name="quantity" value="1">'
              + '<button type="submit" class="qv-btn-cart" style="flex:1;">Add to Cart</button>'
              + '</form>';
          }
          actions += '<a href="' + p.url + '" class="qv-btn-view">View Full Details</a>';
          document.getElementById('qvActions').innerHTML = actions;

          overlay.classList.add('open');
          document.body.style.overflow = 'hidden';
        });
    }

    function esc(s) {
      const d = document.createElement('div');
      d.textContent = s || '';
      return d.innerHTML;
    }
  });
</script>
<button id="scrollTopBtn" class="scroll-top" aria-label="Scroll to top">
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 15l-6-6-6 6"/></svg>
</button>
</body>
</html>