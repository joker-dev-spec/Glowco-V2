<?php
// --- config/config.php ---

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__) . '/');
}

// BASE_URL: prefer environment variable, fall back to host detection, then localhost default
if (!defined('BASE_URL')) {
    $envBase = getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? null);
    if ($envBase) {
        define('BASE_URL', rtrim($envBase, '/') . '/');
    } elseif (!empty($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
        $isLocal = str_starts_with($host, 'localhost') || str_starts_with($host, '127.0.0.1');
        // Railway terminates TLS at its proxy, so $_SERVER['HTTPS'] is unset
        // there; treat every non-local host as HTTPS.
        $scheme = $isLocal ? 'http' : 'https';
        define('BASE_URL', $scheme . '://' . $host . '/');
    } else {
        define('BASE_URL', 'http://localhost/Glowco-V2/');
    }
}

require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'config/security.php';
require_once ROOT_PATH . 'config/payments.php';
require_once ROOT_PATH . 'includes/flash.php';

// Helper: ensure CSRF token is generated at least once per session
if (function_exists('generate_csrf_token') && session_status() === PHP_SESSION_ACTIVE) {
    generate_csrf_token();
}
