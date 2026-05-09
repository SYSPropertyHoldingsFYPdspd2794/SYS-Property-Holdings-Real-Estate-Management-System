<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn);

$account_id = $_SESSION['account_id'];
$alert_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_password'])) {
        $old_pass = $_POST['old_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        $stmt_check = $conn->prepare("SELECT password_hash FROM accounts WHERE account_id = ?");
        $stmt_check->bind_param("i", $account_id);
        $stmt_check->execute();
        $res = $stmt_check->get_result()->fetch_assoc();

        if ($new_pass !== $confirm_pass) {
            $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">New password and confirm password do not match.</div>';
        } elseif ($res && password_verify($old_pass, $res['password_hash'])) {
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_pass = $conn->prepare("UPDATE accounts SET password_hash = ? WHERE account_id = ?");
            $update_pass->bind_param("si", $hashed_password, $account_id);

            if ($update_pass->execute()) {
                $alert_msg = '<div class="alert alert-success fw-bold shadow-sm">Password successfully updated!</div>';
            }
        } else {
            $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">Current password incorrect.</div>';
        }
    }

    if (isset($_POST['update_admin'])) {
        $email = trim($_POST['email'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $department = trim($_POST['department'] ?? '');

        if ($email === '' || $full_name === '' || $department === '') {
            $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">Email, full name and department are required.</div>';
        } else {
            $update_email = $conn->prepare("UPDATE accounts SET email = ? WHERE account_id = ?");
            $update_email->bind_param("si", $email, $account_id);
            $update_email->execute();

            $sql = "INSERT INTO admins (admin_id, full_name, department) VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    full_name = VALUES(full_name),
                    department = VALUES(department)";

            $stmt_upd = $conn->prepare($sql);
            $stmt_upd->bind_param("iss", $account_id, $full_name, $department);

            if ($stmt_upd->execute()) {
                $alert_msg = '<div class="alert alert-success fw-bold shadow-sm">Admin profile and email updated successfully!</div>';
            }
            $stmt_upd->close();
        }
    }
}

$stmt = $conn->prepare("SELECT a.email, ad.full_name, ad.department
                        FROM accounts a
                        LEFT JOIN admins ad ON a.account_id = ad.admin_id
                        WHERE a.account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="mb-5">
        <h2 class="fw-bold m-0"><i class="fas fa-user-shield text-primary me-2"></i>My Profile & Security</h2>
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
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4 text-dark border-bottom pb-2">Admin Information</h4>
                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($admin['full_name'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Role</label>
                                <input type="text" class="form-control bg-light" value="ADMIN" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-primary">Department</label>
                                <input type="text" name="department" class="form-control border-primary" value="<?php echo htmlspecialchars($admin['department'] ?? 'HQ Administration'); ?>" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" name="update_admin" class="btn btn-primary btn-lg fw-bold px-5 shadow-sm">
                                <i class="fas fa-save me-2"></i>Update Admin Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
