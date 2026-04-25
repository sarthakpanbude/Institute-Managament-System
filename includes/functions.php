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
 * Get all students with filtering and pagination
 */
function getAllStudents($pdo, $search = '', $batch_filter = '', $status_filter = '', $session_filter = '', $limit = 10, $offset = 0) {
    $params = [];
    $where = " WHERE 1=1 ";

    if (!empty($search)) {
        $where .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR s.admission_id LIKE ?) ";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if (!empty($batch_filter)) {
        $where .= " AND s.batch_id = ? ";
        $params[] = $batch_filter;
    }

    if (!empty($status_filter)) {
        $where .= " AND s.status = ? ";
        $params[] = $status_filter;
    }

    if (!empty($session_filter)) {
        $where .= " AND b.session = ? ";
        $params[] = $session_filter;
    }

    $sql = "SELECT s.*, u.full_name, u.email, u.username, b.name as batch_name, b.session as batch_session, i.id as invoice_id 
            FROM students s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN batches b ON s.batch_id = b.id
            LEFT JOIN invoices i ON s.id = i.student_id
            $where
            ORDER BY u.full_name ASC
            LIMIT $limit OFFSET $offset";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Count total students for pagination
 */
function getTotalStudentsCount($pdo, $search = '', $batch_filter = '', $status_filter = '', $session_filter = '') {
    $params = [];
    $where = " WHERE 1=1 ";

    if (!empty($search)) {
        $where .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR s.admission_id LIKE ?) ";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if (!empty($batch_filter)) {
        $where .= " AND s.batch_id = ? ";
        $params[] = $batch_filter;
    }

    if (!empty($status_filter)) {
        $where .= " AND s.status = ? ";
        $params[] = $status_filter;
    }

    if (!empty($session_filter)) {
        $where .= " AND b.session = ? ";
        $params[] = $session_filter;
    }

    $sql = "SELECT COUNT(*) FROM students s 
            JOIN users u ON s.user_id = u.id 
            LEFT JOIN batches b ON s.batch_id = b.id
            $where";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

/**
 * Create a new batch
 */
function createBatch($pdo, $name, $desc, $start, $end, $fees = 0, $session = 'Morning') {
    $stmt = $pdo->prepare("INSERT INTO batches (name, description, start_date, end_date, fees, session) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$name, $desc, $start, $end, $fees, $session]);
}

/**
 * Register a new student
 */
function registerStudent($pdo, $data) {
    try {
        $pdo->beginTransaction();

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, 'student')");
        $stmt->execute([$data['username'], $hashedPassword, $data['email'], $data['full_name']]);
        $userId = $pdo->lastInsertId();

        $admissionId = "DNA-" . date('Y') . "-" . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        $stmt = $pdo->prepare("INSERT INTO students (user_id, batch_id, admission_id, parent_name, parent_phone, status, joining_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId, 
            $data['batch_id'], 
            $admissionId, 
            $data['parent_name'] ?? '', 
            $data['parent_phone'] ?? '', 
            'active', 
            date('Y-m-d')
        ]);
        $studentId = $pdo->lastInsertId();

        generateInvoice($pdo, $studentId, $data['batch_id']);

        $pdo->commit();
        sendInvoiceNotification($data['email'], $data['full_name'], "INV-" . time());
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

/**
 * Get student by ID
 */
function getStudentById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT s.*, u.full_name, u.email, u.username, b.name as batch_name 
                          FROM students s 
                          JOIN users u ON s.user_id = u.id 
                          LEFT JOIN batches b ON s.batch_id = b.id 
                          WHERE s.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Update student record
 */
function updateStudent($pdo, $id, $data) {
    try {
        $pdo->beginTransaction();

        $student = getStudentById($pdo, $id);
        
        // Update user info
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
        $stmt->execute([$data['full_name'], $data['email'], $student['user_id']]);

        // Update student info
        $stmt = $pdo->prepare("UPDATE students SET batch_id = ?, status = ?, parent_name = ?, parent_phone = ? WHERE id = ?");
        $stmt->execute([
            $data['batch_id'], 
            $data['status'], 
            $data['parent_name'], 
            $data['parent_phone'], 
            $id
        ]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

/**
 * Delete student (and their user account)
 */
function deleteStudent($pdo, $id) {
    try {
        $student = getStudentById($pdo, $id);
        if (!$student) return false;

        $pdo->beginTransaction();
        
        // User deletion will cascade to student profile if FK is set to CASCADE
        // But we'll do it explicitly just in case
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$student['user_id']]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

/**
 * Generate Invoice with GST
 */
function generateInvoice($pdo, $studentId, $batchId) {
    // Get batch fees
    $stmt = $pdo->prepare("SELECT fees FROM batches WHERE id = ?");
    $stmt->execute([$batchId]);
    $batch = $stmt->fetch(PDO::FETCH_ASSOC);
    $baseAmount = $batch['fees'] ?? 0;

    $gstRate = 18.00; // 18% GST
    $gstAmount = ($baseAmount * $gstRate) / 100;
    $totalAmount = $baseAmount + $gstAmount;
    $invoiceNo = "INV-" . strtoupper(substr(uniqid(), 7));

    $stmt = $pdo->prepare("INSERT INTO invoices (student_id, batch_id, invoice_no, amount, gst_rate, gst_amount, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
    return $stmt->execute([$studentId, $batchId, $invoiceNo, $baseAmount, $gstRate, $gstAmount, $totalAmount]);
}

/**
 * Mock function to send Email/SMS notifications
 */
function sendInvoiceNotification($email, $name, $invoiceNo) {
    // In a real app, you'd use PHPMailer or an API here
    $logMsg = "[" . date('Y-m-d H:i:s') . "] Notification sent to $email ($name) for Invoice $invoiceNo\n";
    file_put_contents(__DIR__ . '/../logs/notifications.log', $logMsg, FILE_APPEND);
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
/**
 * Get count of pending invoices
 */
function getPendingFeesCount($pdo) {
    return $pdo->query("SELECT COUNT(*) FROM invoices WHERE status = 'pending'")->fetchColumn();
}

/**
 * Get count of exams scheduled for this week
 */
function getExamsThisWeekCount($pdo) {
    return $pdo->query("SELECT COUNT(*) FROM tests WHERE YEARWEEK(test_date, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn();
}

/**
 * Check if a student has pending fees
 */
function hasStudentPendingFees($pdo, $student_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE student_id = ? AND status = 'pending'");
    $stmt->execute([$student_id]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Check if a student has tests this week (based on their batch)
 */
function hasStudentTestsThisWeek($pdo, $batch_id) {
    if (!$batch_id) return false;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tests WHERE batch_id = ? AND YEARWEEK(test_date, 1) = YEARWEEK(CURDATE(), 1)");
    $stmt->execute([$batch_id]);
    return $stmt->fetchColumn() > 0;
}
?>
