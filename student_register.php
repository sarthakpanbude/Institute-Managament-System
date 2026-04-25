<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? ''; // This will be their Admission ID/Username
    $password = $_POST['password'] ?? '';
    $batch_id = $_POST['batch_id'] ?? null;

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $error = 'Username or Email already exists.';
    } else {
        try {
            $pdo->beginTransaction();
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'student')");
            $stmt->execute([$username, $email, $hashed_password, $full_name]);
            $user_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO students (user_id, batch_id, admission_id, status, joining_date) VALUES (?, ?, ?, 'active', CURDATE())");
            $stmt->execute([$user_id, $batch_id, $username]);

            $pdo->commit();
            $success = 'Registration successful! <a href="student_login.php" style="color: inherit; text-decoration: underline;">Login now</a>';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}

$batches = getAllBatches($pdo);
$app_name = getSetting($pdo, 'app_name', 'DNA- Da NEET Academy');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration | <?php echo $app_name; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="auth-wrapper" style="background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('assets/img/student-bg.png');">
    <div class="auth-card glass animate-fade-in" style="max-width: 550px;">
        <div class="auth-header">
            <h1 class="gradient-text">Join DNA Academy</h1>
            <p style="color: var(--text-dim)">Start your journey towards medical excellence</p>
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
                    <input type="text" name="full_name" class="form-control" placeholder="Your Name" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="name@email.com" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Admission ID / Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
                </div>
                <div class="form-group">
                    <label>Target Batch</label>
                    <select name="batch_id" class="form-control" required>
                        <option value="">Select a Batch</option>
                        <?php foreach ($batches as $batch): ?>
                            <option value="<?php echo $batch['id']; ?>"><?php echo $batch['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Create Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 15px; background: linear-gradient(135deg, var(--secondary), var(--primary));">
                Enroll Now <i class="fas fa-rocket"></i>
            </button>
        </form>

        <div style="margin-top: 30px; text-align: center; font-size: 0.85rem; color: var(--text-dim);">
            <p>Already registered? <a href="student_login.php" style="color: var(--secondary); text-decoration: none; font-weight: 600;">Sign In</a></p>
            <p style="margin-top: 15px;"><a href="index.php" style="color: var(--text-dim); text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Portal Selection</a></p>
        </div>
    </div>
</div>

</body>
</html>
