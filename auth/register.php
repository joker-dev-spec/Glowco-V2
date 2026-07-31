<?php

require_once dirname(__DIR__) . "/config/config.php";
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) die("Invalid request.");

    $name     = sanitize_input($_POST['name'] ?? '');
    $email    = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if (empty($name) || empty($email)) {
        $error = "Name and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $conn = get_db_connection();

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            $error = "An account with that email already exists.";
        } else {
            $hashed = hash_password($password);
            $stmt   = $conn->prepare(
                "INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'customer')"
            );
            $stmt->bind_param("sss", $name, $email, $hashed);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['name']    = $name;
                $_SESSION['role']    = 'customer';
                $_SESSION['email']   = $email;

                header("Location: " . BASE_URL . "user/dashboard.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — Glow Co.</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <h2>Create Account</h2>
    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" action="register.php">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <div>
        <label>Full Name</label>
        <input type="text" name="name" placeholder="Your name" required
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      </div>
      <div>
        <label>Email</label>
        <input type="email" name="email" placeholder="your@email.com" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div>
        <label>Password</label>
        <input type="password" name="password" placeholder="Min. 8 characters" required minlength="8">
      </div>
      <div>
        <label>Confirm Password</label>
        <input type="password" name="confirm" placeholder="Repeat password" required>
      </div>
      <button type="submit">Create Account</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
  </div>
</div>
<div class="toast" id="toast"></div>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>