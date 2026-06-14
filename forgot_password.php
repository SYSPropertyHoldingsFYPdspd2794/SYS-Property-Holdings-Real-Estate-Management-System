<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
include_once 'includes/email_helper.php';

$error_message = '';
$otp_message = '';
$otp_error_message = '';
$show_otp_modal = false;
$show_success_alert = false;
$allowed_roles = ['CUSTOMER', 'STAFF'];

function request_password_reset_otp($conn, $post_data, $allowed_roles, &$error_message, &$otp_message, &$show_otp_modal) {
    $email = trim($post_data['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
        return;
    }

    $stmt = $conn->prepare("SELECT account_id, email, role FROM accounts WHERE email = ? AND role IN ('CUSTOMER', 'STAFF') LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $account = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$account) {
        $error_message = 'No matching customer or staff account was found.';
        return;
    }

    $otp = (string)random_int(100000, 999999);
    $_SESSION['pending_password_reset'] = [
        'account_id' => (int)$account['account_id'],
        'email' => $account['email'],
        'role' => $account['role'],
        'otp' => $otp,
        'otp_expires_at' => time() + 600,
        'otp_attempts' => 0,
    ];

    if (!send_password_reset_otp($account['email'], $account['role'], $otp)) {
        unset($_SESSION['pending_password_reset']);
        $error_message = 'Unable to send reset OTP. Please check the mail server settings.';
        return;
    }

    $otp_message = 'A 6-digit password reset code has been sent to ' . $account['email'] . '.';
    $show_otp_modal = true;
}

function complete_password_reset($conn, &$otp_error_message, &$show_success_alert, &$show_otp_modal) {
    if (!isset($_SESSION['pending_password_reset'])) {
        $otp_error_message = 'Your reset session has expired. Please request a new code.';
        $show_otp_modal = true;
        return;
    }

    $pending = $_SESSION['pending_password_reset'];
    $submitted_otp = trim($_POST['otp'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (($pending['otp_expires_at'] ?? 0) < time()) {
        unset($_SESSION['pending_password_reset']);
        $otp_error_message = 'This OTP has expired. Please request a new code.';
        $show_otp_modal = true;
        return;
    }

    if (($pending['otp_attempts'] ?? 0) >= 5) {
        unset($_SESSION['pending_password_reset']);
        $otp_error_message = 'Too many incorrect attempts. Please request a new code.';
        $show_otp_modal = true;
        return;
    }

    if (!preg_match('/^\d{6}$/', $submitted_otp) || $submitted_otp !== ($pending['otp'] ?? '')) {
        $_SESSION['pending_password_reset']['otp_attempts'] = ($pending['otp_attempts'] ?? 0) + 1;
        $otp_error_message = 'Invalid OTP. Please check the 6-digit code and try again.';
        $show_otp_modal = true;
        return;
    }

    if (!validate_password_strength($new_password)) {
        $otp_error_message = 'New password is too weak. Please follow the password requirements.';
        $show_otp_modal = true;
        return;
    }

    if ($new_password !== $confirm_password) {
        $otp_error_message = 'New passwords do not match.';
        $show_otp_modal = true;
        return;
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE accounts SET password_hash = ? WHERE account_id = ? AND role IN ('CUSTOMER', 'STAFF')");
    $stmt->bind_param("si", $hashed_password, $pending['account_id']);
    $stmt->execute();
    $updated = $stmt->affected_rows >= 0;
    $stmt->close();

    if (!$updated) {
        $otp_error_message = 'Unable to reset password. Please try again later.';
        $show_otp_modal = true;
        return;
    }

    unset($_SESSION['pending_password_reset']);
    $show_success_alert = true;
}

function resend_password_reset_otp(&$otp_error_message, &$otp_message, &$show_otp_modal) {
    if (!isset($_SESSION['pending_password_reset'])) {
        $otp_error_message = 'Your reset session has expired. Please request a new code.';
        $show_otp_modal = true;
        return;
    }

    $otp = (string)random_int(100000, 999999);
    $_SESSION['pending_password_reset']['otp'] = $otp;
    $_SESSION['pending_password_reset']['otp_expires_at'] = time() + 600;
    $_SESSION['pending_password_reset']['otp_attempts'] = 0;

    $pending = $_SESSION['pending_password_reset'];
    if (!send_password_reset_otp($pending['email'], $pending['role'], $otp)) {
        $otp_error_message = 'Unable to resend the OTP. Please try again later.';
        $show_otp_modal = true;
        return;
    }

    $otp_message = 'A new password reset code has been sent to ' . $pending['email'] . '.';
    $show_otp_modal = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reset_action = $_POST['reset_action'] ?? 'request_otp';

    if ($reset_action === 'verify_otp') {
        complete_password_reset($conn, $otp_error_message, $show_success_alert, $show_otp_modal);
    } elseif ($reset_action === 'resend_otp') {
        resend_password_reset_otp($otp_error_message, $otp_message, $show_otp_modal);
    } else {
        request_password_reset_otp($conn, $_POST, $allowed_roles, $error_message, $otp_message, $show_otp_modal);
    }
}

$form_values = $_POST;
$from_settings = (($_GET['from'] ?? '') === 'settings') || (($_POST['from_settings'] ?? '') === '1');
$back_link = 'login.php';
$back_text = 'Back to login';

if ($from_settings && isset($_SESSION['role'], $_SESSION['account_id']) && in_array($_SESSION['role'], $allowed_roles, true)) {
    $back_link = strtolower($_SESSION['role']) . '/change_password.php';
    $back_text = 'Back to setting';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $stmt_prefill = $conn->prepare("SELECT email FROM accounts WHERE account_id = ? AND role IN ('CUSTOMER', 'STAFF') LIMIT 1");
        $stmt_prefill->bind_param("i", $_SESSION['account_id']);
        $stmt_prefill->execute();
        $prefill_account = $stmt_prefill->get_result()->fetch_assoc();
        $stmt_prefill->close();

        if ($prefill_account) {
            $form_values['email'] = $prefill_account['email'];
        }
    }
}

if (!empty($_SESSION['pending_password_reset']) && (($_POST['reset_action'] ?? '') === 'verify_otp' || ($_POST['reset_action'] ?? '') === 'resend_otp')) {
    $form_values = $_SESSION['pending_password_reset'];
}

include 'includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container my-5 py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 auth-card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="section-kicker mb-2">Account recovery</div>
                        <img loading="lazy" src="SYS Property Catalog/SYS_Property_Holdings_Icon.jpeg" alt="SYS Property Holdings" style="max-height: 60px; margin-bottom: 15px; border-radius: 6px;">
                        <h2 class="fw-bold mb-2">Forgot Password</h2>
                        <p class="text-muted mb-0">Receive a 6-digit OTP and set a new password.</p>
                    </div>

                    <?php if ($error_message !== ''): ?>
                        <div class="alert alert-danger shadow-sm border-0 mb-4">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="forgot_password.php">
                        <input type="hidden" name="reset_action" value="request_otp">
                        <?php if ($from_settings): ?>
                            <input type="hidden" name="from_settings" value="1">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo isset($form_values['email']) ? htmlspecialchars($form_values['email']) : ''; ?>" required>
                        </div>
                        <div class="mb-4"></div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold">Send Reset OTP</button>
                        </div>
                        <div class="text-center">
                            <a href="<?php echo htmlspecialchars($back_link); ?>" class="text-decoration-none fw-bold"><?php echo htmlspecialchars($back_text); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="passwordResetOtpModal" tabindex="-1" aria-labelledby="passwordResetOtpModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="passwordResetOtpModalLabel">
                    <i class="fas fa-key me-2 text-warning"></i>Reset Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-dark">
                <?php if ($otp_message !== ''): ?>
                    <div class="alert alert-success border-0 shadow-sm small">
                        <?php echo htmlspecialchars($otp_message); ?>
                    </div>
                <?php endif; ?>
                <?php if ($otp_error_message !== ''): ?>
                    <div class="alert alert-danger border-0 shadow-sm small">
                        <?php echo htmlspecialchars($otp_error_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="forgot_password.php" id="passwordResetOtpForm">
                    <input type="hidden" name="reset_action" value="verify_otp">
                    <?php if ($from_settings): ?>
                        <input type="hidden" name="from_settings" value="1">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Verification Code</label>
                        <input type="text" name="otp" class="form-control form-control-lg text-center fw-bold" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" autocomplete="one-time-code" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Password</label>
                        <input type="password" name="new_password" class="form-control" required onkeyup="checkPasswordRealtime(this.value, 'reset_')">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <div class="p-3 border rounded bg-light shadow-sm">
                        <ul class="list-unstyled mb-0 d-flex flex-wrap justify-content-between align-items-center">
                            <li id="reset_length" class="text-danger small mx-2 my-1"><i class="fas fa-times-circle me-1"></i> 8+ Characters</li>
                            <li id="reset_upper" class="text-danger small mx-2 my-1"><i class="fas fa-times-circle me-1"></i> Uppercase (A-Z)</li>
                            <li id="reset_lower" class="text-danger small mx-2 my-1"><i class="fas fa-times-circle me-1"></i> Lowercase (a-z)</li>
                            <li id="reset_number" class="text-danger small mx-2 my-1"><i class="fas fa-times-circle me-1"></i> Number (0-9)</li>
                            <li id="reset_symbol" class="text-danger small mx-2 my-1"><i class="fas fa-times-circle me-1"></i> Symbol (@#$!)</li>
                        </ul>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-between">
                <form method="POST" action="forgot_password.php" class="m-0">
                    <input type="hidden" name="reset_action" value="resend_otp">
                    <?php if ($from_settings): ?>
                        <input type="hidden" name="from_settings" value="1">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="fas fa-rotate-right me-1"></i>Resend Code
                    </button>
                </form>
                <button type="submit" form="passwordResetOtpForm" class="btn btn-primary fw-bold">
                    <i class="fas fa-check me-1"></i>Reset Password
                </button>
            </div>
        </div>
    </div>
</div>

<?php if ($show_success_alert): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Password Reset Successful',
        text: 'You can now login with your new password.',
        confirmButtonColor: '#0d6efd'
    }).then(() => {
        window.location.href = 'login.php';
    });
</script>
<?php endif; ?>

<?php if ($show_otp_modal): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('passwordResetOtpModal');
    if (modalElement && window.bootstrap) {
        const otpModal = new bootstrap.Modal(modalElement);
        otpModal.show();
    }
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
