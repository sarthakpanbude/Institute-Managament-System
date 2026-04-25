<?php
require_once __DIR__ . '/../config.php';

try {
    $dsn = "mysql:host=" . DB_HOST;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $pdo->exec("USE " . DB_NAME);

    // Create essential tables if they don't exist
    $tables = [
        "users" => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            role ENUM('admin', 'student', 'parent', 'teacher') DEFAULT 'student',
            full_name VARCHAR(100),
            avatar VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "batches" => "CREATE TABLE IF NOT EXISTS batches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            start_date DATE,
            end_date DATE,
            fees DECIMAL(10, 2) DEFAULT 0
        )",
        "parents" => "CREATE TABLE IF NOT EXISTS parents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            phone VARCHAR(20),
            address TEXT,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "students" => "CREATE TABLE IF NOT EXISTS students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            parent_id INT,
            batch_id INT,
            admission_id VARCHAR(20) UNIQUE,
            parent_name VARCHAR(100),
            parent_phone VARCHAR(20),
            target_year YEAR,
            address TEXT,
            status ENUM('active', 'passed', 'dropped') DEFAULT 'active',
            joining_date DATE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE SET NULL,
            FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE SET NULL
        )",
        "invoices" => "CREATE TABLE IF NOT EXISTS invoices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT,
            batch_id INT,
            invoice_no VARCHAR(20) NOT NULL UNIQUE,
            amount DECIMAL(10, 2),
            gst_rate DECIMAL(5, 2),
            gst_amount DECIMAL(10, 2),
            total_amount DECIMAL(10, 2),
            status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
            due_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE SET NULL
        )",
        "payments" => "CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            invoice_id INT,
            student_id INT,
            transaction_id VARCHAR(100),
            payment_gateway ENUM('razorpay', 'stripe', 'cash') DEFAULT 'razorpay',
            amount DECIMAL(10, 2),
            currency VARCHAR(10) DEFAULT 'INR',
            status VARCHAR(20),
            payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
        )",
        "exams" => "CREATE TABLE IF NOT EXISTS exams (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            batch_id INT,
            exam_date DATE,
            duration_minutes INT DEFAULT 60,
            total_marks INT DEFAULT 100,
            passing_marks INT DEFAULT 40,
            status ENUM('upcoming', 'active', 'completed') DEFAULT 'upcoming',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE
        )",
        "questions" => "CREATE TABLE IF NOT EXISTS questions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            exam_id INT,
            question_text TEXT NOT NULL,
            option_a TEXT,
            option_b TEXT,
            option_c TEXT,
            option_d TEXT,
            correct_option ENUM('A', 'B', 'C', 'D'),
            marks INT DEFAULT 1,
            FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
        )",
        "results" => "CREATE TABLE IF NOT EXISTS results (
            id INT AUTO_INCREMENT PRIMARY KEY,
            exam_id INT,
            student_id INT,
            marks_obtained INT,
            total_marks INT,
            percentage DECIMAL(5, 2),
            grade VARCHAR(10),
            rank_achieved INT,
            status ENUM('pass', 'fail') DEFAULT 'pass',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
        )",
        "attendance" => "CREATE TABLE IF NOT EXISTS attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT,
            batch_id INT,
            attendance_date DATE,
            status ENUM('present', 'absent', 'late') DEFAULT 'present',
            remarks TEXT,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE
        )",
        "notifications" => "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            title VARCHAR(255),
            message TEXT,
            type ENUM('fee', 'exam', 'holiday', 'general') DEFAULT 'general',
            status ENUM('unread', 'read') DEFAULT 'unread',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "notes" => "CREATE TABLE IF NOT EXISTS notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            batch_id INT,
            title VARCHAR(255),
            file_path VARCHAR(255),
            file_type ENUM('pdf', 'video', 'image', 'other') DEFAULT 'pdf',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE
        )",
        "settings" => "CREATE TABLE IF NOT EXISTS settings (
            setting_key VARCHAR(50) PRIMARY KEY,
            setting_value TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )"
    ];

    foreach ($tables as $name => $sql) {
        $pdo->exec($sql);
    }

    // Default Settings
    $defaultSettings = [
        'app_name' => 'DNA- Da NEET Academy',
        'contact_email' => 'info@dna-academy.com',
        'contact_phone' => '+91 9876543210',
        'address' => '123, Science Park, Education Hub, India',
        'footer_text' => '© 2026 DNA- Da NEET Academy. All rights reserved.'
    ];

    $checkSettings = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
    if ($checkSettings == 0) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($defaultSettings as $key => $value) {
            $stmt->execute([$key, $value]);
        }
    }

    // Migration: Handle existing role Enum update
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'student', 'parent', 'teacher') DEFAULT 'student'");

    // Migration: Add missing columns if they don't exist
    $migrations = [
        "batches" => ["fees" => "ALTER TABLE batches ADD COLUMN fees DECIMAL(10, 2) DEFAULT 0"],
        "students" => [
            "parent_id" => "ALTER TABLE students ADD COLUMN parent_id INT AFTER user_id",
            "admission_id" => "ALTER TABLE students ADD COLUMN admission_id VARCHAR(20) UNIQUE",
            "status" => "ALTER TABLE students ADD COLUMN status ENUM('active', 'passed', 'dropped') DEFAULT 'active'",
            "joining_date" => "ALTER TABLE students ADD COLUMN joining_date DATE"
        ],
        "invoices" => [
            "due_date" => "ALTER TABLE invoices ADD COLUMN due_date DATE AFTER status"
        ],
        "results" => [
            "marks_obtained" => "ALTER TABLE results ADD COLUMN marks_obtained INT AFTER student_id",
            "total_marks" => "ALTER TABLE results ADD COLUMN total_marks INT AFTER marks_obtained",
            "percentage" => "ALTER TABLE results ADD COLUMN percentage DECIMAL(5, 2) AFTER total_marks",
            "grade" => "ALTER TABLE results ADD COLUMN grade VARCHAR(10) AFTER percentage",
            "rank_achieved" => "ALTER TABLE results ADD COLUMN rank_achieved INT AFTER grade",
            "status" => "ALTER TABLE results ADD COLUMN status ENUM('pass', 'fail') DEFAULT 'pass' AFTER rank_achieved",
            "created_at" => "ALTER TABLE results ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
        ]
    ];

    foreach ($migrations as $table => $cols) {
        foreach ($cols as $col => $sql) {
            $check = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$col'");
            if ($check->rowCount() == 0) {
                $pdo->exec($sql);
            }
        }
    }

} catch (PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}
?>
