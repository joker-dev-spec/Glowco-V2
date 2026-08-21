<?php
// --- admin/migrate.php ---
// One-time database migrations, run by visiting this page while logged in as admin.
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/admin_auth.php';

$conn = get_db_connection();

$migrations = [
    'reviews table' => "CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT DEFAULT NULL,
        user_id INT NOT NULL,
        rating TINYINT NOT NULL,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_review (product_id, user_id)
    ) ENGINE=InnoDB",
];

$results = [];
foreach ($migrations as $name => $sql) {
    try {
        $conn->query($sql);
        $results[] = ['name' => $name, 'ok' => true];
    } catch (Throwable $e) {
        $results[] = ['name' => $name, 'ok' => false, 'error' => $e->getMessage()];
    }
}

$page_title = 'Database Migrations — Glow Co.';
include ROOT_PATH . 'includes/header.php';
?>

<div class="wishlist-page">
  <p class="section-eyebrow">Maintenance</p>
  <h1>Database Migrations</h1>

  <div style="display:flex;flex-direction:column;gap:16px;margin-top:24px;max-width:560px;">
    <?php foreach ($results as $r): ?>
      <div style="background:var(--white);border-radius:var(--radius-lg);padding:20px;box-shadow:var(--shadow);">
        <strong style="color:var(--plum);"><?= htmlspecialchars($r['name']) ?></strong>
        <?php if ($r['ok']): ?>
          <p style="margin:6px 0 0;color:#2e7d32;font-size:.9rem;">✓ Done — table exists.</p>
        <?php else: ?>
          <p style="margin:6px 0 0;color:#c62828;font-size:.9rem;">✗ Failed: <?= htmlspecialchars($r['error']) ?></p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <p style="margin-top:24px;font-size:.85rem;color:var(--text-soft);">Safe to re-run — nothing is overwritten. You may delete <code>admin/migrate.php</code> once everything shows ✓.</p>
</div>

<?php include ROOT_PATH . 'includes/footer.php'; ?>
