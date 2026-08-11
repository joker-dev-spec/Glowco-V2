<?php
// --- config/database.php ---

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'glowco_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

function get_db_connection(): mysqli {
    static $conn = null;

    if ($conn === null) {
        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT);

        if ($conn->connect_error) {
            error_log("DB connection failed: " . $conn->connect_error);
            http_response_code(500);
            die("Database connection failed. Please try again later.");
        }

        $conn->set_charset('utf8mb4');
    }

    return $conn;
}
