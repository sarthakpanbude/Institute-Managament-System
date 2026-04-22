<?php
// Global Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'neet_ims');

define('APP_NAME', 'NEET Excel Institute');
define('APP_URL', 'http://localhost/Institute');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
