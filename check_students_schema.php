<?php
require_once 'includes/db.php';
$stmt = $pdo->query("DESCRIBE students");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>
