<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';

protect_customer_page('CUSTOMER', $conn);

$account_id = $_SESSION['account_id'];
$alert_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_password'])) {
        $old_pass = $_POST['old_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        if ($old_pass === '' || $new_pass === '' || $confirm_pass === '') {
            $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">Please fill in all password fields.</div>';
        } elseif ($new_pass !== $confirm_pass) {
            $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">New password and confirm password do not match.</div>';
        } else {
            $stmt_check = $conn->prepare("SELECT password_hash FROM accounts WHERE account_id = ?");
            $stmt_check->bind_param("i", $account_id);
            $stmt_check->execute();
            $res = $stmt_check->get_result()->fetch_assoc();
            $stmt_check->close();

            if (!$res || !password_verify($old_pass, $res['password_hash'])) {
                $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">Current password incorrect.</div>';
            } else {
                $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
                $update_pass = $conn->prepare("UPDATE accounts SET password_hash = ? WHERE account_id = ?");
                $update_pass->bind_param("si", $hashed_password, $account_id);

                if ($update_pass->execute()) {
                    $alert_msg = '<div class="alert alert-success fw-bold shadow-sm">Password successfully updated!</div>';
                } else {
                    $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">Failed to update password.</div>';
                }
                $update_pass->close();
            }
        }
    }

    if (isset($_POST['update_customer'])) {
        $email = trim($_POST['email'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone_number'] ?? '');
        $marital = $_POST['marital_status'] ?? 'SINGLE';
        $dep = max(0, intval($_POST['dependents_count'] ?? 0));
        $occ = trim($_POST['occupation'] ?? '');
        $inc = floatval($_POST['monthly_income'] ?? 0);

        if ($email === '' || $full_name === '' || $phone === '' || $occ === '') {
            $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">Please fill in all required profile fields.</div>';
        } else {
            $update_email = $conn->prepare("UPDATE accounts SET email = ? WHERE account_id = ?");
            $update_email->bind_param("si", $email, $account_id);
            $update_email->execute();
            $update_email->close();

            $sql = "UPDATE customers SET
                    full_name = ?,
                    phone_number = ?,
                    marital_status = ?,
                    dependents_count = ?,
                    occupation = ?,
                    monthly_income = ?
                    WHERE customer_id = ?";
            $stmt_upd = $conn->prepare($sql);
            $stmt_upd->bind_param("sssisdi", $full_name, $phone, $marital, $dep, $occ, $inc, $account_id);

            if ($stmt_upd->execute()) {
                $alert_msg = '<div class="alert alert-success fw-bold shadow-sm">Customer profile and email updated successfully!</div>';
            } else {
                $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">Failed to update customer profile.</div>';
            }
            $stmt_upd->close();
        }
    }
}

$stmt = $conn->prepare("SELECT c.*, a.email
                        FROM customers c
                        JOIN accounts a ON c.customer_id = a.account_id
                        WHERE c.customer_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="mb-5">
        <h2 class="fw-bold m-0"><i class="fas fa-user-circle text-primary me-2"></i>My Profile & Security</h2>
    </div>

    <?php echo $alert_msg; ?>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4 text-warning"><i class="fas fa-lock me-2"></i>Security</h4>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Current Password</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-warning w-100 fw-bold">Update Credentials</button>
                        <div class="text-center mt-3">
                            <a href="https://wa.link/k61mrv" target="_blank" rel="noopener noreferrer" class="fw-bold text-decoration-none">Forgot password?</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4 text-dark border-bottom pb-2">Customer Information</h4>
                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-primary">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control border-primary" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Marital Status</label>
                                <select name="marital_status" class="form-select" required>
                                    <option value="SINGLE" <?php echo (($user['marital_status'] ?? '') === 'SINGLE') ? 'selected' : ''; ?>>Single</option>
                                    <option value="MARRIED" <?php echo (($user['marital_status'] ?? '') === 'MARRIED') ? 'selected' : ''; ?>>Married</option>
                                    <option value="DIVORCED" <?php echo (($user['marital_status'] ?? '') === 'DIVORCED') ? 'selected' : ''; ?>>Divorced</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Dependents Count</label>
                                <input type="number" name="dependents_count" class="form-control" value="<?php echo htmlspecialchars($user['dependents_count'] ?? 0); ?>" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Occupation</label>
                                <input type="text" name="occupation" class="form-control" value="<?php echo htmlspecialchars($user['occupation'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Monthly Income (RM)</label>
                                <input type="number" step="0.01" name="monthly_income" class="form-control" value="<?php echo htmlspecialchars($user['monthly_income'] ?? 0); ?>" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" name="update_customer" class="btn btn-primary btn-lg fw-bold px-5 shadow-sm">
                                <i class="fas fa-save me-2"></i>Update Customer Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
