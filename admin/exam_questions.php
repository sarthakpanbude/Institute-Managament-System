<?php
$pageTitle = 'Manage Questions';
require_once 'header.php';
require_once '../includes/functions.php';

$exam_id = $_GET['id'] ?? null;
if (!$exam_id) { header("Location: exams.php"); exit; }

$exam = $pdo->prepare("SELECT e.*, b.name as batch_name FROM exams e LEFT JOIN batches b ON e.batch_id = b.id WHERE e.id = ?");
$exam->execute([$exam_id]);
$exam = $exam->fetch();

$msg = '';
if (isset($_POST['add_question'])) {
    $stmt = $pdo->prepare("INSERT INTO questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option, marks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([
        $exam_id,
        $_POST['question_text'],
        $_POST['option_a'],
        $_POST['option_b'],
        $_POST['option_c'],
        $_POST['option_d'],
        $_POST['correct_option'],
        $_POST['marks']
    ])) {
        $msg = '<div class="badge badge-success" style="padding: 10px; margin-bottom: 20px; width: 100%; text-align: center;">Question added!</div>';
    }
}

$questions = getExamQuestions($pdo, $exam_id);
?>

<div class="card-header">
    <div>
        <h2 style="font-size: 1.8rem;">Questions for: <?php echo $exam['title']; ?></h2>
        <p style="color: var(--text-dim); font-size: 0.9rem;">Adding questions for <?php echo $exam['batch_name'] ?: 'All Batches'; ?>.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="exams.php" class="glass" style="padding: 10px 20px; text-decoration: none; color: var(--text-main); border-radius: 12px; font-weight: 600;">
            <i class="fas fa-arrow-left"></i> Back to Exams
        </a>
        <button class="btn-primary" onclick="openModal('questionModal')">
            <i class="fas fa-plus"></i> Add Question
        </button>
    </div>
</div>

<?php echo $msg; ?>

<div style="margin-top: 30px;">
    <?php foreach ($questions as $index => $q): ?>
    <div class="glass" style="padding: 25px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
            <h4 style="font-size: 1.1rem; line-height: 1.5;">Q<?php echo $index + 1; ?>: <?php echo $q['question_text']; ?></h4>
            <div style="display: flex; gap: 10px;">
                <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><?php echo $q['marks']; ?> Marks</span>
                <button class="action-btn" style="color: #ef4444;"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div style="padding: 12px; border-radius: 10px; border: 1px solid <?php echo $q['correct_option'] == 'A' ? 'var(--primary)' : 'var(--glass-border)'; ?>; background: <?php echo $q['correct_option'] == 'A' ? 'rgba(148, 21, 42, 0.1)' : 'rgba(255,255,255,0.02)'; ?>;">
                <strong>A)</strong> <?php echo $q['option_a']; ?>
            </div>
            <div style="padding: 12px; border-radius: 10px; border: 1px solid <?php echo $q['correct_option'] == 'B' ? 'var(--primary)' : 'var(--glass-border)'; ?>; background: <?php echo $q['correct_option'] == 'B' ? 'rgba(148, 21, 42, 0.1)' : 'rgba(255,255,255,0.02)'; ?>;">
                <strong>B)</strong> <?php echo $q['option_b']; ?>
            </div>
            <div style="padding: 12px; border-radius: 10px; border: 1px solid <?php echo $q['correct_option'] == 'C' ? 'var(--primary)' : 'var(--glass-border)'; ?>; background: <?php echo $q['correct_option'] == 'C' ? 'rgba(148, 21, 42, 0.1)' : 'rgba(255,255,255,0.02)'; ?>;">
                <strong>C)</strong> <?php echo $q['option_c']; ?>
            </div>
            <div style="padding: 12px; border-radius: 10px; border: 1px solid <?php echo $q['correct_option'] == 'D' ? 'var(--primary)' : 'var(--glass-border)'; ?>; background: <?php echo $q['correct_option'] == 'D' ? 'rgba(148, 21, 42, 0.1)' : 'rgba(255,255,255,0.02)'; ?>;">
                <strong>D)</strong> <?php echo $q['option_d']; ?>
            </div>
        </div>
        <div style="margin-top: 15px; font-size: 0.85rem; color: var(--accent); font-weight: 600;">
            <i class="fas fa-check-circle"></i> Correct Option: <?php echo $q['correct_option']; ?>
        </div>
    </div>
    <?php endforeach; if (empty($questions)) echo '<div class="glass" style="padding: 50px; text-align: center; color: var(--text-dim);">No questions added yet.</div>'; ?>
</div>

<!-- Question Modal -->
<div id="questionModal" class="modal-overlay">
    <div class="auth-card glass" style="max-width: 700px; padding: 0;">
        <div class="card-header" style="padding: 20px 30px; border-bottom: 1px solid var(--glass-border); margin-bottom: 0;">
            <h2>Add MCQ Question</h2>
            <button onclick="closeModal('questionModal')" class="close-btn">&times;</button>
        </div>
        <form method="POST" style="padding: 30px;">
            <div class="form-group">
                <label>Question Text *</label>
                <textarea name="question_text" class="form-control" rows="3" required placeholder="Enter the question here..."></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Option A *</label>
                    <input type="text" name="option_a" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Option B *</label>
                    <input type="text" name="option_b" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Option C *</label>
                    <input type="text" name="option_c" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Option D *</label>
                    <input type="text" name="option_d" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Correct Option *</label>
                    <select name="correct_option" class="form-control" required>
                        <option value="A">Option A</option>
                        <option value="B">Option B</option>
                        <option value="C">Option C</option>
                        <option value="D">Option D</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Marks *</label>
                    <input type="number" name="marks" class="form-control" value="4" required>
                </div>
            </div>
            <button type="submit" name="add_question" class="btn-primary" style="width: 100%; justify-content: center; height: 50px;">
                Save Question
            </button>
        </form>
    </div>
</div>

<style>
    .modal-overlay {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
        background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(5px); z-index: 1000; 
        align-items: center; justify-content: center;
    }
    .close-btn { background: none; border: none; color: white; font-size: 2rem; cursor: pointer; opacity: 0.5; transition: 0.3s; }
    .close-btn:hover { opacity: 1; transform: rotate(90deg); }
    .action-btn { background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); width: 34px; height: 34px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
</style>

<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
</script>

</body>
</html>
