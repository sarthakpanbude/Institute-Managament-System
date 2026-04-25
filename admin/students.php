<?php
$pageTitle = 'Manage Students';
require_once 'header.php';
require_once '../includes/functions.php';

// Handle Student Actions (Same as before)
$msg = '';
if (isset($_POST['add_student'])) {
    try {
        if (registerStudent($pdo, $_POST)) {
            $msg = '<div class="badge badge-success" style="padding: 10px; margin-bottom: 20px; width: 100%; text-align: center;">Student registered successfully!</div>';
        } else {
            $msg = '<div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;">Error while saving student.</div>';
        }
    } catch (Exception $e) {
        $msg = '<div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;">Error: ' . $e->getMessage() . '</div>';
    }
}

if (isset($_POST['update_student'])) {
    if (updateStudent($pdo, $_POST['id'], $_POST)) {
        $msg = '<div class="badge badge-success" style="padding: 10px; margin-bottom: 20px; width: 100%; text-align: center;">Student details updated!</div>';
    } else {
        $msg = '<div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;">Error while updating student.</div>';
    }
}

if (isset($_GET['delete_id'])) {
    if (deleteStudent($pdo, $_GET['delete_id'])) {
        $msg = '<div class="badge badge-success" style="padding: 10px; margin-bottom: 20px; width: 100%; text-align: center; background: rgba(239, 68, 68, 0.1); color: #ef4444;">Student record deleted.</div>';
    }
}

// Pagination & Filtering
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$batch_filter = $_GET['batch_filter'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';
$session_filter = $_GET['session_filter'] ?? '';

$students = getAllStudents($pdo, $search, $batch_filter, $status_filter, $session_filter, $limit, $offset);
$total_students_count = getTotalStudentsCount($pdo, $search, $batch_filter, $status_filter, $session_filter);
$total_pages = ceil($total_students_count / $limit);
$batches = getAllBatches($pdo);

// Integration Stats
$pending_fees = getPendingFeesCount($pdo);
$exams_this_week = getExamsThisWeekCount($pdo);
?>

<!-- Integration Stats Cards -->
<div class="stat-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 30px;">
    <div class="stat-card glass">
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $total_students_count; ?></h3>
            <p>Total Students</p>
        </div>
    </div>
    <div class="stat-card glass">
        <div class="stat-icon" style="background: rgba(236, 72, 153, 0.1); color: var(--secondary);">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo count($batches); ?></h3>
            <p>Active Batches</p>
        </div>
    </div>
    <div class="stat-card glass">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $pending_fees; ?></h3>
            <p>Fees Pending</p>
        </div>
    </div>
    <div class="stat-card glass">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--accent);">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $exams_this_week; ?></h3>
            <p>Exams This Week</p>
        </div>
    </div>
</div>

<div class="card-header">
    <div>
        <h2 style="font-size: 1.8rem;">Student Directory</h2>
        <p style="color: var(--text-dim); font-size: 0.9rem;">Connected to Fees, Exams and Materials modules.</p>
    </div>
    <button class="btn-primary" onclick="openModal('studentModal')">
        <i class="fas fa-plus"></i> Add New Student
    </button>
</div>

<?php echo $msg; ?>

<!-- Filters (Same as before) -->
<div class="glass" style="padding: 20px; margin-bottom: 20px;">
    <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-group" style="flex: 2; min-width: 200px; margin-bottom: 0;">
            <label style="font-size: 0.8rem;">Search Name, Email or ID</label>
            <input type="text" name="search" value="<?php echo $search; ?>" class="form-control" placeholder="Search...">
        </div>
        <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
            <label style="font-size: 0.8rem;">Filter Session</label>
            <select name="session_filter" class="form-control">
                <option value="">All Sessions</option>
                <option value="Morning" <?php echo $session_filter == 'Morning' ? 'selected' : ''; ?>>Morning</option>
                <option value="Afternoon" <?php echo $session_filter == 'Afternoon' ? 'selected' : ''; ?>>Afternoon</option>
                <option value="Evening" <?php echo $session_filter == 'Evening' ? 'selected' : ''; ?>>Evening</option>
            </select>
        </div>
        <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
            <label style="font-size: 0.8rem;">Status</label>
            <select name="status_filter" class="form-control">
                <option value="">All Status</option>
                <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="passed" <?php echo $status_filter == 'passed' ? 'selected' : ''; ?>>Passed Out</option>
                <option value="dropped" <?php echo $status_filter == 'dropped' ? 'selected' : ''; ?>>Dropped</option>
            </select>
        </div>
        <button type="submit" class="btn-primary" style="height: 45px; padding: 0 20px;">
            <i class="fas fa-filter"></i> Apply
        </button>
    </form>
</div>

<div class="glass" style="padding: 0; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background: rgba(255,255,255,0.02); border-bottom: 1px solid var(--glass-border); color: var(--text-dim); font-size: 0.85rem;">
                <th style="padding: 15px 20px;">Admission ID</th>
                <th style="padding: 15px 20px;">Student Info</th>
                <th style="padding: 15px 20px;">Batch & Module Status</th>
                <th style="padding: 15px 20px;">Status</th>
                <th style="padding: 15px 20px; text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $s): ?>
            <tr style="border-bottom: 1px solid var(--glass-border); transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                <td style="padding: 15px 20px; font-family: monospace; font-weight: bold; color: var(--primary);"><?php echo $s['admission_id']; ?></td>
                <td style="padding: 15px 20px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="avatar" style="width: 36px; height: 36px; background: linear-gradient(45deg, var(--primary), var(--secondary)); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold; color: white;">
                            <?php echo strtoupper(substr($s['full_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <div style="font-weight: 600;"><?php echo $s['full_name']; ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-dim);"><?php echo $s['email']; ?></div>
                        </div>
                    </div>
                </td>
                <td style="padding: 15px 20px;">
                    <div style="display: flex; flex-direction: column; gap: 5px;">
                        <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: var(--primary); width: fit-content;">
                            <i class="fas fa-layer-group"></i> <?php echo $s['batch_name'] ?: 'N/A'; ?> 
                            <?php if($s['batch_session']): ?>
                                <small style="opacity: 0.7;">(<?php echo $s['batch_session']; ?>)</small>
                            <?php endif; ?>
                        </span>
                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <?php if (hasStudentPendingFees($pdo, $s['id'])): ?>
                                <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; font-size: 0.7rem;" title="Invoice Pending">
                                    <i class="fas fa-exclamation-triangle"></i> Fee Pending
                                </span>
                            <?php endif; ?>
                            <?php if (hasStudentExamsThisWeek($pdo, $s['batch_id'])): ?>
                                <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.7rem;" title="Exams Scheduled">
                                    <i class="fas fa-clock"></i> Exam this week
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td style="padding: 15px 20px;">
                    <?php 
                        $status_colors = ['active' => '#10b981', 'passed' => '#6366f1', 'dropped' => '#ef4444'];
                        $color = $status_colors[$s['status']] ?? '#94a3b8';
                    ?>
                    <span style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 600; color: <?php echo $color; ?>;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: <?php echo $color; ?>;"></span>
                        <?php echo ucfirst($s['status']); ?>
                    </span>
                </td>
                <td style="padding: 15px 20px; text-align: right;">
                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <!-- Integration Quick Links -->
                        <div style="display: flex; gap: 5px; padding-right: 15px; border-right: 1px solid var(--glass-border); margin-right: 15px;">
                            <a href="fees.php?student_id=<?php echo $s['id']; ?>" class="action-btn" style="color: #f59e0b;" title="Fee Management"><i class="fas fa-wallet"></i></a>
                            <a href="tests.php?student_id=<?php echo $s['id']; ?>" class="action-btn" style="color: var(--accent);" title="Results"><i class="fas fa-poll"></i></a>
                            <a href="materials.php?student_id=<?php echo $s['id']; ?>" class="action-btn" style="color: var(--secondary);" title="Study Materials"><i class="fas fa-book-open"></i></a>
                        </div>
                        
                        <button onclick='editStudent(<?php echo json_encode($s); ?>)' class="action-btn" style="color: var(--primary);" title="Edit Profile">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="students.php?delete_id=<?php echo $s['id']; ?>" onclick="return confirm('Are you sure you want to delete this student?')" class="action-btn" style="color: #ef4444;" title="Delete">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination (Same as before) -->
<?php if ($total_pages > 1): ?>
<div style="display: flex; justify-content: center; gap: 8px; margin-top: 30px;">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&status_filter=<?php echo $status_filter; ?>&session_filter=<?php echo $session_filter; ?>" 
            class="<?php echo $page == $i ? 'btn-primary' : 'glass'; ?>" 
            style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; text-decoration: none; border-radius: 10px; font-weight: bold;">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<!-- Modals (Same as before) -->
<div id="studentModal" class="modal-overlay">
    <div class="auth-card glass" style="max-width: 600px; padding: 0;">
        <div class="card-header" style="padding: 20px 30px; border-bottom: 1px solid var(--glass-border); margin-bottom: 0;">
            <h2>Enroll New Student</h2>
            <button onclick="closeModal('studentModal')" class="close-btn">&times;</button>
        </div>
        <form method="POST" style="padding: 30px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" class="form-control" placeholder="John Doe" required>
                </div>
                <div class="form-group">
                    <label>Email ID *</label>
                    <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                </div>
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" class="form-control" placeholder="johndoe123" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label>Parent Name</label>
                    <input type="text" name="parent_name" class="form-control" placeholder="Parent Name">
                </div>
                <div class="form-group">
                    <label>Parent Contact</label>
                    <input type="tel" name="parent_phone" class="form-control" placeholder="10 Digit Number" pattern="[0-9]{10}">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Assign Batch *</label>
                    <select name="batch_id" class="form-control" required>
                        <option value="">Select Batch</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?php echo $b['id']; ?>"><?php echo $b['name']; ?> (₹<?php echo number_format($b['fees']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" name="add_student" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 20px; height: 50px;">
                Complete Enrollment
            </button>
        </form>
    </div>
</div>

<div id="editModal" class="modal-overlay">
    <div class="auth-card glass" style="max-width: 600px; padding: 0;">
        <div class="card-header" style="padding: 20px 30px; border-bottom: 1px solid var(--glass-border); margin-bottom: 0;">
            <h2>Edit Student Details</h2>
            <button onclick="closeModal('editModal')" class="close-btn">&times;</button>
        </div>
        <form method="POST" style="padding: 30px;">
            <input type="hidden" name="id" id="edit_id">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email ID</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Parent Name</label>
                    <input type="text" name="parent_name" id="edit_parent_name" class="form-control">
                </div>
                <div class="form-group">
                    <label>Parent Contact</label>
                    <input type="tel" name="parent_phone" id="edit_parent_phone" class="form-control">
                </div>
                <div class="form-group">
                    <label>Batch</label>
                    <select name="batch_id" id="edit_batch" class="form-control" required>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?php echo $b['id']; ?>"><?php echo $b['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_status" class="form-control" required>
                        <option value="active">Active</option>
                        <option value="passed">Passed Out</option>
                        <option value="dropped">Dropped</option>
                    </select>
                </div>
            </div>
            <button type="submit" name="update_student" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 20px; height: 50px;">
                Save Changes
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
    .action-btn { background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); width: 34px; height: 34px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; transition: 0.3s; cursor: pointer; text-decoration: none; }
    .action-btn:hover { background: rgba(255,255,255,0.1); transform: translateY(-2px); }
</style>

<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function editStudent(student) {
    document.getElementById('edit_id').value = student.id;
    document.getElementById('edit_name').value = student.full_name;
    document.getElementById('edit_email').value = student.email;
    document.getElementById('edit_parent_name').value = student.parent_name;
    document.getElementById('edit_parent_phone').value = student.parent_phone;
    document.getElementById('edit_batch').value = student.batch_id;
    document.getElementById('edit_status').value = student.status;
    openModal('editModal');
}

document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
document.querySelector('a[href="students.php"]').classList.add('active');
</script>

</body>
</html>
