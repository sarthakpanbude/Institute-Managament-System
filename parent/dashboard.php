<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
    header("Location: ../index.php");
    exit;
}

$pageTitle = 'Parent Dashboard';
$my_students = getParentStudents($pdo, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Portal | DNA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .parent-container { padding: 40px; max-width: 1000px; margin: 0 auto; }
        .student-preview-card {
            padding: 30px; margin-bottom: 30px; border-top: 4px solid var(--secondary);
        }
    </style>
</head>
<body>

<div class="parent-container">
    <div class="card-header">
        <div>
            <h1 class="gradient-text">Welcome, <?php echo $_SESSION['full_name']; ?></h1>
            <p style="color: var(--text-dim)">Monitoring your ward's academic journey.</p>
        </div>
        <a href="../logout.php" class="btn-primary" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div style="margin-top: 40px;">
        <h2 style="margin-bottom: 20px;">Linked Students</h2>
        <?php foreach ($my_students as $s): 
            $stats = getStudentStats($pdo, $s['id']);
        ?>
        <div class="glass student-preview-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px;">
                <div style="display: flex; gap: 20px; align-items: center;">
                    <div class="avatar" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(45deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; color: white;">
                        <?php echo substr($s['full_name'], 0, 1); ?>
                    </div>
                    <div>
                        <h3 style="font-size: 1.4rem;"><?php echo $s['full_name']; ?></h3>
                        <p style="color: var(--text-dim);"><?php echo $s['batch_name']; ?> • ID: <?php echo $s['admission_id']; ?></p>
                    </div>
                </div>
                <div style="text-align: right;">
                    <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; margin-bottom: 5px;">Active Student</span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; border-top: 1px solid var(--glass-border); padding-top: 25px;">
                <div style="text-align: center;">
                    <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Test Score</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary);"><?php echo $stats['avg_score']; ?>%</div>
                    <div style="font-size: 0.7rem; color: var(--text-dim);">Average Performance</div>
                </div>
                <div style="text-align: center; border-left: 1px solid var(--glass-border); border-right: 1px solid var(--glass-border);">
                    <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Attendance</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--secondary);">92%</div>
                    <div style="font-size: 0.7rem; color: var(--text-dim);">Overall Presence</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Fee Status</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: <?php echo $stats['pending_fees'] > 0 ? '#f59e0b' : '#10b981'; ?>;">
                        <?php echo $stats['pending_fees'] > 0 ? 'Pending' : 'Cleared'; ?>
                    </div>
                    <div style="font-size: 0.7rem; color: var(--text-dim);">Financial Standing</div>
                </div>
            </div>
            
            <div style="margin-top: 30px; display: flex; gap: 10px;">
                <a href="#" class="btn-primary" style="flex: 1; justify-content: center; font-size: 0.85rem;">View Detailed Report</a>
                <a href="#" class="glass" style="flex: 1; justify-content: center; font-size: 0.85rem; text-decoration: none; border-radius: 12px; display: flex; align-items: center; color: white;">Download Fee Receipt</a>
            </div>
        </div>
        <?php endforeach; if (empty($my_students)) echo '<div class="glass" style="padding: 100px; text-align: center; color: var(--text-dim);">No students are currently linked to your account. Please contact the administrator.</div>'; ?>
    </div>
</div>

</body>
</html>
