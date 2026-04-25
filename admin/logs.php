<?php
$pageTitle = 'Activity Logs';
require_once 'header.php';
require_once '../includes/functions.php';

$logFile = __DIR__ . '/../logs/activity.log';
$logs = [];
if (file_exists($logFile)) {
    $lines = file($logFile);
    $logs = array_reverse($lines); // Show newest first
}

$apiLogFile = __DIR__ . '/../logs/api_integrations.log';
$apiLogs = [];
if (file_exists($apiLogFile)) {
    $apiLines = file($apiLogFile);
    $apiLogs = array_reverse($apiLines);
}
?>

<div class="card-header">
    <div>
        <h2 style="font-size: 1.8rem;">System Audit Logs</h2>
        <p style="color: var(--text-dim); font-size: 0.9rem;">Monitor administrator actions and API activity.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 30px;">
    <div class="glass" style="padding: 0; overflow: hidden;">
        <div class="card-header" style="padding: 20px; border-bottom: 1px solid var(--glass-border); margin-bottom: 0;">
            <h3>User Activity</h3>
            <button class="badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; cursor: pointer;">Clear Logs</button>
        </div>
        <div style="max-height: 600px; overflow-y: auto; font-family: monospace; font-size: 0.85rem; padding: 20px; line-height: 1.8;">
            <?php foreach ($logs as $line): ?>
                <div style="padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.03); color: var(--text-dim);">
                    <?php 
                        // Simple highlighting
                        $line = htmlspecialchars($line);
                        $line = preg_replace('/(\[.*?\])/', '<span style="color:var(--primary)">$1</span>', $line);
                        $line = str_replace('Failed', '<span style="color:#ef4444">Failed</span>', $line);
                        $line = str_replace('Registered', '<span style="color:#10b981">Registered</span>', $line);
                        echo $line;
                    ?>
                </div>
            <?php endforeach; if (empty($logs)) echo '<p style="text-align: center; color: var(--text-dim);">No activity logs found.</p>'; ?>
        </div>
    </div>

    <div class="glass" style="padding: 0; overflow: hidden;">
        <div class="card-header" style="padding: 20px; border-bottom: 1px solid var(--glass-border); margin-bottom: 0;">
            <h3>API Dispatch Logs</h3>
        </div>
        <div style="max-height: 600px; overflow-y: auto; font-family: monospace; font-size: 0.75rem; padding: 20px; line-height: 1.8;">
            <?php foreach ($apiLogs as $line): ?>
                <div style="padding: 8px; border-bottom: 1px solid rgba(255,255,255,0.03); color: #94a3b8;">
                    <?php echo htmlspecialchars($line); ?>
                </div>
            <?php endforeach; if (empty($apiLogs)) echo '<p style="text-align: center; color: var(--text-dim);">No API activity found.</p>'; ?>
        </div>
    </div>
</div>

</body>
</html>
