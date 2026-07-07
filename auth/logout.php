<?php
// --- auth/logout.php ---

session_start();
session_unset();
session_destroy();

require_once dirname(__DIR__) . "/config/config.php";
header("Location: " . BASE_URL . "auth/login.php");
exit();