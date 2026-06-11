<?php
include 'includes/db_connect.php';
include_once 'includes/email_change_helper.php';

$action = $_GET['action'] ?? '';
$token = $_GET['token'] ?? '';
$title = 'Email Change Request';
$message = 'This email change link is invalid or has expired.';
$alert_class = 'danger';

if (in_array($action, ['approve', 'reject'], true) && preg_match('/^[a-f0-9]{64}$/i', $token)) {
    ensure_email_change_requests_table($conn);
    $token_hash = hash('sha256', $token);
    $column = $action === 'approve' ? 'approve_token_hash' : 'reject_token_hash';

    $stmt = $conn->prepare("SELECT request_id, old_email, new_email, status FROM email_change_requests WHERE {$column} = ? LIMIT 1");
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc();

    if ($request && $request['status'] === 'PENDING') {
        $request_id = (int)$request['request_id'];

        if ($action === 'approve') {
            $update = $conn->prepare("UPDATE email_change_requests SET old_approved_at = NOW() WHERE request_id = ? AND status = 'PENDING'");
            $update->bind_param("i", $request_id);
            $update->execute();

            if (email_change_complete_if_ready($conn, $request_id)) {
                $title = 'Email Updated';
                $message = 'The email change has been approved and completed successfully.';
            } else {
                $title = 'Approval Received';
                $message = 'Old email approval is complete. The new email must still verify the OTP before the account email changes.';
            }
            $alert_class = 'success';
        } else {
            $update = $conn->prepare("UPDATE email_change_requests SET status = 'REJECTED', completed_at = NOW() WHERE request_id = ? AND status = 'PENDING'");
            $update->bind_param("i", $request_id);
            $update->execute();
            $title = 'Email Change Rejected';
            $message = 'The email change request has been rejected. Your account email remains unchanged.';
            $alert_class = 'warning';
        }
    } elseif ($request && $request['status'] === 'COMPLETED') {
        $title = 'Already Completed';
        $message = 'This email change request has already been completed.';
        $alert_class = 'info';
    } elseif ($request && $request['status'] === 'REJECTED') {
        $title = 'Already Rejected';
        $message = 'This email change request has already been rejected.';
        $alert_class = 'warning';
    }
}

include 'includes/header.php';
?>
<div class="container my-5 py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow">
                <div class="card-body p-5 text-dark">
                    <h3 class="fw-bold mb-3"><?php echo htmlspecialchars($title); ?></h3>
                    <div class="alert alert-<?php echo htmlspecialchars($alert_class); ?> mb-4">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                    <a href="login.php" class="btn btn-primary fw-bold">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
