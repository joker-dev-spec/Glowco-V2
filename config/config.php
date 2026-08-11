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
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        define('BASE_URL', $scheme . '://' . $_SERVER['HTTP_HOST'] . '/');
    } else {
        define('BASE_URL', 'http://localhost/Glowco-V2/');
    }
}

require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'config/security.php';
require_once ROOT_PATH . 'config/paystack.php';
require_once ROOT_PATH . 'includes/flash.php';

// Helper: ensure CSRF token is generated at least once per session
if (function_exists('generate_csrf_token') && session_status() === PHP_SESSION_ACTIVE) {
    generate_csrf_token();
}
