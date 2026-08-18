<?php
// --- includes/admin_auth.php ---

if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . "config/config.php";
secure_session_start();

if (!is_logged_in()) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

if (!is_admin()) {
    header("Location: " . BASE_URL . "user/dashboard.php?error=access_denied");
    exit();
}