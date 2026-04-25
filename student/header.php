<?php
require_once __DIR__ . '/../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit;
}

$pageTitle = $pageTitle ?? 'My Portal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .student-container {
            padding: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
        .nav-chips {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        .chip {
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            color: var(--text-dim);
            transition: var(--transition);
            border: 1px solid var(--glass-border);
        }
        .chip.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
    </style>
</head>
<body>

<div class="student-container">
    <div class="header-section">
        <div style="display: flex; align-items: center; gap: 20px;">
            <img src="../assets/img/logo.png" alt="DNA Logo" style="width: 60px;">
            <div>
                <h2 class="gradient-text">Hello, <?php echo $_SESSION['full_name']; ?></h2>
                <p style="color: var(--text-dim)">Track your NEET progress and materials.</p>
            </div>
        </div>
        <div style="display: flex; gap: 15px;">
            <a href="../logout.php" class="btn-primary" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <nav class="nav-chips">
        <a href="dashboard.php" class="chip <?php echo ($pageTitle == 'My Progress') ? 'active' : ''; ?>">Dashboard</a>
        <a href="materials.php" class="chip <?php echo ($pageTitle == 'Study Material') ? 'active' : ''; ?>">Study Material</a>
        <a href="exams.php" class="chip <?php echo ($pageTitle == 'Exams') ? 'active' : ''; ?>">Exams</a>
        <a href="fees.php" class="chip <?php echo ($pageTitle == 'Fee Payment') ? 'active' : ''; ?>">Fee Payment</a>
        <a href="profile.php" class="chip <?php echo ($pageTitle == 'My Profile') ? 'active' : ''; ?>">Profile</a>
    </nav>
