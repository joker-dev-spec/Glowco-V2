<?php
// --- config/config.php ---

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__) . '/');
}

define('BASE_URL', 'http://localhost/Glowco-V2/');

require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'config/security.php';
require_once ROOT_PATH . 'includes/flash.php';