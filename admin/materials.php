<?php
$pageTitle = 'Study Materials';
require_once 'header.php';
require_once '../includes/functions.php';

$msg = '';
if (isset($_POST['upload'])) {
    $title = $_POST['title'];
    $batch_id = $_POST['batch_id'] ?: null;
    $type = $_POST['file_type'];
    
    $target_dir = ($type == 'timetable') ? "../uploads/timetable/" : "../uploads/notes/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    
    $file_name = time() . '_' . basename($_FILES["file"]["name"]);
    $target_file = $target_dir . $file_name;
    
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        if (uploadNote($pdo, $batch_id, $title, $file_name, ($type == 'timetable' ? 'other' : 'pdf'))) {
            $msg = '<div class="badge badge-success" style="padding: 10px; margin-bottom: 20px; width: 100%; text-align: center;">File uploaded and shared successfully!</div>';
        }
    } else {
        $msg = '<div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;">Error uploading file.</div>';
    }
}

$notes = getAllNotes($pdo);
$batches = getAllBatches($pdo);
?>

<div class="card-header">
    <div>
        <h2 style="font-size: 1.8rem;">Resource Library</h2>
        <p style="color: var(--text-dim); font-size: 0.9rem;">Upload notes, PDF materials, and academic timetables for students.</p>
    </div>
    <button class="btn-primary" onclick="openModal('uploadModal')">
        <i class="fas fa-file-upload"></i> Upload Material
    </button>
</div>

<?php echo $msg; ?>

<div class="stat-grid" style="margin-top: 20px;">
    <?php foreach ($notes as $n): ?>
    <div class="glass" style="padding: 20px; display: flex; align-items: center; gap: 15px;">
        <div class="stat-icon" style="background: rgba(255,255,255,0.05); color: var(--primary); width: 50px; height: 50px; flex-shrink: 0;">
            <i class="fas fa-file-pdf"></i>
        </div>
        <div style="flex: 1;">
            <h4 style="margin-bottom: 2px;"><?php echo $n['title']; ?></h4>
            <div style="font-size: 0.75rem; color: var(--text-dim);">
                Shared with: <strong><?php echo $n['batch_name'] ?: 'All Batches'; ?></strong>
            </div>
            <div style="margin-top: 10px; display: flex; gap: 10px;">
                <a href="../uploads/notes/<?php echo $n['file_path']; ?>" target="_blank" class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; text-decoration: none;">
                    <i class="fas fa-eye"></i> View
                </a>
                <a href="#" class="badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; text-decoration: none;">
                    <i class="fas fa-trash"></i> Delete
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; if (empty($notes)) echo '<p style="color: var(--text-dim); text-align: center; width: 100%; padding: 50px;">No materials uploaded yet.</p>'; ?>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="modal-overlay">
    <div class="auth-card glass" style="max-width: 500px; padding: 0;">
        <div class="card-header" style="padding: 20px 30px; border-bottom: 1px solid var(--glass-border); margin-bottom: 0;">
            <h2>Upload New Resource</h2>
            <button onclick="closeModal('uploadModal')" class="close-btn">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data" style="padding: 30px;">
            <div class="form-group">
                <label>Resource Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Organic Chemistry Notes - Unit 1" required>
            </div>
            <div class="form-group">
                <label>Type</label>
                <select name="file_type" class="form-control">
                    <option value="notes">Study Notes (PDF)</option>
                    <option value="timetable">Academic Timetable</option>
                </select>
            </div>
            <div class="form-group">
                <label>Target Batch</label>
                <select name="batch_id" class="form-control">
                    <option value="">All Batches (Public)</option>
                    <?php foreach ($batches as $b): ?>
                        <option value="<?php echo $b['id']; ?>"><?php echo $b['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Select File (Max 10MB) *</label>
                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx" required>
            </div>
            <button type="submit" name="upload" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 20px; height: 50px;">
                <i class="fas fa-cloud-upload-alt"></i> Start Upload
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
