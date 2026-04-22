<?php
require_once 'includes/db.php';

echo "<h2>NEET IMS System Setup</h2>";

try {
    // Check if admin exists
    $stmt = $pdo->query("SELECT id FROM users WHERE username = 'admin'");
    if ($stmt->rowCount() == 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, 'admin')");
        $stmt->execute(['admin', $password, 'admin@institute.com', 'System Administrator']);
        echo "<p style='color: green;'>Admin account created successfully!</p>";
        echo "<p>Username: <b>admin</b></p>";
        echo "<p>Password: <b>admin123</b></p>";
    } else {
        echo "<p style='color: orange;'>Admin account already exists.</p>";
    }

    // Create a sample batch
    $stmt = $pdo->query("SELECT id FROM batches LIMIT 1");
    if ($stmt->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO batches (name, description, start_date, end_date) VALUES (?, ?, ?, ?)");
        $stmt->execute(['NEET 2026 Morning', 'Premium morning batch for high achievers.', '2025-06-01', '2026-05-15']);
        echo "<p style='color: green;'>Sample batch created!</p>";
    }

    echo "<hr><p>Initialization complete. <a href='index.php'>Go to Login</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
