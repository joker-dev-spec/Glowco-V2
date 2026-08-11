<?php
// --- auth/reset_password.php ---
require_once dirname(__DIR__) . "/config/config.php";
session_start();

$token = sanitize_input($_GET['token'] ?? '');
$error = '';

if (empty($token)) {
    header("Location: login.php");
    exit();
}

$conn = get_db_connection();
$stmt = $conn->prepare(
    "SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $error = "This reset link is invalid or has expired.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) die("Invalid request.");

    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashed = hash_password($password);
        $stmt   = $conn->prepare(
            "UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?"
        );
        $stmt->bind_param("si", $hashed, $user['id']);
        $stmt->execute();

        set_flash('success', 'Password reset. You can now log in.');
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Glowco</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <form method="POST" action="reset_password.php?token=<?= htmlspecialchars($token) ?>">
        <h2>Reset Password</h2>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <?php if ($user): ?>
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="password" name="password" placeholder="New Password" required minlength="8">
            <input type="password" name="confirm" placeholder="Confirm Password" required>
            <button type="submit">Reset Password</button>
        <?php endif; ?>
        <p><a href="login.php">Back to Login</a></p>
    </form>
  </div>
</div>
</body>
</html>