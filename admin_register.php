<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

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
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = 'Username or Email already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'admin')");
            try {
                $stmt->execute([$username, $email, $hashed_password, $full_name]);
                $success = 'Admin account created successfully! <a href="admin_login.php" style="color: inherit; text-decoration: underline;">Login now</a>';
            } catch (PDOException $e) {
                $error = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}
$app_name = getSetting($pdo, 'app_name', 'DNA- Da NEET Academy');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration | <?php echo $app_name; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card glass animate-fade-in" style="max-width: 500px;">
        <div class="auth-header">
            <h1 class="gradient-text">Admin Registration</h1>
            <p style="color: var(--text-dim)">Create a new management account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error" style="margin-bottom: 20px; padding: 12px; border-radius: 10px; background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; text-align: center;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert-success" style="margin-bottom: 20px; padding: 12px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); border: 1px solid var(--accent); color: var(--accent); text-align: center;">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" placeholder="Admin Name" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" placeholder="admin_user" required>
                </div>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="admin@institute.com" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label>Confirm</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 15px;">
                Register Admin Account <i class="fas fa-user-plus"></i>
            </button>
        </form>

        <div style="margin-top: 30px; text-align: center; font-size: 0.85rem; color: var(--text-dim);">
            <p>Already have an account? <a href="admin_login.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Sign In</a></p>
            <p style="margin-top: 15px;"><a href="index.php" style="color: var(--text-dim); text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Portal Selection</a></p>
        </div>
    </div>
</div>

</body>
</html>
