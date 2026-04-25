<?php
$pageTitle = 'Settings';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        updateSetting($pdo, 'app_name', $_POST['app_name']);
        updateSetting($pdo, 'contact_email', $_POST['contact_email']);
        updateSetting($pdo, 'contact_phone', $_POST['contact_phone']);
        updateSetting($pdo, 'address', $_POST['address']);
        updateSetting($pdo, 'footer_text', $_POST['footer_text']);
        
        $pdo->commit();
        $success = 'Settings updated successfully!';
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Failed to update settings: ' . $e->getMessage();
    }
}

$app_name = getSetting($pdo, 'app_name', 'DNA- Da NEET Academy');
$contact_email = getSetting($pdo, 'contact_email', 'info@dna-academy.com');
$contact_phone = getSetting($pdo, 'contact_phone', '+91 9876543210');
$address = getSetting($pdo, 'address', '123, Science Park, India');
$footer_text = getSetting($pdo, 'footer_text', '© 2026 DNA Academy');
?>

<div class="card glass animate-fade-in">
    <div class="card-header">
        <div>
            <h2 class="gradient-text"><i class="fas fa-cog"></i> System Settings</h2>
            <p style="color: var(--text-dim)">Configure your institute's global parameters</p>
        </div>
    </div>

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

    <form action="" method="POST" class="settings-form">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <div class="form-section">
                <h4 style="margin-bottom: 20px; color: var(--primary);"><i class="fas fa-building"></i> Institute Identity</h4>
                
                <div class="form-group">
                    <label>Application Name</label>
                    <input type="text" name="app_name" class="form-control" value="<?php echo htmlspecialchars($app_name); ?>" required>
                </div>

                <div class="form-group">
                    <label>Footer Copyright Text</label>
                    <input type="text" name="footer_text" class="form-control" value="<?php echo htmlspecialchars($footer_text); ?>">
                </div>
            </div>

            <div class="form-section">
                <h4 style="margin-bottom: 20px; color: var(--primary);"><i class="fas fa-address-book"></i> Contact Information</h4>
                
                <div class="form-group">
                    <label>Support Email</label>
                    <input type="email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($contact_email); ?>" required>
                </div>

                <div class="form-group">
                    <label>Contact Phone</label>
                    <input type="text" name="contact_phone" class="form-control" value="<?php echo htmlspecialchars($contact_phone); ?>" required>
                </div>

                <div class="form-group">
                    <label>Physical Address</label>
                    <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($address); ?></textarea>
                </div>
            </div>
        </div>

        <div style="margin-top: 40px; border-top: 1px solid var(--glass-border); padding-top: 25px; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Save All Changes
            </button>
        </div>
    </form>
</div>

<style>
    .settings-form .form-group {
        margin-bottom: 20px;
    }
    .settings-form label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--text-dim);
        font-size: 0.9rem;
    }
    .form-section {
        background: rgba(255, 255, 255, 0.02);
        padding: 25px;
        border-radius: 16px;
        border: 1px solid var(--glass-border);
    }
</style>

    </main>
</div>

</body>
</html>
