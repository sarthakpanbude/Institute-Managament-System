<?php
require_once 'config.php';
try {
    $dsn = "mysql:host=127.0.0.1";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    echo "Connection successful!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
