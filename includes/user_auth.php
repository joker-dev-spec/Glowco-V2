<?php
// --- includes/user_auth.php ---

session_start();
require_once ROOT_PATH . "config/config.php";

if (!is_logged_in()) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}