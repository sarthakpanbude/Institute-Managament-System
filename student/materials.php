<?php
$pageTitle = 'Study Material';
require_once 'header.php';
require_once '../includes/functions.php';

$student = getStudentByUserId($pdo, $_SESSION['user_id']);
$all_notes = getAllNotes($pdo);

// Filter notes for student's batch or public notes
$notes = array_filter($all_notes, function($n) use ($student) {
    return is_null($n['batch_id']) || $n['batch_id'] == $student['batch_id'];
});
?>

<div class="card-header">
    <div>
        <h2 style="font-size: 1.8rem;">Resource Library</h2>
        <p style="color: var(--text-dim); font-size: 0.9rem;">Access your study notes, PDF materials, and timetables.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">
    <?php foreach ($notes as $n): ?>
    <div class="glass" style="padding: 25px; transition: var(--transition); position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; right: 0; padding: 10px;">
            <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: var(--primary); font-size: 0.7rem;">
                <?php echo $n['batch_name'] ?: 'Public'; ?>
            </span>
        </div>
        <div style="display: flex; align-items: flex-start; gap: 20px;">
            <div class="stat-icon" style="background: rgba(255,255,255,0.05); color: var(--primary); border-radius: 15px; width: 60px; height: 60px;">
                <i class="fas fa-file-pdf" style="font-size: 1.8rem;"></i>
            </div>
            <div style="flex: 1;">
                <h3 style="font-size: 1.1rem; margin-bottom: 5px;"><?php echo $n['title']; ?></h3>
                <p style="font-size: 0.8rem; color: var(--text-dim); margin-bottom: 15px;">
                    Uploaded on <?php echo date('d M, Y', strtotime($n['created_at'])); ?>
                </p>
                <div style="display: flex; gap: 10px;">
                    <a href="../uploads/notes/<?php echo $n['file_path']; ?>" target="_blank" class="btn-primary" style="padding: 8px 15px; font-size: 0.85rem;">
                        <i class="fas fa-download"></i> Download
                    </a>
                    <a href="../uploads/notes/<?php echo $n['file_path']; ?>" target="_blank" class="glass" style="padding: 8px 15px; font-size: 0.85rem; text-decoration: none; border-radius: 10px; color: var(--text-main);">
                        <i class="fas fa-eye"></i> Preview
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($notes)): ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 100px; color: var(--text-dim);">
            <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 20px;"></i>
            <h3>No study materials available for your batch yet.</h3>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
