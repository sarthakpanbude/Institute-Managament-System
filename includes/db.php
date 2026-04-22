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
            role ENUM('admin', 'student', 'teacher') DEFAULT 'student',
            full_name VARCHAR(100),
            avatar VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "students" => "CREATE TABLE IF NOT EXISTS students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            batch_id INT,
            parent_name VARCHAR(100),
            parent_phone VARCHAR(20),
            target_year YEAR,
            address TEXT,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "batches" => "CREATE TABLE IF NOT EXISTS batches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            start_date DATE,
            end_date DATE
        )",
        "tests" => "CREATE TABLE IF NOT EXISTS tests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            test_date DATE,
            total_marks INT DEFAULT 720,
            batch_id INT,
            FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE
        )",
        "results" => "CREATE TABLE IF NOT EXISTS results (
            id INT AUTO_INCREMENT PRIMARY KEY,
            test_id INT,
            student_id INT,
            marks_obtained INT,
            rank_achieved INT,
            FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
        )"
    ];

    foreach ($tables as $name => $sql) {
        $pdo->exec($sql);
    }

} catch (PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}
?>
