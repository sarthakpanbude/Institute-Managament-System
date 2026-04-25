<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND role = 'student'");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        header("Location: student/dashboard.php");
        exit;
    } else {
        $error = 'Invalid student credentials.';
    }
}
$app_name = getSetting($pdo, 'app_name', 'DNA- Da NEET Academy');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login | <?php echo $app_name; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="auth-wrapper" style="background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('assets/img/student-bg.png');">
    <div class="auth-card glass animate-fade-in">
        <div class="auth-header">
            <div class="portal-icon" style="font-size: 3rem; margin-bottom: 10px; background: linear-gradient(135deg, var(--secondary), var(--primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><i class="fas fa-user-graduate"></i></div>
            <h1 class="gradient-text">Student Login</h1>
            <p style="color: var(--text-dim)">Access your learning resources</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error" style="margin-bottom: 20px; padding: 12px; border-radius: 10px; background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; text-align: center;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Admission ID / Email</label>
                <div class="input-with-icon">
                    <i class="fas fa-id-card"></i>
                    <input type="text" name="username" class="form-control" placeholder="DNA-2024-XXX" required>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 15px; background: linear-gradient(135deg, var(--secondary), var(--primary));">
                Enter Portal <i class="fas fa-graduation-cap"></i>
            </button>
        </form>

        <div style="margin-top: 30px; text-align: center; font-size: 0.85rem; color: var(--text-dim);">
            <p>New Student? <a href="student_register.php" style="color: var(--secondary); text-decoration: none; font-weight: 600;">Register here</a></p>
            <p style="margin-top: 15px;"><a href="index.php" style="color: var(--text-dim); text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Portal Selection</a></p>
        </div>
    </div>
</div>

</body>
</html>
