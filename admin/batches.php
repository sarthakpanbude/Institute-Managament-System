<?php
$pageTitle = 'Manage Batches';
require_once 'header.php';
require_once '../includes/functions.php';

// Handle Batch Creation
$msg = '';
if (isset($_POST['add_batch'])) {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $session = $_POST['session'] ?? 'Morning';
    $fees = $_POST['fees'] ?? 0;

    if (createBatch($pdo, $name, $desc, $start, $end, $fees, $session)) {
        $msg = '<div class="badge badge-success" style="padding: 10px; margin-bottom: 20px;">Batch created successfully!</div>';
    }
}

// Filtering
$session_filter = $_GET['session'] ?? '';
$search_filter = $_GET['search'] ?? '';
$batches = getAllBatches($pdo);

if (!empty($session_filter)) {
    $batches = array_filter($batches, function($b) use ($session_filter) {
        return $b['session'] === $session_filter;
    });
}

if (!empty($search_filter)) {
    $batches = array_filter($batches, function($b) use ($search_filter) {
        return stripos($b['name'], $search_filter) !== false || stripos($b['description'], $search_filter) !== false;
    });
}
?>

<div class="card-header">
    <h2>Academic Batches</h2>
    <div style="display: flex; gap: 15px; align-items: center;">
        <form method="GET" style="display: flex; gap: 10px; align-items: center; margin-bottom: 0;">
            <div class="input-with-icon" style="margin-bottom: 0;">
                <i class="fas fa-search"></i>
                <input type="text" name="search" class="form-control" placeholder="Search batches..." value="<?php echo htmlspecialchars($search_filter); ?>" style="width: 200px;">
            </div>
            <select name="session" class="form-control" style="width: 150px;" onchange="this.form.submit()">
                <option value="">All Sessions</option>
                <option value="Morning" <?php echo $session_filter == 'Morning' ? 'selected' : ''; ?>>Morning</option>
                <option value="Afternoon" <?php echo $session_filter == 'Afternoon' ? 'selected' : ''; ?>>Afternoon</option>
                <option value="Evening" <?php echo $session_filter == 'Evening' ? 'selected' : ''; ?>>Evening</option>
            </select>
            <button type="submit" class="btn-primary" style="padding: 10px 15px;"><i class="fas fa-filter"></i></button>
        </form>
        <button class="btn-primary" onclick="document.getElementById('batchModal').style.display='flex'">
            <i class="fas fa-plus"></i> Create New Batch
        </button>
    </div>
</div>

<?php echo $msg; ?>

<div class="stat-grid">
    <?php foreach ($batches as $b): ?>
    <div class="glass" style="padding: 25px; position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
            <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
                <i class="fas fa-layer-group"></i>
            </div>
            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 5px;">
                <span class="badge badge-success">Active</span>
                <span class="badge" style="background: var(--glass-border); color: var(--text-main); font-size: 0.7rem;"><?php echo $b['session']; ?></span>
            </div>
        </div>
        <h3 style="font-size: 1.4rem; margin-bottom: 5px;"><?php echo $b['name']; ?></h3>
        <p style="color: var(--text-dim); font-size: 0.9rem; margin-bottom: 10px;">
            <?php echo $b['description']; ?>
        </p>
        <div style="margin-bottom: 20px;">
            <span style="font-size: 1.2rem; font-weight: bold; color: var(--primary);">₹<?php echo number_format($b['fees'], 2); ?></span>
            <small style="color: var(--text-dim);">+ 18% GST</small>
        </div>
        <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--glass-border); padding-top: 15px; font-size: 0.85rem; color: var(--text-dim);">
            <span><i class="far fa-calendar-alt"></i> <?php echo date('M Y', strtotime($b['start_date'])); ?></span>
            <span><i class="fas fa-users"></i> 0 Students</span>
        </div>
    </div>
    <?php endforeach; if (empty($batches)) echo '<p style="color: var(--text-dim); padding: 40px; text-align: center; width: 100%;">No batches created yet.</p>'; ?>
</div>

<!-- Add Batch Modal -->
<div id="batchModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
    <div class="auth-card glass" style="max-width: 500px;">
        <div class="card-header">
            <h2>Create New Batch</h2>
            <button onclick="document.getElementById('batchModal').style.display='none'" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <form method="POST">
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Batch Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. NEET 2026" required>
                </div>
                <div class="form-group">
                    <label>Session</label>
                    <select name="session" class="form-control" required>
                        <option value="Morning">Morning</option>
                        <option value="Afternoon">Afternoon</option>
                        <option value="Evening">Evening</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label>Course Fees (Base Amount)</label>
                <div class="input-with-icon">
                    <i class="fas fa-indian-rupee-sign"></i>
                    <input type="number" name="fees" class="form-control" placeholder="0.00" step="0.01" required>
                </div>
            </div>
            <button type="submit" name="add_batch" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 20px;">
                Initialize Batch
            </button>
        </form>
    </div>
</div>

</body>
</html>
