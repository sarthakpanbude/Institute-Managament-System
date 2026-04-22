<?php
require_once __DIR__ . '/db.php';

/**
 * Get all batches
 */
function getAllBatches($pdo) {
    $stmt = $pdo->query("SELECT * FROM batches ORDER BY name ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all students with their batch and user info
 */
function getAllStudents($pdo) {
    $sql = "SELECT s.*, u.full_name, u.email, b.name as batch_name 
            FROM students s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN batches b ON s.batch_id = b.id
            ORDER BY u.full_name ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Create a new batch
 */
function createBatch($pdo, $name, $desc, $start, $end) {
    $stmt = $pdo->prepare("INSERT INTO batches (name, description, start_date, end_date) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$name, $desc, $start, $end]);
}

/**
 * Register a new student (Creates user first, then student profile)
 */
function registerStudent($pdo, $username, $password, $email, $fullName, $batchId) {
    try {
        $pdo->beginTransaction();

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, 'student')");
        $stmt->execute([$username, $hashedPassword, $email, $fullName]);
        $userId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO students (user_id, batch_id) VALUES (?, ?)");
        $stmt->execute([$userId, $batchId]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

/**
 * Get all mock tests
 */
function getAllTests($pdo) {
    $stmt = $pdo->query("SELECT t.*, b.name as batch_name FROM tests t LEFT JOIN batches b ON t.batch_id = b.id ORDER BY t.test_date DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Create a new mock test
 */
function createTest($pdo, $title, $date, $marks, $batch_id) {
    $stmt = $pdo->prepare("INSERT INTO tests (title, test_date, total_marks, batch_id) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$title, $date, $marks, $batch_id]);
}
?>
