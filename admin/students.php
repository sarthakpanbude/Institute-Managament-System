<?php
$pageTitle = 'Manage Students';
require_once 'header.php';
require_once '../includes/functions.php';

// Handle Student Registration
$msg = '';
if (isset($_POST['add_student'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $batch_id = $_POST['batch_id'];

    if (registerStudent($pdo, $username, $password, $email, $full_name, $batch_id)) {
        $msg = '<div class="badge badge-success" style="padding: 10px; margin-bottom: 20px;">Student registered successfully!</div>';
    } else {
        $msg = '<div style="color: #ef4444; margin-bottom: 20px;">Failed to register student.</div>';
    }
}

$students = getAllStudents($pdo);
$batches = getAllBatches($pdo);
?>

<div class="card-header">
    <h2>Students Directory</h2>
    <button class="btn-primary" onclick="document.getElementById('studentModal').style.display='flex'">
        <i class="fas fa-plus"></i> Add New Student
    </button>
</div>

<?php echo $msg; ?>

<div class="glass" style="padding: 20px; overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-dim);">
                <th style="padding: 15px;">Name</th>
                <th style="padding: 15px;">Email</th>
                <th style="padding: 15px;">Batch</th>
                <th style="padding: 15px;">Contact</th>
                <th style="padding: 15px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $s): ?>
            <tr style="border-bottom: 1px solid var(--glass-border);">
                <td style="padding: 15px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="avatar" style="width: 32px; height: 32px; font-size: 0.8rem; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                            <?php echo substr($s['full_name'], 0, 1); ?>
                        </div>
                        <?php echo $s['full_name']; ?>
                    </div>
                </td>
                <td style="padding: 15px;"><?php echo $s['email']; ?></td>
                <td style="padding: 15px;">
                    <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
                        <?php echo $s['batch_name'] ?: 'Not Assigned'; ?>
                    </span>
                </td>
                <td style="padding: 15px;"><?php echo $s['parent_phone'] ?: '--'; ?></td>
                <td style="padding: 15px;">
                    <button style="background: none; border: none; color: var(--text-dim); cursor: pointer;"><i class="fas fa-edit"></i></button>
                    <button style="background: none; border: none; color: #ef4444; cursor: pointer; margin-left: 10px;"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
            <?php endforeach; if (empty($students)) echo '<tr><td colspan="5" style="text-align: center; padding: 40px; color: var(--text-dim);">No students enrolled yet.</td></tr>'; ?>
        </tbody>
    </table>
</div>

<!-- Add Student Modal -->
<div id="studentModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
    <div class="auth-card glass" style="max-width: 600px;">
        <div class="card-header">
            <h2>Add New Student</h2>
            <button onclick="document.getElementById('studentModal').style.display='none'" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email ID</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Select Batch</label>
                    <select name="batch_id" class="form-control">
                        <option value="">Select Batch</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?php echo $b['id']; ?>"><?php echo $b['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" name="add_student" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 20px;">
                Enroll Student
            </button>
        </form>
    </div>
</div>

<script>
// Update Sidebar active state
document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
document.querySelector('a[href="#"]').classList.add('active'); // Temporary placeholder
</script>

</body>
</html>
