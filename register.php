<?php
include 'includes/header.php';
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
include_once 'includes/email_helper.php';

$error_message = '';
$otp_message = '';
$otp_error_message = '';
$show_duplicate_alert = false; 
$show_success_alert = false; 
$show_otp_modal = false;
$duplicate_email = '';

function email_exists($conn, $email) {
    $stmt_check = $conn->prepare("SELECT email FROM accounts WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();
    $exists = $stmt_check->num_rows > 0;
    $stmt_check->close();

    return $exists;
}

function create_pending_registration_otp($conn, $post_data, &$error_message, &$show_duplicate_alert, &$duplicate_email, &$otp_message, &$show_otp_modal) {
    $email = trim($post_data['email'] ?? '');
    $password = $post_data['password'] ?? '';
    $confirm_password = $post_data['confirm_password'] ?? '';
    $full_name = trim($post_data['full_name'] ?? '');
    $phone_number = trim($post_data['phone_number'] ?? '');
    $marital_status = $post_data['marital_status'] ?? '';
    $dependents_count = (int)($post_data['dependents_count'] ?? 0);
    $occupation = trim($post_data['occupation'] ?? '');
    $monthly_income = (float)($post_data['monthly_income'] ?? 0);

    if (!validate_password_strength($password)) {
        $error_message = 'Password is too weak. Please follow the requirements below.';
        return;
    }

    if ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
        return;
    }

    if (email_exists($conn, $email)) {
        $duplicate_email = $email;
        $show_duplicate_alert = true;
        return;
    }

    $otp = (string)random_int(100000, 999999);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $_SESSION['pending_registration'] = [
        'email' => $email,
        'password_hash' => $hashed_password,
        'full_name' => $full_name,
        'phone_number' => $phone_number,
        'marital_status' => $marital_status,
        'dependents_count' => $dependents_count,
        'occupation' => $occupation,
        'monthly_income' => $monthly_income,
        'otp' => $otp,
        'otp_expires_at' => time() + 600,
        'otp_attempts' => 0,
    ];

    if (!send_registration_otp($email, $full_name, $otp)) {
        unset($_SESSION['pending_registration']);
        $error_message = 'Unable to send verification email. Please check the email address or mail server settings.';
        return;
    }

    $otp_message = 'A 6-digit verification code has been sent to ' . $email . '.';
    $show_otp_modal = true;
}

function complete_pending_registration($conn, &$otp_error_message, &$show_success_alert, &$show_otp_modal) {
    if (!isset($_SESSION['pending_registration'])) {
        $otp_error_message = 'Your verification session has expired. Please register again.';
        $show_otp_modal = true;
        return;
    }

    $pending = $_SESSION['pending_registration'];
    $submitted_otp = trim($_POST['otp'] ?? '');

    if (($pending['otp_expires_at'] ?? 0) < time()) {
        unset($_SESSION['pending_registration']);
        $otp_error_message = 'This OTP has expired. Please register again to receive a new code.';
        $show_otp_modal = true;
        return;
    }

    if (($pending['otp_attempts'] ?? 0) >= 5) {
        unset($_SESSION['pending_registration']);
        $otp_error_message = 'Too many incorrect attempts. Please register again.';
        $show_otp_modal = true;
        return;
    }

    if (!preg_match('/^\d{6}$/', $submitted_otp) || $submitted_otp !== ($pending['otp'] ?? '')) {
        $_SESSION['pending_registration']['otp_attempts'] = ($pending['otp_attempts'] ?? 0) + 1;
        $otp_error_message = 'Invalid OTP. Please check the 6-digit code and try again.';
        $show_otp_modal = true;
        return;
    }

    if (email_exists($conn, $pending['email'])) {
        unset($_SESSION['pending_registration']);
        $otp_error_message = 'This email is already registered. Please login or use another email.';
        $show_otp_modal = true;
        return;
    }

    $role = 'CUSTOMER';
    $conn->begin_transaction();

    try {
        $stmt_account = $conn->prepare("INSERT INTO accounts (email, password_hash, role) VALUES (?,?,?)");
        $stmt_account->bind_param("sss", $pending['email'], $pending['password_hash'], $role);
        $stmt_account->execute();

        $account_id = $conn->insert_id;

        $stmt_customer = $conn->prepare("INSERT INTO customers (customer_id, full_name, phone_number, marital_status, dependents_count, occupation, monthly_income) VALUES (?,?,?,?,?,?,?)");
        $stmt_customer->bind_param("isssisd", $account_id, $pending['full_name'], $pending['phone_number'], $pending['marital_status'], $pending['dependents_count'], $pending['occupation'], $pending['monthly_income']);
        $stmt_customer->execute();

        $conn->commit();
        unset($_SESSION['pending_registration']);
        $show_success_alert = true;
    } catch (Exception $e) {
        $conn->rollback();
        $otp_error_message = 'Registration failed. Please try again later.';
        $show_otp_modal = true;
    }
}

function resend_pending_registration_otp(&$otp_error_message, &$otp_message, &$show_otp_modal) {
    if (!isset($_SESSION['pending_registration'])) {
        $otp_error_message = 'Your verification session has expired. Please register again.';
        $show_otp_modal = true;
        return;
    }

    $otp = (string)random_int(100000, 999999);
    $_SESSION['pending_registration']['otp'] = $otp;
    $_SESSION['pending_registration']['otp_expires_at'] = time() + 600;
    $_SESSION['pending_registration']['otp_attempts'] = 0;

    $pending = $_SESSION['pending_registration'];
    if (!send_registration_otp($pending['email'], $pending['full_name'], $otp)) {
        $otp_error_message = 'Unable to resend the OTP. Please try again later.';
        $show_otp_modal = true;
        return;
    }

    $otp_message = 'A new verification code has been sent to ' . $pending['email'] . '.';
    $show_otp_modal = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $register_action = $_POST['register_action'] ?? 'request_otp';

    if ($register_action === 'verify_otp') {
        complete_pending_registration($conn, $otp_error_message, $show_success_alert, $show_otp_modal);
    } elseif ($register_action === 'resend_otp') {
        resend_pending_registration_otp($otp_error_message, $otp_message, $show_otp_modal);
    } else {
        create_pending_registration_otp($conn, $_POST, $error_message, $show_duplicate_alert, $duplicate_email, $otp_message, $show_otp_modal);
    }
}

$form_values = $_POST;
if (!empty($_SESSION['pending_registration']) && (($_POST['register_action'] ?? '') === 'verify_otp' || ($_POST['register_action'] ?? '') === 'resend_otp')) {
    $form_values = $_SESSION['pending_registration'];
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container my-5 py-4">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-sm border-0 auth-card">
                <div class="card-body p-5">
                    <div class="text-center mb-5">
                        <div class="section-kicker mb-2">Start your home journey</div>
                        <h2 class="fw-bold mb-2">Create an Account</h2>
                        <p class="text-muted mb-0">Save properties, book visits, and track affordable housing applications.</p>
                    </div>

                    <?php if ($error_message !== ''): ?>
                        <div class="alert alert-danger shadow-sm border-0 mb-4">
                            <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="register.php">
                        <input type="hidden" name="register_action" value="request_otp">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" name="full_name" class="form-control py-2" value="<?php echo isset($form_values['full_name']) ? htmlspecialchars($form_values['full_name']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control py-2" value="<?php echo isset($form_values['email']) ? htmlspecialchars($form_values['email']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Password</label>
                                <input type="password" name="password" class="form-control py-2" required onkeyup="checkPasswordRealtime(this.value, 'reg_')">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control py-2" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="p-3 border rounded bg-light shadow-sm">
                                    <ul class="list-unstyled mb-0 d-flex flex-wrap justify-content-between align-items-center">
                                        <li id="reg_length" class="text-danger small mx-2 my-1"><i class="fas fa-times-circle me-1"></i> 8+ Characters</li>
                                        <li id="reg_upper" class="text-danger small mx-2 my-1"><i class="fas fa-times-circle me-1"></i> Uppercase (A-Z)</li>
                                        <li id="reg_lower" class="text-danger small mx-2 my-1"><i class="fas fa-times-circle me-1"></i> Lowercase (a-z)</li>
                                        <li id="reg_number" class="text-danger small mx-2 my-1"><i class="fas fa-times-circle me-1"></i> Number (0-9)</li>
                                        <li id="reg_symbol" class="text-danger small mx-2 my-1"><i class="fas fa-times-circle me-1"></i> Symbol (@#$!)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control py-2" value="<?php echo isset($form_values['phone_number']) ? htmlspecialchars($form_values['phone_number']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Marital Status</label>
                                <select name="marital_status" class="form-select py-2" required>
                                    <option value="" disabled <?php echo !isset($form_values['marital_status']) ? 'selected' : ''; ?>>Select Status</option>
                                    <option value="SINGLE" <?php echo (isset($form_values['marital_status']) && $form_values['marital_status'] == 'SINGLE') ? 'selected' : ''; ?>>Single</option>
                                    <option value="MARRIED" <?php echo (isset($form_values['marital_status']) && $form_values['marital_status'] == 'MARRIED') ? 'selected' : ''; ?>>Married</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Dependents</label>
                                <input type="number" name="dependents_count" class="form-control py-2" min="0" value="<?php echo isset($form_values['dependents_count']) ? htmlspecialchars($form_values['dependents_count']) : '0'; ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Occupation</label>
                                <input type="text" name="occupation" class="form-control py-2" value="<?php echo isset($form_values['occupation']) ? htmlspecialchars($form_values['occupation']) : ''; ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Income (RM)</label>
                                <input type="number" step="0.01" name="monthly_income" class="form-control py-2" value="<?php echo isset($form_values['monthly_income']) ? htmlspecialchars($form_values['monthly_income']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm">Register Now</button>
                        </div>
                    </form>
                    <div class="text-center mt-4">
                        <span class="text-muted">Already have an account?</span> <a href="login.php" class="fw-bold text-decoration-none">Login here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="otpVerificationModal" tabindex="-1" aria-labelledby="otpVerificationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="otpVerificationModalLabel">
                    <i class="fas fa-envelope-circle-check me-2 text-warning"></i>Email Verification
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

                <p class="text-muted small mb-3">Enter the 6-digit OTP sent to your email to complete your registration.</p>
                <form method="POST" action="register.php" id="otpVerifyForm">
                    <input type="hidden" name="register_action" value="verify_otp">
                    <label class="form-label fw-bold">Verification Code</label>
                    <input type="text" name="otp" class="form-control form-control-lg text-center fw-bold" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" autocomplete="one-time-code" required>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-between">
                <form method="POST" action="register.php" class="m-0">
                    <input type="hidden" name="register_action" value="resend_otp">
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="fas fa-rotate-right me-1"></i>Resend Code
                    </button>
                </form>
                <button type="submit" form="otpVerifyForm" class="btn btn-primary fw-bold">
                    <i class="fas fa-check me-1"></i>Verify OTP
                </button>
            </div>
        </div>
    </div>
</div>

<?php if ($show_duplicate_alert): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Email Already Registered',
        text: 'The email ' + <?php echo json_encode($duplicate_email); ?> + ' is already in use. Please use another or login.',
        confirmButtonColor: '#0d6efd'
    });
</script>
<?php endif; ?>

<?php if ($show_success_alert): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Registration Successful',
        text: 'Welcome to SYS Property Holdings!',
        confirmButtonColor: '#0d6efd'
    }).then(() => {
        window.location.href = 'login.php?registration=success';
    });
</script>
<?php endif; ?>

<?php if ($show_otp_modal): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('otpVerificationModal');
    if (modalElement && window.bootstrap) {
        const otpModal = new bootstrap.Modal(modalElement);
        otpModal.show();
    }
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
