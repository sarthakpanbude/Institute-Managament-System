<?php
$pageTitle = 'Fee Payment';
require_once 'header.php';
require_once '../includes/functions.php';

$student = getStudentByUserId($pdo, $_SESSION['user_id']);
$invoices = getStudentInvoices($pdo, $student['id']);

// Handle Mock Payment Success
if (isset($_POST['mock_pay'])) {
    if (recordPayment($pdo, $_POST['invoice_id'], $student['id'], $_POST['amount'], 'PAY-' . time(), 'razorpay')) {
        header("Location: fees.php?success=1");
        exit;
    }
}
?>

<div class="card-header">
    <div>
        <h2 style="font-size: 1.8rem;">Fees & Payments</h2>
        <p style="color: var(--text-dim); font-size: 0.9rem;">Manage your course fees and download receipts.</p>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="badge badge-success" style="width: 100%; padding: 15px; margin-top: 20px; font-size: 1rem; justify-content: center;">
        <i class="fas fa-check-circle"></i> Payment Successful! Your receipt has been generated.
    </div>
<?php endif; ?>

<div class="glass" style="padding: 0; overflow: hidden; margin-top: 30px;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background: rgba(255,255,255,0.02); border-bottom: 1px solid var(--glass-border); color: var(--text-dim); font-size: 0.85rem;">
                <th style="padding: 15px 20px;">Invoice Date</th>
                <th style="padding: 15px 20px;">Invoice #</th>
                <th style="padding: 15px 20px;">Description</th>
                <th style="padding: 15px 20px;">Amount</th>
                <th style="padding: 15px 20px;">Status</th>
                <th style="padding: 15px 20px; text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoices as $inv): ?>
            <tr style="border-bottom: 1px solid var(--glass-border);">
                <td style="padding: 15px 20px;"><?php echo date('d M, Y', strtotime($inv['created_at'])); ?></td>
                <td style="padding: 15px 20px; font-family: monospace; font-weight: bold; color: var(--primary);"><?php echo $inv['invoice_no']; ?></td>
                <td style="padding: 15px 20px;"><?php echo $inv['batch_name']; ?> - Course Fee</td>
                <td style="padding: 15px 20px;">
                    <div style="font-weight: 700;">₹<?php echo number_format($inv['total_amount'], 2); ?></div>
                    <div style="font-size: 0.75rem; color: var(--text-dim);">Incl. 18% GST</div>
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
                    <?php if ($inv['status'] == 'pending'): ?>
                        <button class="btn-primary" style="padding: 8px 15px; font-size: 0.85rem;" onclick='initiatePayment(<?php echo json_encode($inv); ?>)'>
                            <i class="fas fa-credit-card"></i> Pay Now
                        </button>
                    <?php else: ?>
                        <a href="receipt.php?id=<?php echo $inv['id']; ?>" class="glass" style="padding: 8px 15px; font-size: 0.85rem; text-decoration: none; border: 1px solid var(--glass-border); border-radius: 10px; color: var(--text-main);">
                            <i class="fas fa-file-pdf"></i> Receipt
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; if (empty($invoices)) echo '<tr><td colspan="6" style="padding: 40px; text-align: center; color: var(--text-dim);">No invoices found.</td></tr>'; ?>
        </tbody>
    </table>
</div>

<!-- Modal for Razorpay Simulation -->
<div id="paymentModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
    <div class="auth-card glass" style="max-width: 400px; text-align: center; padding: 40px;">
        <img src="https://razorpay.com/favicon.png" alt="Razorpay" style="width: 50px; margin-bottom: 20px;">
        <h2 style="margin-bottom: 10px;">Razorpay Checkout</h2>
        <p style="color: var(--text-dim); margin-bottom: 30px;">Secure payment for <span id="modal_inv_no"></span></p>
        
        <div style="font-size: 2rem; font-weight: 800; margin-bottom: 30px;" id="modal_amount"></div>
        
        <form method="POST">
            <input type="hidden" name="invoice_id" id="form_inv_id">
            <input type="hidden" name="amount" id="form_amount">
            <button type="submit" name="mock_pay" class="btn-primary" style="width: 100%; justify-content: center; height: 55px; font-size: 1.1rem; background: #3399cc;">
                Complete Payment
            </button>
        </form>
        <button onclick="closeModal()" style="background: none; border: none; color: var(--text-dim); margin-top: 20px; cursor: pointer;">Cancel</button>
    </div>
</div>

<script>
function initiatePayment(inv) {
    document.getElementById('modal_inv_no').innerText = inv.invoice_no;
    document.getElementById('modal_amount').innerText = '₹' + parseFloat(inv.total_amount).toLocaleString('en-IN');
    document.getElementById('form_inv_id').value = inv.id;
    document.getElementById('form_amount').value = inv.total_amount;
    document.getElementById('paymentModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('paymentModal').style.display = 'none';
}
</script>

</body>
</html>
