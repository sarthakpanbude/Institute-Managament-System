<?php
$pageTitle = 'Dashboard Overview';
require_once 'header.php';
require_once '../includes/functions.php';

// Fetch Dynamic Stats
$total_students = getTotalStudentsCount($pdo);
$total_batches = count(getAllBatches($pdo));
$avg_score = round(getAverageScore($pdo), 1);
$total_revenue = getTotalRevenue($pdo);
$upcoming_exams = getUpcomingExams($pdo, 3);
$revenue_data = getMonthlyRevenueData($pdo);
$performance_data = getMonthlyPerformanceData($pdo);

// Format revenue for display
if ($total_revenue >= 100000) {
    $revenue_display = "₹" . round($total_revenue / 100000, 1) . "L";
} else {
    $revenue_display = "₹" . number_format($total_revenue);
}
?>

        <!-- Stats Overview -->
        <div class="stat-grid">
            <div class="stat-card glass">
                <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_students; ?></h3>
                    <p>Total Students</p>
                </div>
            </div>

            <div class="stat-card glass">
                <div class="stat-icon" style="background: rgba(236, 72, 153, 0.1); color: var(--secondary);">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_batches; ?></h3>
                    <p>Active Batches</p>
                </div>
            </div>

            <div class="stat-card glass">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--accent);">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $avg_score; ?>%</h3>
                    <p>Average Score</p>
                </div>
            </div>

            <div class="stat-card glass">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $revenue_display; ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
        </div>

        <!-- Charts and Detailed Info -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div class="chart-container glass">
                <div class="card-header">
                    <h2>Performance & Revenue</h2>
                    <div style="display: flex; gap: 10px;">
                        <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;">Performance</span>
                        <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">Revenue</span>
                    </div>
                </div>
                <canvas id="mainChart" height="150"></canvas>
            </div>

            <div class="glass" style="padding: 25px;">
                <div class="card-header">
                    <h2>Upcoming Exams</h2>
                    <a href="exams.php" style="font-size: 0.8rem; color: var(--primary); text-decoration: none;">View All</a>
                </div>
                <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px;">
                    <?php if (empty($upcoming_exams)): ?>
                        <p style="color: var(--text-dim); font-size: 0.9rem;">No upcoming exams scheduled.</p>
                    <?php else: ?>
                        <?php foreach ($upcoming_exams as $exam): ?>
                            <div style="border-left: 4px solid var(--primary); padding-left: 15px;">
                                <p style="font-weight: 600;"><?php echo $exam['title']; ?></p>
                                <p style="font-size: 0.8rem; color: var(--text-dim);">
                                    <?php echo date('d M', strtotime($exam['exam_date'])); ?> • <?php echo $exam['batch_name']; ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="card-header" style="border-top: 1px solid var(--glass-border); padding-top: 20px;">
                    <h2>Notifications</h2>
                </div>
                <div style="font-size: 0.9rem; color: var(--text-dim);">
                    <p style="margin-bottom: 10px;"><i class="fas fa-bell" style="color: var(--primary); margin-right: 10px;"></i> Fee reminders sent to 12 students.</p>
                    <p><i class="fas fa-info-circle" style="color: var(--secondary); margin-right: 10px;"></i> Holiday notice scheduled for tomorrow.</p>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
const ctx = document.getElementById('mainChart').getContext('2d');
const labels = <?php echo json_encode(array_column($revenue_data, 'month')); ?>;
const revenue = <?php echo json_encode(array_column($revenue_data, 'total')); ?>;
const scores = <?php echo json_encode(array_column($performance_data, 'avg_score')); ?>;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels.length ? labels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [
            {
                label: 'Revenue',
                data: revenue.length ? revenue : [0, 0, 0, 0, 0, 0],
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y'
            },
            {
                label: 'Avg Score',
                data: scores.length ? scores : [60, 65, 70, 75, 80, 85],
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { 
                type: 'linear', display: true, position: 'left',
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { color: '#94a3b8' }
            },
            y1: { 
                type: 'linear', display: true, position: 'right',
                grid: { drawOnChartArea: false },
                ticks: { color: '#94a3b8' }
            },
            x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
        }
    }
});
</script>

</body>
</html>
