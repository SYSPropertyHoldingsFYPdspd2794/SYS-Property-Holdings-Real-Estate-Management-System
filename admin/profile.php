<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';
include '../includes/functions.php';

protect_admin_page('ADMIN', $conn); 

$account_id = $_SESSION['account_id'];
$alert_msg = ''; $alert_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_password'])) {
        $old_pass = $_POST['old_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        if (!validate_password_strength($new_pass)) {
            $alert_msg = 'Password does not meet security requirements.';
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
                if ($upd->execute()) {
                    $alert_msg = 'Password updated successfully.';
                    $alert_type = 'success';
                }
            } else {
                $alert_msg = 'Current password incorrect.';
                $alert_type = 'danger';
            }
        }
    }

    if (isset($_POST['update_profile'])) {
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        
        $upd_acc = $conn->prepare("UPDATE accounts SET email = ? WHERE account_id = ?");
        $upd_acc->bind_param("si", $email, $account_id);
        $upd_acc->execute();

        $upd_adm = $conn->prepare("UPDATE admins SET full_name = ? WHERE admin_id = ?");
        $upd_adm->bind_param("si", $full_name, $account_id);
        
        if ($upd_adm->execute()) {
            $alert_msg = 'Profile updated successfully.';
            $alert_type = 'success';
        }
    }
}

$stmt = $conn->prepare("SELECT ad.full_name, a.email FROM accounts a LEFT JOIN admins ad ON a.account_id = ad.admin_id WHERE a.account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

include '../includes/header.php';
?>

<div class="container my-5">
    <?php if ($alert_msg): ?>
        <div class="alert alert-<?php echo $alert_type; ?> shadow-sm"><?php echo htmlspecialchars($alert_msg); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-3">Security</h4>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Current Password</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">New Password</label>
                            <input type="password" name="new_password" class="form-control" required onkeyup="checkPasswordRealtime(this.value, 'adm_')">
                            
                            <div class="mt-3 p-3 border rounded bg-light" style="font-size: 0.85rem;">
                                <ul class="list-unstyled mb-0">
                                    <li id="adm_length" class="text-danger"><i class="fas fa-times-circle me-2"></i>8+ Characters</li>
                                    <li id="adm_upper" class="text-danger"><i class="fas fa-times-circle me-2"></i>Uppercase (A-Z)</li>
                                    <li id="adm_lower" class="text-danger"><i class="fas fa-times-circle me-2"></i>Lowercase (a-z)</li>
                                    <li id="adm_number" class="text-danger"><i class="fas fa-times-circle me-2"></i>Number (0-9)</li>
                                    <li id="adm_symbol" class="text-danger"><i class="fas fa-times-circle me-2"></i>Symbol (@#$!)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-warning w-100 fw-bold">Update Password</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4 border-bottom pb-2">Admin Profile</h4>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($admin['full_name'] ?? ''); ?>" required>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary fw-bold px-4">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>