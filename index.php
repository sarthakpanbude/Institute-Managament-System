<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$app_name = getSetting($pdo, 'app_name', 'DNA- Da NEET Academy');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $app_name; ?> | Welcome</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .portal-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('assets/img/hero.png');
            background-size: cover;
            background-position: center;
            padding: 20px;
        }

        .portal-selection {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 900px;
            width: 100%;
        }

        .portal-card {
            text-align: center;
            padding: 50px 30px;
            transition: var(--transition);
            cursor: pointer;
            text-decoration: none;
            color: var(--text-main);
            border: 2px solid transparent;
        }

        .portal-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.05);
        }

        .portal-icon {
            font-size: 4rem;
            margin-bottom: 25px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .portal-card h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .portal-card p {
            color: var(--text-dim);
            margin-bottom: 30px;
            line-height: 1.6;
        }
    </style>
</head>
<body>

<div class="portal-container">
    <div style="width: 100%; max-width: 900px;">
        <div class="auth-header" style="margin-bottom: 50px; text-align: center;">
            <img src="assets/img/logo.png" alt="DNA Logo" style="width: 150px; margin-bottom: 20px;">
            <h1 class="gradient-text" style="font-size: 3rem;"><?php echo $app_name; ?></h1>
            <p style="color: var(--text-dim); font-size: 1.2rem;">Choose your portal to continue</p>
        </div>

        <div class="portal-selection">
            <a href="admin_login.php" class="portal-card glass animate-fade-in">
                <div class="portal-icon"><i class="fas fa-user-shield"></i></div>
                <h2>Admin Portal</h2>
                <p>Management, Analytics, Finance, and System Configuration</p>
                <div class="btn-primary" style="display: inline-flex;">Enter Admin Portal <i class="fas fa-chevron-right" style="margin-left: 10px;"></i></div>
            </a>

            <a href="student_login.php" class="portal-card glass animate-fade-in" style="animation-delay: 0.1s;">
                <div class="portal-icon"><i class="fas fa-user-graduate"></i></div>
                <h2>Student Portal</h2>
                <p>View Materials, Take Exams, Check Results, and Fees</p>
                <div class="btn-primary" style="display: inline-flex; background: linear-gradient(135deg, var(--secondary), var(--primary));">Access Student Portal <i class="fas fa-chevron-right" style="margin-left: 10px;"></i></div>
            </a>
        </div>

        <div style="text-align: center; margin-top: 50px; color: var(--text-dim);">
            <p><?php echo getSetting($pdo, 'footer_text', '© 2026 DNA Academy'); ?></p>
        </div>
    </div>
</div>

</body>
</html>
