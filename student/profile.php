<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../includes/functions.php';

$student = getStudentByUserId($pdo, $_SESSION['user_id']);
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $parent_name = $_POST['parent_name'] ?? '';
    $parent_phone = $_POST['parent_phone'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    try {
        $pdo->beginTransaction();

        // Update User info
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
        $stmt->execute([$full_name, $email, $_SESSION['user_id']]);
        $_SESSION['full_name'] = $full_name;

        // Update Student info
        $stmt = $pdo->prepare("UPDATE students SET parent_name = ?, parent_phone = ? WHERE user_id = ?");
        $stmt->execute([$parent_name, $parent_phone, $_SESSION['user_id']]);

        // Update Password if provided
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $_SESSION['user_id']]);
        }

        $pdo->commit();
        $success = 'Profile updated successfully!';
        $student = getStudentByUserId($pdo, $_SESSION['user_id']); // Refresh data
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Failed to update profile: ' . $e->getMessage();
    }
}
?>

<div class="animate-fade-in">
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
        <!-- Left: Basic Info -->
        <div>
            <div class="card glass" style="text-align: center; padding: 40px 20px;">
                <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--primary); margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white; font-weight: bold;">
                    <?php echo substr($student['full_name'], 0, 1); ?>
                </div>
                <h3><?php echo htmlspecialchars($student['full_name']); ?></h3>
                <p style="color: var(--text-dim); margin-bottom: 20px;"><?php echo $student['admission_id']; ?></p>
                
                <div style="text-align: left; margin-top: 30px; border-top: 1px solid var(--glass-border); padding-top: 20px;">
                    <div style="margin-bottom: 15px;">
                        <label style="font-size: 0.8rem; color: var(--text-dim);">Batch</label>
                        <p style="font-weight: 600;"><?php echo $student['batch_name'] ?: 'Not Assigned'; ?></p>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="font-size: 0.8rem; color: var(--text-dim);">Status</label>
                        <p><span class="badge" style="background: rgba(16, 185, 129, 0.1); color: var(--accent);"><?php echo ucfirst($student['status']); ?></span></p>
                    </div>
                    <div>
                        <label style="font-size: 0.8rem; color: var(--text-dim);">Joined On</label>
                        <p><?php echo date('d M Y', strtotime($student['joining_date'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Edit Form -->
        <div>
            <div class="card glass">
                <h2 class="gradient-text" style="margin-bottom: 25px;">Account Settings</h2>

                <?php if ($success): ?>
                    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--accent); color: var(--accent); padding: 15px; border-radius: 12px; margin-bottom: 25px;">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; padding: 15px; border-radius: 12px; margin-bottom: 25px;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                        <div class="form-group">
                            <label>Parent/Guardian Name</label>
                            <input type="text" name="parent_name" class="form-control" value="<?php echo htmlspecialchars($student['parent_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Parent Phone</label>
                            <input type="text" name="parent_phone" class="form-control" value="<?php echo htmlspecialchars($student['parent_phone'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 30px;">
                        <label>New Password (Leave blank to keep current)</label>
                        <input type="password" name="new_password" class="form-control" placeholder="••••••••">
                    </div>

                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn-primary">
                            Save Changes <i class="fas fa-save" style="margin-left: 10px;"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 0.9rem;
        color: var(--text-dim);
    }
</style>

</body>
</html>
