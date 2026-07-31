<?php


define('DB_HOST', 'localhost');
define('DB_NAME', 'glowco_db');
define('DB_USER', 'root');
define('DB_PASS', '');

function get_db_connection(): mysqli {
    static $conn = null;

    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            error_log("DB connection failed: " . $conn->connect_error);
            die("Database connection failed.");
        }

        $conn->set_charset('utf8mb4');
    }

    return $conn;
}