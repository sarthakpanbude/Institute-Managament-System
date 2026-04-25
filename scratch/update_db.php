<?php
require_once '../includes/db.php';

try {
    $pdo->exec("ALTER TABLE batches ADD COLUMN IF NOT EXISTS session VARCHAR(50) DEFAULT 'Morning'");
    echo "Column 'session' added successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
