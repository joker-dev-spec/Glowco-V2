<?php
// --- pages/contact.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/config.php';
session_start();

$form_success = '';
$form_error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) die("Invalid request.");

    $name    = sanitize_input($_POST['name'] ?? '');
    $email   = sanitize_input($_POST['email'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
        $form_error = "Please provide your name, a valid email and a message.";
    } else {
        $conn = get_db_connection();
        $stmt = $conn->prepare(
            "INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        if ($stmt->execute()) {
            $form_success = "Thanks {$name}! Your message has been received. We'll reply within 24 hours.";
            $_POST = [];
        } else {
            $form_error = "Sorry, we couldn't send your message. Please try again.";
        }
    }
}

$page_title = 'Contact — Glow Co.';
include ROOT_PATH . 'includes/header.php';
$csrf = generate_csrf_token();
?>

<section class="page-hero">
  <p class="section-eyebrow">Get in touch</p>
  <h1>We'd love to<br><em>hear from you.</em></h1>
  <p>Questions about your order, skincare advice, or just want to say hi — reach us on any of these platforms.</p>
</section>

<section class="contact-section">
  <div class="social-cards">
    <a href="https://wa.me/qr/IEDMJTDBM3L6D1" target="_blank" class="social-card">
      <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
      </svg>
      <h3>WhatsApp</h3>
      <p>Chat with us directly for fast responses and order updates.</p>
    </a>

    <a href="https://www.instagram.com/okunborjanet85/" target="_blank" class="social-card">
      <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
      </svg>
      <h3>Instagram</h3>
      <p>Follow us for skincare tips, new arrivals, and behind the scenes.</p>
    </a>

    <a href="https://www.tiktok.com" target="_blank" class="social-card">
      <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor">
        <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.76a4.85 4.85 0 01-1.01-.07z"/>
      </svg>
      <h3>TikTok</h3>
      <p>Watch tutorials, reviews, and skincare routines from our community.</p>
    </a>
  </div>

  <div style="margin-top:60px;background:var(--white);border-radius:20px;padding:40px;box-shadow:var(--shadow);text-align:center;">
    <p class="section-eyebrow">Response Time</p>
    <h2 style="font-size:1.8rem;margin-bottom:12px;color:var(--plum);">We reply within 24 hours</h2>
    <p style="color:var(--text-soft);font-size:.95rem;line-height:1.7;">Monday – Friday, 9am – 6pm WAT.<br>Orders placed on weekends are processed Monday morning.</p>
  </div>

  <div class="contact-form-card">
    <div style="text-align:center;margin-bottom:28px;">
      <p class="section-eyebrow">Send a message</p>
      <h2 style="font-size:1.8rem;color:var(--plum);">We'd love to help</h2>
    </div>

    <?php if ($form_success): ?>
      <div class="flash flash--success"><?= htmlspecialchars($form_success) ?></div>
    <?php elseif ($form_error): ?>
      <div class="flash flash--error"><?= htmlspecialchars($form_error) ?></div>
    <?php endif; ?>

    <form method="POST" action="contact.php">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

      <div class="form-row-2">
        <div>
          <label>Your Name *</label>
          <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div>
          <label>Your Email *</label>
          <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
      </div>

      <div>
        <label>Subject</label>
        <input type="text" name="subject" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>"
               placeholder="Order question, skincare advice, partnership...">
      </div>

      <div>
        <label>Message *</label>
        <textarea name="message" required rows="5"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
      </div>

      <button type="submit" class="btn-primary" style="align-self:flex-start;">Send Message</button>
    </form>
  </div>
</section>

<?php include ROOT_PATH . 'includes/footer.php'; ?>