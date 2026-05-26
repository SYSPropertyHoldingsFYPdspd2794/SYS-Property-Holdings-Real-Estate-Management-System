<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';
include '../includes/functions.php';

protect_customer_page('CUSTOMER', $conn);

$account_id = $_SESSION['account_id'];
$alert_msg = '';
$alert_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $old_pass = $_POST['old_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (!validate_password_strength($new_pass)) {
        $alert_msg = 'Password must follow all security rules.';
        $alert_type = 'danger';
    } elseif ($new_pass !== $confirm_pass) {
        $alert_msg = 'Passwords do not match.';
        $alert_type = 'danger';
    } else {
        $stmt_check = $conn->prepare("SELECT password_hash FROM accounts WHERE account_id = ?");
        $stmt_check->bind_param("i", $account_id);
        $stmt_check->execute();
        $res = $stmt_check->get_result()->fetch_assoc();

        if ($res && password_verify($old_pass, $res['password_hash'])) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE accounts SET password_hash = ? WHERE account_id = ?");
            $upd->bind_param("si", $hashed, $account_id);
            $upd->execute();
            $alert_msg = 'Password successfully updated.';
            $alert_type = 'success';
        } else {
            $alert_msg = 'Current password incorrect.';
            $alert_type = 'danger';
        }
    }
}

include '../includes/header.php';
?>

<div class="container my-5">
    <?php if ($alert_msg): ?>
        <div class="alert alert-<?php echo $alert_type; ?> shadow-sm"><?php echo htmlspecialchars($alert_msg); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-3 col-md-4">
            <div class="profile-settings-nav shadow-sm">
                <a href="profile.php" class="profile-settings-link">
                    <i class="fas fa-user me-2"></i>Profile
                </a>
                <a href="change_password.php" class="profile-settings-link active">
                    <i class="fas fa-lock me-2"></i>Change Password
                </a>
                <a href="privacy_data.php" class="profile-settings-link">
                    <i class="fas fa-shield-alt me-2"></i>Privacy & Data
                </a>
            </div>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4 border-bottom pb-2">Change Password</h4>
                    <form method="POST" class="password-form">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Current Password</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">New Password</label>
                            <input type="password" name="new_password" class="form-control" required onkeyup="checkPasswordRealtime(this.value, 'cus_')">

                            <div class="mt-3 p-3 border rounded bg-light" style="font-size: 0.85rem;">
                                <ul class="list-unstyled mb-0">
                                    <li id="cus_length" class="text-danger"><i class="fas fa-times-circle me-2"></i>8+ Characters</li>
                                    <li id="cus_upper" class="text-danger"><i class="fas fa-times-circle me-2"></i>Uppercase (A-Z)</li>
                                    <li id="cus_lower" class="text-danger"><i class="fas fa-times-circle me-2"></i>Lowercase (a-z)</li>
                                    <li id="cus_number" class="text-danger"><i class="fas fa-times-circle me-2"></i>Number (0-9)</li>
                                    <li id="cus_symbol" class="text-danger"><i class="fas fa-times-circle me-2"></i>Symbol (@#$!)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-primary fw-bold px-4">Update Password</button>
                        <a href="https://wa.link/bzspzh" target="_blank" rel="noopener noreferrer" class="btn btn-link fw-bold text-decoration-none ms-2">
                            Forgot Password?
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-settings-nav {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.06);
    }
    .profile-settings-link {
        display: flex;
        align-items: center;
        padding: 16px 18px;
        color: #212529;
        font-weight: 700;
        text-decoration: none;
        border-left: 4px solid transparent;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }
    .profile-settings-link:last-child {
        border-bottom: 0;
    }
    .profile-settings-link:hover,
    .profile-settings-link.active {
        color: #0d6efd;
        background: #f8fbff;
        border-left-color: #0d6efd;
    }
    .password-form {
        max-width: 560px;
    }
</style>

<?php include '../includes/footer.php'; ?>
