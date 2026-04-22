<?php
require_once 'includes/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';

    // Real authentication logic
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['role'] === $role) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Redirect based on role
            if ($role === 'admin') {
                header("Location: admin/dashboard.php");
            } elseif ($role === 'student') {
                header("Location: student/dashboard.php");
            }
            exit;
        } else {
            $error = 'Access denied for this portal role.';
        }
    } else {
        $error = 'Invalid credentials or user does not exist.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .auth-wrapper {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('assets/img/hero.png');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card glass">
        <div class="auth-header">
            <h1 class="gradient-text">NEET Excel</h1>
            <p style="color: var(--text-dim)">Excellence in Medical Preparation</p>
        </div>

        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-size: 0.9rem;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Username or Email</label>
                <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label>Portal Role</label>
                <select name="role" class="form-control">
                    <option value="student">Student Portal</option>
                    <option value="teacher">Faculty Portal</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                Sign In <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div style="margin-top: 30px; text-align: center; font-size: 0.85rem; color: var(--text-dim);">
            <p>Don't have an account? <a href="#" style="color: var(--primary); text-decoration: none; font-weight: 600;">Contact Center</a></p>
        </div>
    </div>
</div>

</body>
</html>
