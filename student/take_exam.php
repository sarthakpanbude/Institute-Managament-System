<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { header("Location: ../index.php"); exit; }

$exam_id = $_GET['id'] ?? null;
if (!$exam_id) { header("Location: exams.php"); exit; }

$exam = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
$exam->execute([$exam_id]);
$exam = $exam->fetch();

$questions = getExamQuestions($pdo, $exam_id);
if (empty($questions)) { die("No questions added to this exam yet."); }

// Check if already attempted
$stmt = $pdo->prepare("SELECT COUNT(*) FROM results WHERE student_id = (SELECT id FROM students WHERE user_id = ?) AND exam_id = ?");
$stmt->execute([$_SESSION['user_id'], $exam_id]);
if ($stmt->fetchColumn() > 0) { die("You have already attempted this exam."); }

$duration_seconds = $exam['duration_minutes'] * 60;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $exam['title']; ?> | Exam Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #0f172a; }
        .exam-header {
            position: sticky; top: 0; background: rgba(30, 41, 59, 0.9);
            backdrop-filter: blur(10px); padding: 20px 40px;
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid var(--glass-border); z-index: 100;
        }
        .timer-box {
            background: rgba(148, 21, 42, 0.1); border: 2px solid var(--primary);
            padding: 10px 25px; border-radius: 50px; font-weight: 700; font-size: 1.2rem;
            display: flex; align-items: center; gap: 10px; color: var(--primary);
        }
        .exam-container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .q-card { margin-bottom: 40px; animation: fadeIn 0.5s ease-out; }
        .option-label {
            display: block; padding: 20px; background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border); border-radius: 12px;
            margin-bottom: 15px; cursor: pointer; transition: 0.3s;
        }
        .option-label:hover { background: rgba(255,255,255,0.06); border-color: var(--primary); }
        .option-label input { display: none; }
        .option-label.selected { background: rgba(148, 21, 42, 0.15); border-color: var(--primary); }
        @keyframes fadeIn { from { opacity:0; transform: translateY(10px); } to { opacity:1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="exam-header">
    <div>
        <h2 style="margin: 0;"><?php echo $exam['title']; ?></h2>
        <span style="color: var(--text-dim); font-size: 0.9rem;">Total Questions: <?php echo count($questions); ?></span>
    </div>
    <div class="timer-box" id="timer">
        <i class="far fa-clock"></i> <span id="time-left">00:00:00</span>
    </div>
</div>

<div class="exam-container">
    <form id="examForm" action="submit_exam.php" method="POST">
        <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
        <?php foreach ($questions as $i => $q): ?>
        <div class="q-card glass" style="padding: 30px;">
            <h3 style="margin-bottom: 25px; line-height: 1.5;">
                <span style="color: var(--primary);">Q<?php echo $i + 1; ?>.</span> <?php echo $q['question_text']; ?>
            </h3>
            <div class="options-group">
                <label class="option-label" onclick="selectOption(this, <?php echo $i; ?>)">
                    <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="A">
                    <strong>A.</strong> <?php echo $q['option_a']; ?>
                </label>
                <label class="option-label" onclick="selectOption(this, <?php echo $i; ?>)">
                    <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="B">
                    <strong>B.</strong> <?php echo $q['option_b']; ?>
                </label>
                <label class="option-label" onclick="selectOption(this, <?php echo $i; ?>)">
                    <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="C">
                    <strong>C.</strong> <?php echo $q['option_c']; ?>
                </label>
                <label class="option-label" onclick="selectOption(this, <?php echo $i; ?>)">
                    <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="D">
                    <strong>D.</strong> <?php echo $q['option_d']; ?>
                </label>
            </div>
        </div>
        <?php endforeach; ?>

        <div style="padding: 40px 0; text-align: center;">
            <button type="submit" class="btn-primary" style="height: 60px; padding: 0 60px; font-size: 1.2rem; border-radius: 50px;">
                <i class="fas fa-check-double"></i> Submit Examination
            </button>
        </div>
    </form>
</div>

<script>
let timeLeft = <?php echo $duration_seconds; ?>;
const timerDisplay = document.getElementById('time-left');
const examForm = document.getElementById('examForm');

const countdown = setInterval(() => {
    if (timeLeft <= 0) {
        clearInterval(countdown);
        alert("Time is up! Submitting exam automatically.");
        examForm.submit();
    } else {
        timeLeft--;
        updateTimerDisplay();
    }
}, 1000);

function updateTimerDisplay() {
    const h = Math.floor(timeLeft / 3600);
    const m = Math.floor((timeLeft % 3600) / 60);
    const s = timeLeft % 60;
    timerDisplay.innerText = `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    
    if (timeLeft < 300) {
        document.getElementById('timer').style.background = 'rgba(239, 68, 68, 0.1)';
        document.getElementById('timer').style.borderColor = '#ef4444';
        timerDisplay.style.color = '#ef4444';
    }
}

function selectOption(el, qIndex) {
    const parent = el.closest('.options-group');
    parent.querySelectorAll('.option-label').forEach(lbl => lbl.classList.remove('selected'));
    el.classList.add('selected');
}

updateTimerDisplay();
</script>

</body>
</html>
