<?php
$pageTitle = 'Dashboard Overview';
require_once 'header.php';
?>

        <!-- Stats Overview -->
        <div class="stat-grid">
            <div class="stat-card glass">
                <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-info">
                    <h3>124</h3>
                    <p>Total Students</p>
                </div>
            </div>

            <div class="stat-card glass">
                <div class="stat-icon" style="background: rgba(236, 72, 153, 0.1); color: var(--secondary);">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-info">
                    <h3>8</h3>
                    <p>Active Batches</p>
                </div>
            </div>

            <div class="stat-card glass">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--accent);">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h3>85%</h3>
                    <p>Average Score</p>
                </div>
            </div>

            <div class="stat-card glass">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-info">
                    <h3>₹4.2L</h3>
                    <p>Monthly Revenue</p>
                </div>
            </div>
        </div>

        <!-- Charts and Detailed Info -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div class="chart-container glass">
                <div class="card-header">
                    <h2>Performance Trends</h2>
                    <select class="glass" style="padding: 5px 10px; border-radius: 8px; color: var(--text-dim);">
                        <option>Last 6 Months</option>
                    </select>
                </div>
                <canvas id="performanceChart" height="150"></canvas>
            </div>

            <div class="glass" style="padding: 25px;">
                <div class="card-header">
                    <h2>Upcoming Tests</h2>
                </div>
                <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px;">
                    <div style="border-left: 4px solid var(--primary); padding-left: 15px;">
                        <p style="font-weight: 600;">Biology Mock #4</p>
                        <p style="font-size: 0.8rem; color: var(--text-dim);">Tomorrow, 10:00 AM • Batch Alpha</p>
                    </div>
                    <div style="border-left: 4px solid var(--secondary); padding-left: 15px;">
                        <p style="font-weight: 600;">Physics Full Syllabus</p>
                        <p style="font-size: 0.8rem; color: var(--text-dim);">22nd April • All Batches</p>
                    </div>
                </div>

                <div class="card-header" style="border-top: 1px solid var(--glass-border); pt: 20px;">
                    <h2>Center Information</h2>
                </div>
                <div style="font-size: 0.9rem; color: var(--text-dim);">
                    <p style="margin-bottom: 10px;"><i class="fas fa-map-marker-alt" style="color: var(--primary); margin-right: 10px;"></i> 1st floor, Maruti Plaza, B wing, Vidyavikas Circle, Gangapur Rd, Nashik, Maharashtra 422005</p>
                    <p><i class="fas fa-phone-alt" style="color: var(--primary); margin-right: 10px;"></i> 070204 61661</p>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
const ctx = document.getElementById('performanceChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Avg Test Score (%)',
            data: [65, 72, 68, 78, 85, 82],
            borderColor: '#6366f1',
            tension: 0.4,
            fill: true,
            backgroundColor: 'rgba(99, 102, 241, 0.1)'
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
            x: { grid: { display: false } }
        }
    }
});
</script>

</body>
</html>
