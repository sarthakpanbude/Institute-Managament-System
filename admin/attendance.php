<?php
$pageTitle = 'Attendance';
require_once 'header.php';
require_once '../includes/functions.php';

$batch_id = $_GET['batch_id'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');
$batches = getAllBatches($pdo);
$students = getStudentsByBatch($pdo, $batch_id);

$msg = '';
if (isset($_POST['save_attendance'])) {
    if (saveAttendance($pdo, $_POST['batch_id'], $_POST['date'], $_POST['records'])) {
        $msg = '<div class="badge badge-success" style="padding: 10px; margin-bottom: 20px; width: 100%; text-align: center;">Attendance saved successfully for ' . date('d M', strtotime($_POST['date'])) . '</div>';
    }
}
?>

<div class="card-header">
    <div>
        <h2 style="font-size: 1.8rem;">Daily Attendance</h2>
        <p style="color: var(--text-dim); font-size: 0.9rem;">Mark and track student presence batch-wise.</p>
    </div>
</div>

<?php echo $msg; ?>

<div class="glass" style="padding: 20px; margin-top: 20px;">
    <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
            <label style="font-size: 0.8rem;">Select Batch *</label>
            <select name="batch_id" class="form-control" onchange="this.form.submit()" required>
                <option value="">-- Select Batch --</option>
                <?php foreach ($batches as $b): ?>
                    <option value="<?php echo $b['id']; ?>" <?php echo $batch_id == $b['id'] ? 'selected' : ''; ?>><?php echo $b['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
            <label style="font-size: 0.8rem;">Date</label>
            <input type="date" name="date" value="<?php echo $date; ?>" class="form-control" onchange="this.form.submit()">
        </div>
    </form>
</div>

<?php if ($batch_id): ?>
    <div class="glass" style="padding: 0; overflow: hidden; margin-top: 20px;">
        <form method="POST">
            <input type="hidden" name="batch_id" value="<?php echo $batch_id; ?>">
            <input type="hidden" name="date" value="<?php echo $date; ?>">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: rgba(255,255,255,0.02); border-bottom: 1px solid var(--glass-border); color: var(--text-dim); font-size: 0.85rem;">
                        <th style="padding: 15px 20px;">Student Name</th>
                        <th style="padding: 15px 20px;">Email</th>
                        <th style="padding: 15px 20px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <td style="padding: 15px 20px; font-weight: 600;"><?php echo $s['full_name']; ?></td>
                        <td style="padding: 15px 20px; color: var(--text-dim);"><?php echo $s['email']; ?></td>
                        <td style="padding: 15px 20px; text-align: center;">
                            <div style="display: flex; justify-content: center; gap: 15px;">
                                <label style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                    <input type="radio" name="records[<?php echo $s['id']; ?>]" value="present" checked> P
                                </label>
                                <label style="cursor: pointer; display: flex; align-items: center; gap: 5px; color: #ef4444;">
                                    <input type="radio" name="records[<?php echo $s['id']; ?>]" value="absent"> A
                                </label>
                                <label style="cursor: pointer; display: flex; align-items: center; gap: 5px; color: #f59e0b;">
                                    <input type="radio" name="records[<?php echo $s['id']; ?>]" value="late"> L
                                </label>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="padding: 20px; display: flex; justify-content: flex-end;">
                <button type="submit" name="save_attendance" class="btn-primary" style="height: 50px; padding: 0 40px;">
                    <i class="fas fa-save"></i> Save Attendance
                </button>
            </div>
        </form>
    </div>
<?php else: ?>
    <div style="text-align: center; padding: 100px; color: var(--text-dim);">
        <i class="fas fa-arrow-up" style="font-size: 2rem; margin-bottom: 20px; display: block;"></i>
        <h3>Please select a batch to start taking attendance.</h3>
    </div>
<?php endif; ?>

</body>
</html>
