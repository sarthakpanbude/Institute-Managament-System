<?php
$pageTitle = 'Exams & Results';
require_once 'header.php';
require_once '../includes/functions.php';

// Handle Exam Creation
$msg = '';
if (isset($_POST['add_exam'])) {
    if (createExam($pdo, $_POST)) {
        $msg = '<div class="badge badge-success" style="padding: 10px; margin-bottom: 20px; width: 100%; text-align: center;">Exam scheduled successfully!</div>';
    } else {
        $msg = '<div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;">Error while scheduling exam.</div>';
    }
}

$exams = getAllExams($pdo);
$batches = getAllBatches($pdo);
?>

<div class="card-header">
    <div>
        <h2 style="font-size: 1.8rem;">Exam Management</h2>
        <p style="color: var(--text-dim); font-size: 0.9rem;">Create online MCQ exams and manage results.</p>
    </div>
    <button class="btn-primary" onclick="openModal('examModal')">
        <i class="fas fa-plus"></i> Schedule New Exam
    </button>
</div>

<?php echo $msg; ?>

<div class="glass" style="padding: 0; overflow: hidden; margin-top: 20px;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background: rgba(255,255,255,0.02); border-bottom: 1px solid var(--glass-border); color: var(--text-dim); font-size: 0.85rem;">
                <th style="padding: 15px 20px;">Exam Details</th>
                <th style="padding: 15px 20px;">Date & Duration</th>
                <th style="padding: 15px 20px;">Batch</th>
                <th style="padding: 15px 20px;">Marks</th>
                <th style="padding: 15px 20px;">Status</th>
                <th style="padding: 15px 20px; text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($exams as $e): ?>
            <tr style="border-bottom: 1px solid var(--glass-border); transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                <td style="padding: 15px 20px;">
                    <div style="font-weight: 600;"><?php echo $e['title']; ?></div>
                    <div style="font-size: 0.75rem; color: var(--text-dim);"><?php echo substr($e['description'], 0, 50); ?>...</div>
                </td>
                <td style="padding: 15px 20px;">
                    <div style="font-size: 0.9rem;"><i class="far fa-calendar-alt"></i> <?php echo date('d M, Y', strtotime($e['exam_date'])); ?></div>
                    <div style="font-size: 0.75rem; color: var(--text-dim);"><i class="far fa-clock"></i> <?php echo $e['duration_minutes']; ?> Minutes</div>
                </td>
                <td style="padding: 15px 20px;">
                    <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
                        <?php echo $e['batch_name'] ?: 'All Batches'; ?>
                    </span>
                </td>
                <td style="padding: 15px 20px;">
                    <div style="font-weight: 600;"><?php echo $e['total_marks']; ?></div>
                    <div style="font-size: 0.7rem; color: var(--accent);">Pass: <?php echo $e['passing_marks']; ?></div>
                </td>
                <td style="padding: 15px 20px;">
                    <?php 
                        $status_colors = ['upcoming' => '#6366f1', 'active' => '#10b981', 'completed' => '#94a3b8'];
                        $color = $status_colors[$e['status']] ?? '#94a3b8';
                    ?>
                    <span class="badge" style="background: <?php echo $color; ?>22; color: <?php echo $color; ?>;">
                        <?php echo ucfirst($e['status']); ?>
                    </span>
                </td>
                <td style="padding: 15px 20px; text-align: right;">
                    <div style="display: flex; justify-content: flex-end; gap: 8px;">
                        <a href="exam_questions.php?id=<?php echo $e['id']; ?>" class="action-btn" style="color: var(--accent);" title="Manage Questions">
                            <i class="fas fa-question-circle"></i>
                        </a>
                        <a href="exam_results.php?id=<?php echo $e['id']; ?>" class="action-btn" style="color: var(--secondary);" title="View Results">
                            <i class="fas fa-poll"></i>
                        </a>
                        <button class="action-btn" style="color: var(--primary);" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($exams)): ?>
            <tr><td colspan="6" style="padding: 40px; text-align: center; color: var(--text-dim);">No exams found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Exam Modal -->
<div id="examModal" class="modal-overlay">
    <div class="auth-card glass" style="max-width: 600px; padding: 0;">
        <div class="card-header" style="padding: 20px 30px; border-bottom: 1px solid var(--glass-border); margin-bottom: 0;">
            <h2>Schedule New Exam</h2>
            <button onclick="closeModal('examModal')" class="close-btn">&times;</button>
        </div>
        <form method="POST" style="padding: 30px;">
            <div class="form-group">
                <label>Exam Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. NEET Biology Mock #5" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Briefly describe the syllabus..."></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Exam Date *</label>
                    <input type="date" name="exam_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Duration (Minutes) *</label>
                    <input type="number" name="duration_minutes" class="form-control" value="60" required>
                </div>
                <div class="form-group">
                    <label>Total Marks *</label>
                    <input type="number" name="total_marks" class="form-control" value="100" required>
                </div>
                <div class="form-group">
                    <label>Passing Marks *</label>
                    <input type="number" name="passing_marks" class="form-control" value="40" required>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Target Batch</label>
                    <select name="batch_id" class="form-control">
                        <option value="">All Batches</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?php echo $b['id']; ?>"><?php echo $b['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" name="add_exam" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 20px; height: 50px;">
                Create Exam
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
    .action-btn { background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); width: 34px; height: 34px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; transition: 0.3s; cursor: pointer; text-decoration: none; }
    .action-btn:hover { background: rgba(255,255,255,0.1); transform: translateY(-2px); }
</style>

<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
</script>

</body>
</html>
