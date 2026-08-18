<?php

require_once dirname(__DIR__) . "/config/config.php";
secure_session_start();
header('Cache-Control: no-store, no-cache, must-revalidate');

$token = sanitize_input($_GET['token'] ?? '');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) die("Invalid request.");

    if (!rate_limit('login', 5, 300)) {
        $error = "Too many failed attempts. Please try again in a few minutes.";
    } else {
        $email    = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $conn = get_db_connection();
        $stmt = $conn->prepare("SELECT id, name, password_hash, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (verify_password($password, $user['password_hash'])) {
                session_regenerate_id(true);
                rate_limit_clear('login');
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['role']    = $user['role'];
                $_SESSION['email']   = $email;
                header("Location: " . BASE_URL . ($user['role'] === 'admin' ? "admin/dashboard.php" : "user/dashboard.php"));
                exit();
            }
        }
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Glow Co.</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <h2>Welcome Back</h2>
    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" action="login.php">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <div>
        <label>Email</label>
        <input type="email" name="email" placeholder="your@email.com" required>
      </div>
      <div>
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit">Login</button>
    </form>
    <p>No account? <a href="register.php">Register</a></p>
    <p><a href="forgot_password.php">Forgot password?</a></p>
  </div>
</div>
<div class="toast" id="toast"></div>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>