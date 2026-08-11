<?php
// --- router.php ---
// 404 handling for the PHP built-in server (used by start.sh).
// Returns false so the server serves existing files/scripts normally;
// renders 404.php for everything else.

$uri  = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$file = __DIR__ . $uri;

if ($uri !== '/' && is_file($file)) {
    return false;
}

if ($uri !== '/') {
    http_response_code(404);
    require __DIR__ . '/404.php';
    return true;
}

return false;
