<?php
/**
 * Automated Fee Reminder Cron Script
 * Run daily via crontab: 0 0 * * * php /path/to/cron/fee_reminders.php
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Fetch all pending invoices
$stmt = $pdo->prepare("SELECT i.*, u.full_name, u.email, u.id as user_id 
                      FROM invoices i 
                      JOIN students s ON i.student_id = s.id 
                      JOIN users u ON s.user_id = u.id 
                      WHERE i.status = 'pending'");
$stmt->execute();
$invoices = $stmt->fetchAll();

$count = 0;
foreach ($invoices as $inv) {
    $data = [
        'user_id' => $inv['user_id'],
        'title' => "Fee Payment Reminder",
        'message' => "Dear " . $inv['full_name'] . ", this is a friendly reminder to pay your pending fees of ₹" . number_format($inv['total_amount'], 2) . " for Batch: " . $inv['invoice_no'],
        'type' => 'fee',
        'channels' => ['sms', 'whatsapp'] // Dispatched via simulated APIs
    ];
    
    if (sendNotification($pdo, $data)) {
        $count++;
    }
}

logActivity("Cron Job Run", "Fee Reminders sent to $count students.");
echo "Success: $count reminders dispatched.\n";
