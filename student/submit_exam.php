<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_id = $_POST['exam_id'];
    $answers = $_POST['answers'] ?? [];
    
    $student = getStudentByUserId($pdo, $_SESSION['user_id']);
    $student_id = $student['id'];

    // Get all correct answers for this exam
    $stmt = $pdo->prepare("SELECT id, correct_option, marks FROM questions WHERE exam_id = ?");
    $stmt->execute([$exam_id]);
    $questions = $stmt->fetchAll();

    $marks_obtained = 0;
    $total_max_marks = 0;

    foreach ($questions as $q) {
        $total_max_marks += $q['marks'];
        if (isset($answers[$q['id']]) && $answers[$q['id']] === $q['correct_option']) {
            $marks_obtained += $q['marks'];
        }
    }

    $percentage = ($marks_obtained / $total_max_marks) * 100;
    $status = ($percentage >= 40) ? 'pass' : 'fail';
    
    // Grading logic
    if ($percentage >= 90) $grade = 'A+';
    elseif ($percentage >= 80) $grade = 'A';
    elseif ($percentage >= 70) $grade = 'B';
    elseif ($percentage >= 60) $grade = 'C';
    else $grade = 'D';

    // Store results
    $stmt = $pdo->prepare("INSERT INTO results (exam_id, student_id, marks_obtained, total_marks, percentage, grade, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $exam_id,
        $student_id,
        $marks_obtained,
        $total_max_marks,
        $percentage,
        $grade,
        $status
    ]);

    // Send notification
    $data = [
        'user_id' => $_SESSION['user_id'],
        'title' => "Result Out: " . substr(time(), 0, 5),
        'message' => "You scored " . $marks_obtained . "/" . $total_max_marks . " (" . round($percentage, 2) . "%). Grade: " . $grade,
        'type' => 'exam'
    ];
    sendNotification($pdo, $data);

    header("Location: result_view.php?id=" . $pdo->lastInsertId());
} else {
    header("Location: dashboard.php");
}
exit;
