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
            } elseif ($role === 'parent') {
                header("Location: parent/dashboard.php");
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
    <title>Login | DNA- Da NEET Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="manifest" href="manifest.json">
    <style>
        .auth-wrapper {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('assets/img/hero.png');
            background-size: cover;
            background-position: center;
        }
    </style>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js');
            });
        }
    </script>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card glass">
        <div class="auth-header">
            <img src="assets/img/logo.png" alt="DNA Logo" style="width: 120px; margin-bottom: 20px;">
            <h1 class="gradient-text" style="font-size: 2.2rem;">DNA- Da NEET Academy</h1>
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
                <div class="input-with-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
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
                <label>Portal Role</label>
                <div class="input-with-icon">
                    <i class="fas fa-users-cog"></i>
                    <select name="role" class="form-control" style="appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394a3b8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 1rem center; background-size: 0.65em auto; padding-right: 2.5rem;">
                        <option value="admin">Administrator Portal</option>
                        <option value="student">Student Portal</option>
                        <option value="parent">Parent Portal</option>
                        <option value="teacher">Faculty Portal</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                Sign In <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div style="margin-top: 30px; text-align: center; font-size: 0.85rem; color: var(--text-dim);">
            <p>New Admin? <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Register as Admin</a></p>
            <p style="margin-top: 10px;">Student registration? <a href="#" style="color: var(--primary); text-decoration: none; font-weight: 600;">Contact Center</a></p>
        </div>
    </div>
</div>

</body>
</html>
