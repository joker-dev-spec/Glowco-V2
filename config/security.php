<?php


function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function is_admin(): bool {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

function hash_password(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verify_password(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

function sanitize_input(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool {
    return isset($_SESSION['csrf_token'], $token) && hash_equals($_SESSION['csrf_token'], $token);
}

function secure_session_start(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');

    session_start();
}

function rate_limit(string $action, int $max = 5, int $window = 300): bool {
    static $cleaned = false;

    try {
        $conn = get_db_connection();

        if (!$cleaned) {
            $cleaned = true;
            $conn->query("DELETE FROM rate_limits WHERE window_start < " . (time() - 86400));
        }

        $ip  = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $now = time();

        $stmt = $conn->prepare(
            "SELECT attempts, window_start FROM rate_limits WHERE action = ? AND ip_hash = ?"
        );
        $stmt->bind_param("ss", $action, $ip);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row || (int)$row['window_start'] < $now - $window) {
            $stmt = $conn->prepare(
                "INSERT INTO rate_limits (action, ip_hash, attempts, window_start) VALUES (?, ?, 1, ?)
                 ON DUPLICATE KEY UPDATE attempts = 1, window_start = VALUES(window_start)"
            );
            $stmt->bind_param("ssi", $action, $ip, $now);
            $stmt->execute();
            return true;
        }

        if ((int)$row['attempts'] >= $max) {
            return false;
        }

        $stmt = $conn->prepare(
            "UPDATE rate_limits SET attempts = attempts + 1 WHERE action = ? AND ip_hash = ?"
        );
        $stmt->bind_param("ss", $action, $ip);
        $stmt->execute();

        return true;
    } catch (Throwable $e) {
        return true;
    }
}

function rate_limit_clear(string $action): void {
    try {
        $conn = get_db_connection();
        $ip   = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $stmt = $conn->prepare("DELETE FROM rate_limits WHERE action = ? AND ip_hash = ?");
        $stmt->bind_param("ss", $action, $ip);
        $stmt->execute();
    } catch (Throwable $e) {
    }
}