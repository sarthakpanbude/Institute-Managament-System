<?php
$pageTitle = 'Exams';
require_once 'header.php';
require_once '../includes/functions.php';

$student = getStudentByUserId($pdo, $_SESSION['user_id']);
$available_exams = getAvailableExams($pdo, $student['batch_id']);
?>

<div class="card-header">
    <div>
        <h2 style="font-size: 1.8rem;">Online Examination Portal</h2>
        <p style="color: var(--text-dim); font-size: 0.9rem;">Attempt your scheduled mock tests and view your results.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-top: 30px;">
    <?php foreach ($available_exams as $e): ?>
    <div class="glass" style="padding: 25px; border-top: 5px solid var(--primary);">
        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
            <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <?php echo date('d M, Y', strtotime($e['exam_date'])); ?>
            </span>
            <span class="badge" style="background: rgba(255,255,255,0.05); color: var(--text-main);">
                <i class="far fa-clock"></i> <?php echo $e['duration_minutes']; ?> Min
            </span>
        </div>
        <h3 style="font-size: 1.2rem; margin-bottom: 10px;"><?php echo $e['title']; ?></h3>
        <p style="font-size: 0.85rem; color: var(--text-dim); margin-bottom: 20px; min-height: 40px;">
            <?php echo $e['description'] ?: 'Prepare well for your selection. This test follows the latest NEET pattern.'; ?>
        </p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; border-top: 1px solid var(--glass-border); padding-top: 15px;">
            <div>
                <div style="font-size: 0.7rem; color: var(--text-dim);">Total Marks</div>
                <div style="font-weight: 700;"><?php echo $e['total_marks']; ?></div>
            </div>
            <div>
                <div style="font-size: 0.7rem; color: var(--text-dim);">Passing Marks</div>
                <div style="font-weight: 700;"><?php echo $e['passing_marks']; ?></div>
            </div>
        </div>

        <?php 
            $can_start = (date('Y-m-d') == $e['exam_date']);
        ?>
        <a href="take_exam.php?id=<?php echo $e['id']; ?>" class="btn-primary" style="width: 100%; justify-content: center; height: 45px; <?php echo !$can_start ? 'opacity: 0.5; pointer-events: none;' : ''; ?>">
            <i class="fas fa-play"></i> <?php echo $can_start ? 'Start Examination' : 'Available on ' . date('d M', strtotime($e['exam_date'])); ?>
        </a>
    </div>
    <?php endforeach; ?>
    
    <?php if (empty($available_exams)): ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 100px; color: var(--text-dim);">
            <i class="fas fa-vial" style="font-size: 3rem; margin-bottom: 20px;"></i>
            <h3>No exams are currently scheduled for your batch.</h3>
        </div>
    <?php endif; ?>
</div>

<div class="card-header" style="margin-top: 60px;">
    <h2>Past Results</h2>
</div>
<div class="glass" style="padding: 20px; margin-top: 20px; text-align: center; color: var(--text-dim);">
    <p>Your previous test results will appear here after evaluation.</p>
</div>

</body>
</html>
