<?php
require_once __DIR__ . '/../includes/db.php';
// Authentication check would go here

$pageTitle = $pageTitle ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            padding: 30px 20px;
            border-right: 1px solid var(--glass-border);
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 100;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }

        .sidebar-brand {
            margin-bottom: 40px;
            padding: 0 15px;
        }

        .sidebar-nav {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 15px;
            text-decoration: none;
            color: var(--text-dim);
            border-radius: 12px;
            transition: var(--transition);
            font-weight: 500;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .nav-link i {
            font-size: 1.1rem;
            width: 24px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-icon {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-info h3 {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .stat-info p {
            color: var(--text-dim);
            font-size: 0.9rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-container {
            padding: 25px;
            margin-bottom: 30px;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success { background: rgba(16, 185, 129, 0.1); color: var(--accent); }
    </style>
</head>
<body>

<div class="dashboard-container">
    <aside class="sidebar glass">
        <div class="sidebar-brand">
            <img src="../assets/img/logo.png" alt="DNA Logo" style="width: 60px; margin-bottom: 10px;">
            <h2 class="gradient-text" style="font-size: 1.2rem;">DNA- Da NEET Academy</h2>
        </div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php echo ($pageTitle == 'Dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="students.php" class="nav-link <?php echo ($pageTitle == 'Manage Students') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Students
                </a>
            </li>
            <li class="nav-item">
                <a href="batches.php" class="nav-link <?php echo ($pageTitle == 'Manage Batches') ? 'active' : ''; ?>">
                    <i class="fas fa-layer-group"></i> Batches
                </a>
            </li>
            <li class="nav-item">
                <a href="fees.php" class="nav-link <?php echo ($pageTitle == 'Fee Management') ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice-dollar"></i> Fee Management
                </a>
            </li>
            <li class="nav-item">
                <a href="exams.php" class="nav-link <?php echo ($pageTitle == 'Exams & Results') ? 'active' : ''; ?>">
                    <i class="fas fa-vial"></i> Exams & Results
                </a>
            </li>
            <li class="nav-item">
                <a href="attendance.php" class="nav-link <?php echo ($pageTitle == 'Attendance') ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i> Attendance
                </a>
            </li>
            <li class="nav-item">
                <a href="notifications.php" class="nav-link <?php echo ($pageTitle == 'Notifications') ? 'active' : ''; ?>">
                    <i class="fas fa-bell"></i> Push Notifications
                </a>
            </li>
            <li class="nav-item">
                <a href="materials.php" class="nav-link <?php echo ($pageTitle == 'Study Materials') ? 'active' : ''; ?>">
                    <i class="fas fa-book"></i> Study Materials
                </a>
            </li>
            <li class="nav-item">
                <a href="logs.php" class="nav-link <?php echo ($pageTitle == 'Activity Logs') ? 'active' : ''; ?>">
                    <i class="fas fa-clipboard-list"></i> Audit Logs
                </a>
            </li>
            <li class="nav-item">
                <a href="settings.php" class="nav-link <?php echo ($pageTitle == 'Settings') ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i> Settings & PWA
                </a>
            </li>
            <li class="nav-item" style="margin-top: 40px;">
                <a href="../logout.php" class="nav-link" style="color: #ef4444;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <div class="user-greeting">
                <h1>Welcome Back, <span class="gradient-text">Admin</span></h1>
                <p style="color: var(--text-dim)">Here's what's happening today.</p>
            </div>
            <div class="user-profile" style="display: flex; align-items: center; gap: 15px;">
                <div class="notifications">
                    <i class="far fa-bell" style="font-size: 1.2rem; color: var(--text-dim);"></i>
                </div>
                <div class="avatar glass" style="width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border-color: var(--primary);">
                    A
                </div>
            </div>
        </header>
