<?php
require_once '../includes/db.php';
require_once '../config.php';

if (!isset($_GET['id'])) {
    die("Invoice ID is required.");
}

$invoice_id = $_GET['id'];

// Fetch invoice details with student and batch info
$stmt = $pdo->prepare("SELECT i.*, u.full_name, u.email, b.name as batch_name 
                      FROM invoices i
                      JOIN students s ON i.student_id = s.id
                      JOIN users u ON s.user_id = u.id
                      JOIN batches b ON i.batch_id = b.id
                      WHERE i.id = ?");
$stmt->execute([$invoice_id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    die("Invoice not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo $invoice['invoice_no']; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8fafc; color: #1e293b; padding: 40px; }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 40px;
            border: 1px solid #e2e8f0;
            background: #fff;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }
        .invoice-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px; margin-bottom: 30px; }
        .invoice-details { display: flex; justify-content: space-between; margin-bottom: 40px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table th { background: #f8fafc; padding: 12px; text-align: left; border-bottom: 2px solid #e2e8f0; }
        table td { padding: 12px; border-bottom: 1px solid #f1f5f9; }
        .totals { float: right; width: 300px; }
        .totals div { display: flex; justify-content: space-between; padding: 8px 0; }
        .grand-total { border-top: 2px solid #1e293b; margin-top: 10px; padding-top: 10px; font-weight: bold; font-size: 1.2rem; color: #6366f1; }
        @media print {
            body { background: none; padding: 0; }
            .invoice-box { box-shadow: none; border: none; max-width: 100%; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

<div class="invoice-box">
    <div class="print-btn" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" class="btn-primary" style="padding: 10px 20px;">
            <i class="fas fa-print"></i> Print Invoice
        </button>
    </div>

    <div class="invoice-header">
        <div>
            <h1 style="color: #6366f1; margin-bottom: 5px;">INVOICE</h1>
            <p style="color: #64748b;">#<?php echo $invoice['invoice_no']; ?></p>
        </div>
        <div style="text-align: right;">
            <h2 style="margin-bottom: 5px;">DNA- Da NEET Academy</h2>
            <p style="color: #64748b; font-size: 0.9rem;">1st floor, Maruti Plaza, B wing,<br>Vidyavikas Circle, Gangapur Rd, Nashik, MH 422005</p>
            <p style="color: #64748b; font-size: 0.9rem;"><i class="fas fa-phone-alt"></i> 070204 61661</p>
        </div>
    </div>

    <div class="invoice-details">
        <div>
            <h4 style="color: #64748b; text-transform: uppercase; margin-bottom: 10px; font-size: 0.8rem;">Billed To:</h4>
            <p><strong><?php echo $invoice['full_name']; ?></strong></p>
            <p style="color: #64748b;"><?php echo $invoice['email']; ?></p>
            <p style="color: #64748b;">Course: <?php echo $invoice['batch_name']; ?></p>
        </div>
        <div style="text-align: right;">
            <h4 style="color: #64748b; text-transform: uppercase; margin-bottom: 10px; font-size: 0.8rem;">Invoice Date:</h4>
            <p><?php echo date('d M, Y', strtotime($invoice['created_at'])); ?></p>
            <h4 style="color: #64748b; text-transform: uppercase; margin-top: 15px; margin-bottom: 5px; font-size: 0.8rem;">Status:</h4>
            <span style="background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;"><?php echo strtoupper($invoice['status']); ?></span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Course Enrollment Fee (<?php echo $invoice['batch_name']; ?>)</td>
                <td style="text-align: right;">₹<?php echo number_format($invoice['amount'], 2); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="totals">
        <div>
            <span>Subtotal</span>
            <span>₹<?php echo number_format($invoice['amount'], 2); ?></span>
        </div>
        <div>
            <span>GST (<?php echo $invoice['gst_rate']; ?>%)</span>
            <span>₹<?php echo number_format($invoice['gst_amount'], 2); ?></span>
        </div>
        <div class="grand-total">
            <span>Total Amount</span>
            <span>₹<?php echo number_format($invoice['total_amount'], 2); ?></span>
        </div>
    </div>

    <div style="margin-top: 100px; padding-top: 20px; border-top: 1px solid #f1f5f9; color: #94a3b8; font-size: 0.8rem; text-align: center;">
        <p>This is a computer-generated invoice. No signature required.</p>
        <p>Thank you for choosing DNA Academy!</p>
    </div>
</div>

</body>
</html>
