<?php
$pageTitle = 'My Progress';
require_once 'header.php';
require_once '../includes/functions.php';

// Fetch Student Batch
$stmt = $pdo->prepare("SELECT b.name, b.description FROM students s JOIN batches b ON s.batch_id = b.id WHERE s.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$batch = $stmt->fetch();
?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
    <div class="glass" style="padding: 30px;">
        <div class="card-header">
            <h3>Academic Performance</h3>
        </div>
        <div style="margin-top: 20px;">
            <div style="margin-bottom: 25px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Biology (Botany + Zoology)</span>
                    <span style="color: var(--accent);">88%</span>
                </div>
                <div style="height: 8px; width: 100%; background: rgba(255,255,255,0.05); border-radius: 10px;">
                    <div style="height: 100%; width: 88%; background: var(--accent); border-radius: 10px;"></div>
                </div>
            </div>

            <div style="margin-bottom: 25px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Physics (Mechanics)</span>
                    <span style="color: var(--primary);">72%</span>
                </div>
                <div style="height: 8px; width: 100%; background: rgba(255,255,255,0.05); border-radius: 10px;">
                    <div style="height: 100%; width: 72%; background: var(--primary); border-radius: 10px;"></div>
                </div>
            </div>

            <div style="margin-bottom: 25px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Chemistry (Organic)</span>
                    <span style="color: var(--secondary);">82%</span>
                </div>
                <div style="height: 8px; width: 100%; background: rgba(255,255,255,0.05); border-radius: 10px;">
                    <div style="height: 100%; width: 82%; background: var(--secondary); border-radius: 10px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="glass" style="padding: 25px; margin-bottom: 30px;">
            <p style="color: var(--text-dim); font-size: 0.85rem; margin-bottom: 10px;">Current Batch</p>
            <h4 style="font-size: 1.2rem; color: var(--primary);"><?php echo $batch['name'] ?? 'Not Assigned'; ?></h4>
            <p style="font-size: 0.85rem; color: var(--text-dim); margin-top: 5px;"><?php echo $batch['description'] ?? ''; ?></p>
        </div>

        <div class="glass" style="padding: 25px;">
            <h3>Upcoming Mock Tests</h3>
            <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 15px;">
                <div style="background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; border-left: 3px solid var(--primary);">
                    <p style="font-weight: 600; font-size: 0.95rem;">Full Length NEET #4</p>
                    <p style="color: var(--text-dim); font-size: 0.8rem;">24th April • 10:00 AM</p>
                </div>
                <div style="background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; border-left: 3px solid var(--secondary);">
                    <p style="font-weight: 600; font-size: 0.95rem;">Biology Unit Test</p>
                    <p style="color: var(--text-dim); font-size: 0.8rem;">28th April • 02:00 PM</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="glass" style="margin-top: 40px; padding: 30px;">
    <h3>Recent Test Scores</h3>
    <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
        <tr style="border-bottom: 1px solid var(--glass-border); color: var(--text-dim);">
            <th style="padding: 15px; text-align: left;">Date</th>
            <th style="padding: 15px; text-align: left;">Title</th>
            <th style="padding: 15px; text-align: left;">Score</th>
            <th style="padding: 15px; text-align: left;">Percentile</th>
            <th style="padding: 15px; text-align: left;">Action</th>
        </tr>
        <tr style="border-bottom: 1px solid var(--glass-border);">
            <td style="padding: 15px;">15 Apr</td>
            <td style="padding: 15px;">Physics Kinematics</td>
            <td style="padding: 15px;">164 / 180</td>
            <td style="padding: 15px;"><span class="badge badge-success">98.2%</span></td>
            <td style="padding: 15px;"><a href="#" class="gradient-text">View Analysis</a></td>
        </tr>
        <tr>
            <td style="padding: 15px;">08 Apr</td>
            <td style="padding: 15px;">Botany Photosynthesis</td>
            <td style="padding: 15px;">152 / 180</td>
            <td style="padding: 15px;"><span class="badge badge-success">94.5%</span></td>
            <td style="padding: 15px;"><a href="#" class="gradient-text">View Analysis</a></td>
        </tr>
    </table>
</div>

</body>
</html>
