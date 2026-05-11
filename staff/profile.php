<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';
include '../includes/functions.php';

protect_staff_page('STAFF', $conn);

$account_id = $_SESSION['account_id']; 
$alert_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_password'])) {
        $old_pass = $_POST['old_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        if (!validate_password_strength($new_pass)) {
            $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">Password does not meet requirements.</div>';
        } elseif ($new_pass !== $confirm_pass) {
            $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">Passwords do not match.</div>';
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
                $alert_msg = '<div class="alert alert-success fw-bold shadow-sm">Password updated successfully!</div>';
            } else {
                $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">Current password incorrect.</div>';
            }
        }
    }

    if (isset($_POST['update_staff'])) {
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone_number']);
        
        $upd_acc = $conn->prepare("UPDATE accounts SET email = ? WHERE account_id = ?");
        $upd_acc->bind_param("si", $email, $account_id);
        $upd_acc->execute();

        $stmt_upd = $conn->prepare("UPDATE staff SET full_name = ?, phone_number = ? WHERE staff_id = ?");
        $stmt_upd->bind_param("ssi", $full_name, $phone, $account_id);
        
        if ($stmt_upd->execute()) {
            $alert_msg = '<div class="alert alert-success fw-bold shadow-sm">Staff record updated successfully!</div>';
        }
    }
}

$stmt = $conn->prepare("SELECT a.email, s.full_name, s.assigned_state, s.phone_number FROM accounts a LEFT JOIN staff s ON a.account_id = s.staff_id WHERE a.account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include '../includes/header.php';
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4"><i class="fas fa-user-circle text-primary me-2"></i>My Profile & Security</h2>
    <?php echo $alert_msg; ?>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4 text-warning">Security</h4>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Current Password</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">New Password</label>
                            <input type="password" name="new_password" class="form-control" required onkeyup="checkPasswordRealtime(this.value, 'stf_')">
                            
                            <div class="mt-3 p-3 border rounded bg-light" style="font-size: 0.85rem;">
                                <ul class="list-unstyled mb-0">
                                    <li id="stf_length" class="text-danger"><i class="fas fa-times-circle me-2"></i>8+ Characters</li>
                                    <li id="stf_upper" class="text-danger"><i class="fas fa-times-circle me-2"></i>Uppercase (A-Z)</li>
                                    <li id="stf_lower" class="text-danger"><i class="fas fa-times-circle me-2"></i>Lowercase (a-z)</li>
                                    <li id="stf_number" class="text-danger"><i class="fas fa-times-circle me-2"></i>Number (0-9)</li>
                                    <li id="stf_symbol" class="text-danger"><i class="fas fa-times-circle me-2"></i>Symbol (@#$!)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Confirm Password</label>
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
                    <h4 class="fw-bold mb-4 border-bottom pb-2">Staff Information</h4>
                    <form method="POST">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Region (Locked)</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['assigned_state'] ?? 'N/A'); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <button type="submit" name="update_staff" class="btn btn-primary fw-bold px-5">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>