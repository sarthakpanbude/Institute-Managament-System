<?php
$pageTitle = 'Notifications';
require_once 'header.php';
require_once '../includes/functions.php';

$msg = '';
if (isset($_POST['send_bulk'])) {
    $data = [
        'target' => $_POST['target'],
        'title' => $_POST['title'],
        'message' => $_POST['message'],
        'type' => $_POST['type'],
        'channels' => $_POST['channels'] ?? []
    ];
    
    if (sendNotification($pdo, $data)) {
        $msg = '<div class="badge badge-success" style="padding: 10px; margin-bottom: 20px; width: 100%; text-align: center;">Notification dispatched successfully!</div>';
    }
}

$notifications = getAllNotifications($pdo);
?>

<div class="card-header">
    <div>
        <h2 style="font-size: 1.8rem;">Push Notification Center</h2>
        <p style="color: var(--text-dim); font-size: 0.9rem;">Send fee reminders, exam alerts, and holiday notices.</p>
    </div>
    <button class="btn-primary" onclick="openModal('notifyModal')">
        <i class="fas fa-paper-plane"></i> New Notification
    </button>
</div>

<?php echo $msg; ?>

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 20px; margin-top: 20px;">
    <div class="glass" style="padding: 0; overflow: hidden;">
        <div class="card-header" style="padding: 20px; border-bottom: 1px solid var(--glass-border); margin-bottom: 0;">
            <h3>Recent History</h3>
        </div>
        <div style="max-height: 600px; overflow-y: auto; padding: 10px;">
            <?php foreach ($notifications as $n): ?>
            <div style="padding: 15px; border-bottom: 1px solid var(--glass-border); display: flex; gap: 15px;">
                <div class="stat-icon" style="width: 40px; height: 40px; flex-shrink: 0; background: rgba(255,255,255,0.05); color: var(--primary);">
                    <?php 
                        $icons = ['fee' => 'fa-wallet', 'exam' => 'fa-vial', 'holiday' => 'fa-umbrella-beach', 'general' => 'fa-bell'];
                        echo '<i class="fas ' . ($icons[$n['type']] ?? 'fa-bell') . '"></i>';
                    ?>
                </div>
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <h4 style="margin-bottom: 5px;"><?php echo $n['title']; ?></h4>
                        <span style="font-size: 0.7rem; color: var(--text-dim);"><?php echo date('d M, H:i', strtotime($n['created_at'])); ?></span>
                    </div>
                    <p style="font-size: 0.85rem; color: var(--text-dim); margin-bottom: 8px;"><?php echo $n['message']; ?></p>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: var(--primary); font-size: 0.7rem;">
                            To: <?php echo $n['user_name'] ?: 'Multiple Recipients'; ?>
                        </span>
                        <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: var(--accent); font-size: 0.7rem;">Dispatched</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($notifications)): ?>
                <div style="padding: 40px; text-align: center; color: var(--text-dim);">No notification history.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="glass" style="padding: 25px; height: fit-content;">
        <h3 style="margin-bottom: 20px;">API Status</h3>
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.9rem;"><i class="fab fa-google" style="color: #4285F4; width: 25px;"></i> Firebase (FCM)</span>
                <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">Connected</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.9rem;"><i class="fas fa-sms" style="color: #f59e0b; width: 25px;"></i> SMS Gateway</span>
                <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">Active</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.9rem;"><i class="fab fa-whatsapp" style="color: #25D366; width: 25px;"></i> WhatsApp API</span>
                <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">Ready</span>
            </div>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--glass-border);">
            <p style="font-size: 0.8rem; color: var(--text-dim); line-height: 1.5;">
                <i class="fas fa-info-circle"></i> Integrations are currently in <strong>Simulation Mode</strong>. API keys can be configured in System Settings.
            </p>
        </div>
    </div>
</div>

<!-- Notify Modal -->
<div id="notifyModal" class="modal-overlay">
    <div class="auth-card glass" style="max-width: 550px; padding: 0;">
        <div class="card-header" style="padding: 20px 30px; border-bottom: 1px solid var(--glass-border); margin-bottom: 0;">
            <h2>Send New Notification</h2>
            <button onclick="closeModal('notifyModal')" class="close-btn">&times;</button>
        </div>
        <form method="POST" style="padding: 30px;">
            <div class="form-group">
                <label>Target Audience *</label>
                <select name="target" class="form-control" required>
                    <option value="all_students">All Students</option>
                    <option value="all_parents">All Parents</option>
                    <option value="all">Everyone</option>
                </select>
            </div>
            <div class="form-group">
                <label>Notification Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Tomorrow is a Holiday" required>
            </div>
            <div class="form-group">
                <label>Message Content *</label>
                <textarea name="message" class="form-control" rows="4" placeholder="Enter the detailed message here..." required></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Category</label>
                    <select name="type" class="form-control">
                        <option value="general">General Update</option>
                        <option value="fee">Fee Reminder</option>
                        <option value="exam">Exam Alert</option>
                        <option value="holiday">Holiday Notice</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Extra Delivery Channels</label>
                    <div style="display: flex; gap: 10px; margin-top: 8px;">
                        <label title="SMS"><input type="checkbox" name="channels[]" value="sms"> <i class="fas fa-sms"></i></label>
                        <label title="WhatsApp"><input type="checkbox" name="channels[]" value="whatsapp"> <i class="fab fa-whatsapp"></i></label>
                        <label title="Firebase"><input type="checkbox" name="channels[]" value="fcm" checked> <i class="fas fa-mobile-alt"></i></label>
                    </div>
                </div>
            </div>
            <button type="submit" name="send_bulk" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 20px; height: 50px;">
                <i class="fas fa-paper-plane"></i> Blast Notification
            </button>
        </form>
    </div>
</div>

<style>
    .modal-overlay {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
        background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(5px); z-index: 1000; 
        align-items: center; justify-content: center;
    }
    .close-btn { background: none; border: none; color: white; font-size: 2rem; cursor: pointer; opacity: 0.5; transition: 0.3s; }
    .close-btn:hover { opacity: 1; transform: rotate(90deg); }
</style>

<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
</script>

</body>
</html>
