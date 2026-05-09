<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn); 

$account_id = $_SESSION['account_id'];
$alert_msg = '';
$alert_type = '';

function table_column_exists($conn, $table, $column)
{
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->bind_param("ss", $table, $column);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return ((int)($row['total'] ?? 0)) > 0;
    } catch (Throwable $e) {
        return false;
    }
}

$admin_has_department = table_column_exists($conn, 'admins', 'department');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_password'])) {
        $old_pass = $_POST['old_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';

        if (strlen($new_pass) < 8) {
            $alert_msg = 'New password must be at least 8 characters.';
            $alert_type = 'danger';
        } else {
            $stmt_check = $conn->prepare("SELECT password_hash FROM accounts WHERE account_id = ?");
            $stmt_check->bind_param("i", $account_id);
            $stmt_check->execute();
            $res = $stmt_check->get_result()->fetch_assoc();

            if ($res && password_verify($old_pass, $res['password_hash'])) {
                $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
                $update_pass = $conn->prepare("UPDATE accounts SET password_hash = ? WHERE account_id = ?");
                $update_pass->bind_param("si", $hashed_password, $account_id);

                if ($update_pass->execute()) {
                    $alert_msg = 'Password updated successfully.';
                    $alert_type = 'success';
                }
            } else {
                $alert_msg = 'Verification failed: Current password incorrect.';
                $alert_type = 'danger';
            }
        }
    }

    if (isset($_POST['update_profile'])) {
        $email = trim($_POST['email'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $department = trim($_POST['department'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $full_name === '' || ($admin_has_department && $department === '')) {
            $alert_msg = $admin_has_department ? 'Please enter a valid email, full name, and department.' : 'Please enter a valid email and full name.';
            $alert_type = 'danger';
        } else {
            try {
                $conn->begin_transaction();

                $upd_acc = $conn->prepare("UPDATE accounts SET email = ? WHERE account_id = ?");
                $upd_acc->bind_param("si", $email, $account_id);
                $upd_acc->execute();

                if ($admin_has_department) {
                    $update_stmt = $conn->prepare("INSERT INTO admins (admin_id, full_name, department) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), department = VALUES(department)");
                    $update_stmt->bind_param("iss", $account_id, $full_name, $department);
                } else {
                    $update_stmt = $conn->prepare("INSERT INTO admins (admin_id, full_name) VALUES (?, ?) ON DUPLICATE KEY UPDATE full_name = VALUES(full_name)");
                    $update_stmt->bind_param("is", $account_id, $full_name);
                }
                $update_stmt->execute();

                $conn->commit();
                $alert_msg = 'Profile and email successfully updated.';
                $alert_type = 'success';
            } catch (Exception $e) {
                $conn->rollback();
                $alert_msg = 'Profile update failed. Please check if the email is already used.';
                $alert_type = 'danger';
            }
        }
    }
}

$admin_department_select = $admin_has_department ? "ad.department" : "'HQ Administration' AS department";
$stmt = $conn->prepare("SELECT ad.full_name, $admin_department_select, a.email FROM accounts a LEFT JOIN admins ad ON a.account_id = ad.admin_id WHERE a.account_id = ?");
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
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-warning w-100 fw-bold mt-2">Update Password</button>
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
                        <?php if ($admin_has_department): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Department</label>
                                <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($admin['department'] ?? ''); ?>" required>
                            </div>
                        <?php endif; ?>
                        <button type="submit" name="update_profile" class="btn btn-primary fw-bold px-4">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
