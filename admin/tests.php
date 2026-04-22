<?php
$pageTitle = 'Mock Tests';
require_once 'header.php';
require_once '../includes/functions.php';

// Handle Test Creation
$msg = '';
if (isset($_POST['add_test'])) {
    $title = $_POST['title'];
    $date = $_POST['test_date'];
    $marks = $_POST['total_marks'];
    $batch_id = $_POST['batch_id'] ?: null;

    if (createTest($pdo, $title, $date, $marks, $batch_id)) {
        $msg = '<div class="badge badge-success" style="padding: 10px; margin-bottom: 20px;">Test scheduled successfully!</div>';
    }
}

$tests = getAllTests($pdo);
$batches = getAllBatches($pdo);
?>

<div class="card-header">
    <h2>NEET Mock Exams</h2>
    <button class="btn-primary" onclick="document.getElementById('testModal').style.display='flex'">
        <i class="fas fa-plus"></i> Schedule New Test
    </button>
</div>

<?php echo $msg; ?>

<div class="glass" style="padding: 20px;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-dim);">
                <th style="padding: 15px;">Exam Title</th>
                <th style="padding: 15px;">Date</th>
                <th style="padding: 15px;">Batch</th>
                <th style="padding: 15px;">Max Marks</th>
                <th style="padding: 15px;">Status</th>
                <th style="padding: 15px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tests as $t): ?>
            <tr style="border-bottom: 1px solid var(--glass-border);">
                <td style="padding: 15px; font-weight: 600;"><?php echo $t['title']; ?></td>
                <td style="padding: 15px;"><?php echo date('d M, Y', strtotime($t['test_date'])); ?></td>
                <td style="padding: 15px;"><?php echo $t['batch_name'] ?: 'All Batches'; ?></td>
                <td style="padding: 15px;"><?php echo $t['total_marks']; ?></td>
                <td style="padding: 15px;">
                    <?php 
                    $today = date('Y-m-d');
                    if ($t['test_date'] > $today) {
                        echo '<span class="badge" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">Upcoming</span>';
                    } else {
                        echo '<span class="badge" style="background: rgba(16, 185, 129, 0.1); color: var(--accent);">Completed</span>';
                    }
                    ?>
                </td>
                <td style="padding: 15px;">
                    <a href="#" class="gradient-text" style="text-decoration: none; font-weight: 600; font-size: 0.9rem;">Update Scores</a>
                </td>
            </tr>
            <?php endforeach; if (empty($tests)) echo '<tr><td colspan="6" style="text-align: center; padding: 40px; color: var(--text-dim);">No tests scheduled.</td></tr>'; ?>
        </tbody>
    </table>
</div>

<!-- Add Test Modal -->
<div id="testModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
    <div class="auth-card glass" style="max-width: 500px;">
        <div class="card-header">
            <h2>Schedule New Exam</h2>
            <button onclick="document.getElementById('testModal').style.display='none'" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <form method="POST">
            <div class="form-group">
                <label>Test Title</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Full Length Mock Test #1" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Test Date</label>
                    <input type="date" name="test_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Total Marks</label>
                    <input type="number" name="total_marks" class="form-control" value="720" required>
                </div>
            </div>
            <div class="form-group">
                <label>Target Batch (Optional)</label>
                <select name="batch_id" class="form-control">
                    <option value="">All Batches</option>
                    <?php foreach ($batches as $b): ?>
                        <option value="<?php echo $b['id']; ?>"><?php echo $b['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="add_test" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 20px;">
                Confirm Schedule
            </button>
        </form>
    </div>
</div>

</body>
</html>
