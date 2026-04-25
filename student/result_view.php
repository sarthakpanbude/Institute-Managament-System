<?php
$pageTitle = 'Exam Result';
require_once 'header.php';
require_once '../includes/functions.php';

$result_id = $_GET['id'] ?? null;
if (!$result_id) { header("Location: dashboard.php"); exit; }

$stmt = $pdo->prepare("SELECT r.*, e.title as exam_title 
                      FROM results r 
                      JOIN exams e ON r.exam_id = e.id 
                      WHERE r.id = ? AND r.student_id = (SELECT id FROM students WHERE user_id = ?)");
$stmt->execute([$result_id, $_SESSION['user_id']]);
$res = $stmt->fetch();

if (!$res) { die("Result not found."); }
?>

<div class="glass" style="max-width: 600px; margin: 50px auto; padding: 40px; text-align: center;">
    <div style="width: 100px; height: 100px; border-radius: 50%; background: <?php echo $res['status'] == 'pass' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; font-size: 2.5rem; color: <?php echo $res['status'] == 'pass' ? '#10b981' : '#ef4444'; ?>;">
        <i class="fas <?php echo $res['status'] == 'pass' ? 'fa-check' : 'fa-times'; ?>"></i>
    </div>
    
    <h1 style="font-size: 2rem; margin-bottom: 10px;"><?php echo $res['status'] == 'pass' ? 'Congratulations!' : 'Keep Trying!'; ?></h1>
    <p style="color: var(--text-dim); margin-bottom: 40px;">You have completed <strong><?php echo $res['exam_title']; ?></strong></p>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px;">
        <div style="padding: 20px; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid var(--glass-border);">
            <div style="font-size: 2.2rem; font-weight: 800; color: var(--primary);"><?php echo $res['marks_obtained']; ?></div>
            <div style="font-size: 0.8rem; color: var(--text-dim);">Score / <?php echo $res['total_marks']; ?></div>
        </div>
        <div style="padding: 20px; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid var(--glass-border);">
            <div style="font-size: 2.2rem; font-weight: 800; color: var(--accent);"><?php echo round($res['percentage'], 1); ?>%</div>
            <div style="font-size: 0.8rem; color: var(--text-dim);">Percentage</div>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; background: rgba(255,255,255,0.02); border-radius: 12px; border: 1px solid var(--glass-border); margin-bottom: 30px;">
        <div style="text-align: left;">
            <div style="font-size: 1.1rem; font-weight: 700;">Grade: <?php echo $res['grade']; ?></div>
            <div style="font-size: 0.75rem; color: var(--text-dim);">Performance Level</div>
        </div>
        <div class="badge <?php echo $res['status'] == 'pass' ? 'badge-success' : 'badge-danger'; ?>" style="font-size: 0.9rem; padding: 8px 20px;">
            <?php echo strtoupper($res['status']); ?>
        </div>
    </div>

    <div style="display: flex; gap: 15px;">
        <a href="dashboard.php" class="glass" style="flex: 1; padding: 15px; border-radius: 12px; text-decoration: none; color: white;">
            Back to Dashboard
        </a>
        <button class="btn-primary" style="flex: 1;" onclick="window.print()">
            <i class="fas fa-file-pdf"></i> Download Result
        </button>
    </div>
</div>

</body>
</html>
