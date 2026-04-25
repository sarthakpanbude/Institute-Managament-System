<?php
$pageTitle = 'Fee Management';
require_once 'header.php';
require_once '../includes/functions.php';

// Handle Cash Payment Recording
$msg = '';
if (isset($_POST['record_cash'])) {
    if (recordPayment($pdo, $_POST['invoice_id'], $_POST['student_id'], $_POST['amount'], 'CASH-' . time(), 'cash')) {
        $msg = '<div class="badge badge-success" style="padding: 10px; margin-bottom: 20px; width: 100%; text-align: center;">Cash payment recorded successfully!</div>';
    }
}

$search = $_GET['search'] ?? '';
$invoices = getAllInvoices($pdo, $_GET['status'] ?? '', $search);
$total_pending = getPendingFeesCount($pdo);
?>

<div class="card-header">
    <div>
        <h2 style="font-size: 1.8rem;">Financial Records</h2>
        <p style="color: var(--text-dim); font-size: 0.9rem;">Track invoices, payments, and pending dues.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="?status=pending" class="btn-primary" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2);">
            <i class="fas fa-clock"></i> View Pending (<?php echo $total_pending; ?>)
        </a>
        <button class="btn-primary" onclick="window.print()">
            <i class="fas fa-file-pdf"></i> Export Report
        </button>
    </div>
</div>

<?php echo $msg; ?>

<div class="glass" style="padding: 20px; margin-top: 20px;">
    <form method="GET" style="display: flex; gap: 15px;">
        <input type="hidden" name="status" value="<?php echo $_GET['status'] ?? ''; ?>">
        <div class="form-group" style="flex: 1; margin-bottom: 0;">
            <input type="text" name="search" value="<?php echo $search; ?>" class="form-control" placeholder="Search Student Name or Invoice #...">
        </div>
        <button type="submit" class="btn-primary" style="height: 48px; padding: 0 30px;">
            <i class="fas fa-search"></i> Search
        </button>
    </form>
</div>

<div class="glass" style="padding: 0; overflow: hidden; margin-top: 20px;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background: rgba(255,255,255,0.02); border-bottom: 1px solid var(--glass-border); color: var(--text-dim); font-size: 0.85rem;">
                <th style="padding: 15px 20px;">Invoice #</th>
                <th style="padding: 15px 20px;">Student Info</th>
                <th style="padding: 15px 20px;">Batch</th>
                <th style="padding: 15px 20px;">Amount</th>
                <th style="padding: 15px 20px;">Status</th>
                <th style="padding: 15px 20px; text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoices as $inv): ?>
            <tr style="border-bottom: 1px solid var(--glass-border); transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                <td style="padding: 15px 20px; font-family: monospace; font-weight: bold; color: var(--primary);">
                    <?php echo $inv['invoice_no']; ?>
                </td>
                <td style="padding: 15px 20px;">
                    <div style="font-weight: 600;"><?php echo $inv['student_name']; ?></div>
                    <div style="font-size: 0.75rem; color: var(--text-dim);"><?php echo date('d M, Y', strtotime($inv['created_at'])); ?></div>
                </td>
                <td style="padding: 15px 20px;">
                    <?php echo $inv['batch_name']; ?>
                </td>
                <td style="padding: 15px 20px;">
                    <div style="font-weight: 600;">₹<?php echo number_format($inv['total_amount'], 2); ?></div>
                    <div style="font-size: 0.7rem; color: var(--text-dim);">Incl. GST</div>
                </td>
                <td style="padding: 15px 20px;">
                    <?php 
                        $status_colors = ['pending' => '#f59e0b', 'paid' => '#10b981', 'cancelled' => '#ef4444'];
                        $color = $status_colors[$inv['status']] ?? '#94a3b8';
                    ?>
                    <span class="badge" style="background: <?php echo $color; ?>22; color: <?php echo $color; ?>;">
                        <?php echo ucfirst($inv['status']); ?>
                    </span>
                </td>
                <td style="padding: 15px 20px; text-align: right;">
                    <div style="display: flex; justify-content: flex-end; gap: 8px;">
                        <?php if ($inv['status'] === 'pending'): ?>
                        <button onclick='openPaymentModal(<?php echo json_encode($inv); ?>)' class="action-btn" style="color: #10b981;" title="Record Payment">
                            <i class="fas fa-hand-holding-usd"></i>
                        </button>
                        <?php endif; ?>
                        <a href="view_invoice.php?id=<?php echo $inv['id']; ?>" class="action-btn" style="color: var(--primary);" title="Download PDF">
                            <i class="fas fa-file-invoice"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($invoices)): ?>
            <tr><td colspan="6" style="padding: 40px; text-align: center; color: var(--text-dim);">No financial records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Record Payment Modal -->
<div id="paymentModal" class="modal-overlay">
    <div class="auth-card glass" style="max-width: 450px; padding: 0;">
        <div class="card-header" style="padding: 20px 30px; border-bottom: 1px solid var(--glass-border); margin-bottom: 0;">
            <h2>Record Payment</h2>
            <button onclick="closeModal('paymentModal')" class="close-btn">&times;</button>
        </div>
        <form method="POST" style="padding: 30px;">
            <input type="hidden" name="invoice_id" id="modal_inv_id">
            <input type="hidden" name="student_id" id="modal_std_id">
            <input type="hidden" name="amount" id="modal_amount_val">
            
            <div style="background: rgba(255,255,255,0.02); border-radius: 12px; padding: 15px; margin-bottom: 20px; border: 1px solid var(--glass-border);">
                <p style="font-size: 0.8rem; color: var(--text-dim); margin-bottom: 5px;">Paying For:</p>
                <div style="font-weight: 600;" id="modal_inv_no"></div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent); margin-top: 10px;" id="modal_amount_text"></div>
            </div>

            <div class="form-group">
                <label>Payment Method</label>
                <select name="method" class="form-control" disabled>
                    <option value="cash">Cash Payment</option>
                </select>
                <small style="color: var(--text-dim);">Online payments are handled via Student Portal.</small>
            </div>

            <button type="submit" name="record_cash" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 20px; height: 50px;">
                Confirm & Receipt
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

function openPaymentModal(inv) {
    document.getElementById('modal_inv_id').value = inv.id;
    document.getElementById('modal_std_id').value = inv.student_id;
    document.getElementById('modal_amount_val').value = inv.total_amount;
    document.getElementById('modal_inv_no').innerText = inv.invoice_no + ' (' + inv.student_name + ')';
    document.getElementById('modal_amount_text').innerText = '₹' + parseFloat(inv.total_amount).toLocaleString('en-IN');
    openModal('paymentModal');
}
</script>

</body>
</html>
