<?php
$pageTitle = 'My Progress';
require_once 'header.php';
require_once '../includes/functions.php';

$student = getStudentByUserId($pdo, $_SESSION['user_id']);
$stats = getStudentStats($pdo, $student['id']);
$upcoming = getUpcomingExams($pdo, 3);
$performance = getStudentPerformanceData($pdo, $student['id']);
?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="glass" style="padding: 25px; display: flex; align-items: center; gap: 20px;">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
            <i class="fas fa-chart-line"></i>
        </div>
        <div>
            <h3 style="font-size: 1.8rem;"><?php echo $stats['avg_score']; ?>%</h3>
            <p style="color: var(--text-dim); font-size: 0.9rem;">Average Score</p>
        </div>
    </div>
    <div class="glass" style="padding: 25px; display: flex; align-items: center; gap: 20px;">
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
            <i class="fas fa-vial"></i>
        </div>
        <div>
            <h3 style="font-size: 1.8rem;"><?php echo $stats['tests_done']; ?></h3>
            <p style="color: var(--text-dim); font-size: 0.9rem;">Tests Completed</p>
        </div>
    </div>
    <div class="glass" style="padding: 25px; display: flex; align-items: center; gap: 20px;">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div>
            <h3 style="font-size: 1.8rem;"><?php echo $stats['pending_fees']; ?></h3>
            <p style="color: var(--text-dim); font-size: 0.9rem;">Pending Fees</p>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <div class="glass" style="padding: 25px;">
        <div class="card-header">
            <h2>My Performance</h2>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        </div>
        <canvas id="studentPerfChart" height="150"></canvas>
    </div>

    <div style="display: flex; flex-direction: column; gap: 20px;">
        <div class="glass" style="padding: 25px;">
            <div class="card-header">
                <h3>Upcoming Exams</h3>
            </div>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <?php foreach ($upcoming as $exam): ?>
                <div style="border-left: 3px solid var(--primary); padding-left: 15px;">
                    <div style="font-weight: 600; font-size: 0.95rem;"><?php echo $exam['title']; ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-dim); margin-top: 3px;">
                        <?php echo date('d M, Y', strtotime($exam['exam_date'])); ?> • <?php echo $exam['duration_minutes']; ?> Min
                    </div>
                </div>
                <?php endforeach; if (empty($upcoming)) echo '<p style="color: var(--text-dim); font-size: 0.9rem;">No upcoming exams.</p>'; ?>
            </div>
            <a href="exams.php" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 20px; font-size: 0.85rem; padding: 10px;">
                Enter Exam Portal
            </a>
        </div>

        <div class="glass" style="padding: 25px;">
            <div class="card-header">
                <h3>Current Batch</h3>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <div class="avatar" style="width: 45px; height: 45px; border-radius: 12px; background: rgba(99, 102, 241, 0.1); display: flex; align-items: center; justify-content: center; color: var(--primary); font-weight: bold;">
                    <?php echo substr($student['batch_name'], 0, 1); ?>
                </div>
                <div>
                    <div style="font-weight: 600;"><?php echo $student['batch_name']; ?></div>
                    <div style="font-size: 0.75rem; color: var(--text-dim);">Academic Year 2026</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('studentPerfChart').getContext('2d');
const labels = <?php echo json_encode(array_column($performance, 'month')); ?>;
const data = <?php echo json_encode(array_column($performance, 'percentage')); ?>;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels.length ? labels : ['Mock 1', 'Mock 2', 'Mock 3'],
        datasets: [{
            label: 'Score %',
            data: data.length ? data : [65, 78, 82],
            borderColor: '#94152a',
            tension: 0.4,
            fill: true,
            backgroundColor: 'rgba(148, 21, 42, 0.1)'
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { min: 0, max: 100, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
            x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
        }
    }
});
</script>

</body>
</html>
