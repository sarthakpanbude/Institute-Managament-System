<?php
require_once 'includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';

    if ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if username or email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = 'Username or Email already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'admin')");
            try {
                $stmt->execute([$username, $email, $hashed_password, $full_name]);
                $success = 'Admin account created successfully! You can now log in.';
            } catch (PDOException $e) {
                $error = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card glass">
        <div class="auth-header">
            <img src="assets/img/logo.png" alt="DNA Logo" style="width: 120px; margin-bottom: 20px;">
            <h1 class="gradient-text" style="font-size: 2.2rem;">DNA- Da NEET Academy</h1>
            <p style="color: var(--text-dim)">Create an Administrator Account</p>
        </div>

        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-size: 0.9rem;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--accent); color: var(--accent); padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-size: 0.9rem;">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <div class="input-with-icon">
                    <i class="fas fa-id-card"></i>
                    <input type="text" name="full_name" class="form-control" placeholder="Admin Name" required>
                </div>
            </div>

            <div class="form-group">
                <label>Username</label>
                <div class="input-with-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
                </div>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <div class="input-with-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="form-control" placeholder="admin@dna-academy.com" required>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <div class="input-with-icon">
                    <i class="fas fa-shield-alt"></i>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                Register Admin <i class="fas fa-user-plus"></i>
            </button>
        </form>

        <div style="margin-top: 30px; text-align: center; font-size: 0.85rem; color: var(--text-dim);">
            <p>Already have an account? <a href="index.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Back to Login</a></p>
        </div>
    </div>
</div>

</body>
</html>
