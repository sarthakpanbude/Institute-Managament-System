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
        logActivity("Student Registered", "Name: {$data['full_name']}, Email: {$data['email']}");
        sendInvoiceNotification($data['email'], $data['full_name'], "INV-" . time());
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        logActivity("Student Registration Failed", $e->getMessage());
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
        logActivity("Student Updated", "ID: $id, Name: {$data['full_name']}");
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        logActivity("Student Update Failed", $e->getMessage());
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
        logActivity("Student Deleted", "ID: $id, Name: {$student['full_name']}");
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        logActivity("Student Deletion Failed", $e->getMessage());
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
 * Get total revenue from paid payments
 */
function getTotalRevenue($pdo) {
    return $pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'completed' OR payment_gateway = 'cash'")->fetchColumn() ?: 0;
}

/**
 * Get average score across all results
 */
function getAverageScore($pdo) {
    return $pdo->query("SELECT AVG(percentage) FROM results")->fetchColumn() ?: 0;
}

/**
 * Get upcoming exams
 */
function getUpcomingExams($pdo, $limit = 5) {
    $stmt = $pdo->prepare("SELECT e.*, b.name as batch_name FROM exams e LEFT JOIN batches b ON e.batch_id = b.id WHERE e.exam_date >= CURDATE() ORDER BY e.exam_date ASC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get monthly revenue data for Chart.js
 */
function getMonthlyRevenueData($pdo) {
    $sql = "SELECT DATE_FORMAT(payment_date, '%b') as month, SUM(amount) as total 
            FROM payments 
            WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY month 
            ORDER BY payment_date ASC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get monthly performance data for Chart.js
 */
function getMonthlyPerformanceData($pdo) {
    $sql = "SELECT DATE_FORMAT(created_at, '%b') as month, AVG(percentage) as avg_score 
            FROM results 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY month 
            ORDER BY created_at ASC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all exams
 */
function getAllExams($pdo) {
    $stmt = $pdo->query("SELECT e.*, b.name as batch_name FROM exams e LEFT JOIN batches b ON e.batch_id = b.id ORDER BY e.exam_date DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Create a new exam
 */
function createExam($pdo, $data) {
    $stmt = $pdo->prepare("INSERT INTO exams (title, description, exam_date, duration_minutes, total_marks, passing_marks, batch_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([
        $data['title'],
        $data['description'],
        $data['exam_date'],
        $data['duration_minutes'],
        $data['total_marks'],
        $data['passing_marks'],
        $data['batch_id']
    ]);
    if ($result) {
        logActivity("Exam Scheduled", "Title: {$data['title']}, Date: {$data['exam_date']}");
    }
    return $result;
}

/**
 * Get all invoices with student info and search
 */
function getAllInvoices($pdo, $status = '', $search = '') {
    $params = [];
    $where = " WHERE 1=1 ";
    
    if ($status) {
        $where .= " AND i.status = ? ";
        $params[] = $status;
    }
    
    if ($search) {
        $where .= " AND (u.full_name LIKE ? OR i.invoice_no LIKE ?) ";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $sql = "SELECT i.*, u.full_name as student_name, b.name as batch_name 
            FROM invoices i
            JOIN students s ON i.student_id = s.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN batches b ON i.batch_id = b.id
            $where
            ORDER BY i.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Record a payment (Gateway/Cash)
 */
function recordPayment($pdo, $invoice_id, $student_id, $amount, $tx_id, $method = 'razorpay') {
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO payments (invoice_id, student_id, amount, transaction_id, payment_gateway, status) VALUES (?, ?, ?, ?, ?, 'completed')");
        $stmt->execute([$invoice_id, $student_id, $amount, $tx_id, $method]);

        $stmt = $pdo->prepare("UPDATE invoices SET status = 'paid' WHERE id = ?");
        $stmt->execute([$invoice_id]);

        $pdo->commit();
        logActivity("Payment Recorded", "Invoice ID: $invoice_id, Amount: ₹$amount, Method: $method");
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        logActivity("Payment Recording Failed", $e->getMessage());
        return false;
    }
}

/**
 * Get count of pending invoices
 */
function getPendingFeesCount($pdo) {
    return $pdo->query("SELECT COUNT(*) FROM invoices WHERE status = 'pending'")->fetchColumn() ?: 0;
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
 * Get count of exams scheduled for this week
 */
function getExamsThisWeekCount($pdo) {
    return $pdo->query("SELECT COUNT(*) FROM exams WHERE YEARWEEK(exam_date, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn() ?: 0;
}

/**
 * Check if a student has exams this week (based on their batch)
 */
function hasStudentExamsThisWeek($pdo, $batch_id) {
    if (!$batch_id) return false;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM exams WHERE batch_id = ? AND YEARWEEK(exam_date, 1) = YEARWEEK(CURDATE(), 1)");
    $stmt->execute([$batch_id]);
    return $stmt->fetchColumn() > 0;
}
/**
 * Get students by batch ID
 */
function getStudentsByBatch($pdo, $batch_id) {
    if (!$batch_id) return [];
    $stmt = $pdo->prepare("SELECT s.*, u.full_name, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.batch_id = ? AND s.status = 'active' ORDER BY u.full_name ASC");
    $stmt->execute([$batch_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Save Attendance for multiple students
 */
function saveAttendance($pdo, $batch_id, $date, $records) {
    try {
        $pdo->beginTransaction();
        
        // Remove existing for same day to prevent duplicates (though typically you'd update)
        $stmt = $pdo->prepare("DELETE FROM attendance WHERE batch_id = ? AND attendance_date = ?");
        $stmt->execute([$batch_id, $date]);

        $stmt = $pdo->prepare("INSERT INTO attendance (student_id, batch_id, attendance_date, status) VALUES (?, ?, ?, ?)");
        
        foreach ($records as $student_id => $status) {
            $stmt->execute([$student_id, $batch_id, $date, $status]);
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}
/**
 * Get all notifications
 */
function getAllNotifications($pdo) {
    $stmt = $pdo->query("SELECT n.*, u.full_name as user_name FROM notifications n LEFT JOIN users u ON n.user_id = u.id ORDER BY n.created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Send Notification (Internal + External API Simulation)
 */
function sendNotification($pdo, $data) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
        
        // If user_id is 0, it means 'All Students'
        if ($data['target'] == 'all_students') {
            $students = $pdo->query("SELECT user_id FROM students")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($students as $uid) {
                $stmt->execute([$uid, $data['title'], $data['message'], $data['type']]);
            }
        } elseif ($data['target'] == 'all_parents') {
            $parents = $pdo->query("SELECT user_id FROM parents")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($parents as $uid) {
                $stmt->execute([$uid, $data['title'], $data['message'], $data['type']]);
            }
        } else {
            $stmt->execute([$data['user_id'], $data['title'], $data['message'], $data['type']]);
        }

        // Simulate External API Integrations
        $logPath = __DIR__ . '/../logs/api_integrations.log';
        if (!empty($data['channels'])) {
            foreach ($data['channels'] as $channel) {
                $logMsg = "[" . date('Y-m-d H:i:s') . "] [$channel API] Sending: " . $data['title'] . "\n";
                file_put_contents($logPath, $logMsg, FILE_APPEND);
            }
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}
/**
 * Get all notes/study materials
 */
function getAllNotes($pdo) {
    $stmt = $pdo->query("SELECT n.*, b.name as batch_name FROM notes n LEFT JOIN batches b ON n.batch_id = b.id ORDER BY n.created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Record a new note/file upload in database
 */
function uploadNote($pdo, $batch_id, $title, $file_path, $type = 'pdf') {
    $stmt = $pdo->prepare("INSERT INTO notes (batch_id, title, file_path, file_type) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$batch_id, $title, $file_path, $type]);
}
/**
 * Get student record by user ID
 */
function getStudentByUserId($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT s.*, u.full_name, u.email, b.name as batch_name 
                          FROM students s 
                          JOIN users u ON s.user_id = u.id 
                          LEFT JOIN batches b ON s.batch_id = b.id 
                          WHERE s.user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get student dashboard stats
 */
function getStudentStats($pdo, $student_id) {
    $stats = [];
    
    // Average Score
    $stmt = $pdo->prepare("SELECT AVG(percentage) FROM results WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $stats['avg_score'] = round($stmt->fetchColumn() ?: 0, 1);

    // Tests Attempted
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM results WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $stats['tests_done'] = $stmt->fetchColumn();

    // Pending Fees
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE student_id = ? AND status = 'pending'");
    $stmt->execute([$student_id]);
    $stats['pending_fees'] = $stmt->fetchColumn();

    return $stats;
}

/**
 * Get monthly performance for a specific student
 */
function getStudentPerformanceData($pdo, $student_id) {
    $stmt = $pdo->prepare("SELECT DATE_FORMAT(created_at, '%b') as month, percentage 
                          FROM results 
                          WHERE student_id = ? 
                          ORDER BY created_at ASC");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/**
 * Get all invoices for a specific student
 */
function getStudentInvoices($pdo, $student_id) {
    $stmt = $pdo->prepare("SELECT i.*, b.name as batch_name 
                          FROM invoices i 
                          LEFT JOIN batches b ON i.batch_id = b.id 
                          WHERE i.student_id = ? 
                          ORDER BY i.created_at DESC");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/**
 * Get available exams for a student's batch
 */
function getAvailableExams($pdo, $batch_id) {
    $stmt = $pdo->prepare("SELECT e.*, b.name as batch_name 
                          FROM exams e 
                          LEFT JOIN batches b ON e.batch_id = b.id 
                          WHERE (e.batch_id IS NULL OR e.batch_id = ?) 
                          AND e.status != 'completed' 
                          ORDER BY e.exam_date ASC");
    $stmt->execute([$batch_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get questions for an exam
 */
function getExamQuestions($pdo, $exam_id) {
    if (!$exam_id) return [];
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE exam_id = ? ORDER BY id ASC");
    $stmt->execute([$exam_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/**
 * Get students linked to a parent user
 */
function getParentStudents($pdo, $parent_user_id) {
    $stmt = $pdo->prepare("SELECT s.*, u.full_name, u.email, b.name as batch_name 
                          FROM students s 
                          JOIN users u ON s.user_id = u.id 
                          JOIN parents p ON s.parent_id = p.id 
                          LEFT JOIN batches b ON s.batch_id = b.id 
                          WHERE p.user_id = ?");
    $stmt->execute([$parent_user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/**
 * Log System Activity
 */
function logActivity($action, $details = '') {
    $user = $_SESSION['full_name'] ?? 'System';
    $logMsg = "[" . date('Y-m-d H:i:s') . "] [$user] $action: $details\n";
    file_put_contents(__DIR__ . '/../logs/activity.log', $logMsg, FILE_APPEND);
}
?>
