<?php

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/admin_auth.php';

$conn = get_db_connection();

// Mark any unread messages as read once this page is viewed
$conn->query("UPDATE messages SET is_read = 1 WHERE is_read = 0");

$messages = $conn->query(
    "SELECT id, name, email, subject, message, is_read, created_at
     FROM messages ORDER BY created_at DESC"
);

$page_title = 'Messages — Glow Co. Admin';
include ROOT_PATH . 'includes/admin_header.php';
?>

<div class="admin-main">
  <h1>Contact Messages</h1>

  <?php if ($messages->num_rows === 0): ?>
    <div style="text-align:center;padding:60px 20px;background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow);color:var(--text-soft);">
      No messages yet. Messages sent from the Contact page will appear here.
    </div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:16px;">
      <?php while ($m = $messages->fetch_assoc()): ?>
        <div style="background:var(--white);border-radius:var(--radius-lg);padding:24px;box-shadow:var(--shadow);border-left:4px solid <?= $m['is_read'] ? 'var(--pink-soft)' : 'var(--gold)' ?>;">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
            <div>
              <strong style="color:var(--plum);"><?= htmlspecialchars($m['name']) ?></strong>
              <span style="color:var(--text-soft);font-size:.82rem;margin-left:8px;"><?= htmlspecialchars($m['email']) ?></span>
            </div>
            <span style="font-size:.78rem;color:var(--text-soft);"><?= date('M j, Y g:ia', strtotime($m['created_at'])) ?></span>
          </div>
          <?php if ($m['subject']): ?>
            <p style="font-weight:600;color:var(--plum);margin-bottom:8px;font-size:.9rem;"><?= htmlspecialchars($m['subject']) ?></p>
          <?php endif; ?>
          <p style="color:var(--text-soft);font-size:.9rem;line-height:1.7;"><?= nl2br(htmlspecialchars($m['message'])) ?></p>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<?php include ROOT_PATH . 'includes/admin_footer.php'; ?>
