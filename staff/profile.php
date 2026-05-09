<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';

protect_staff_page('STAFF', $conn);

$account_id = $_SESSION['account_id']; 
$alert_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // SECTION A: Password Hashing (Task #9)
    if (isset($_POST['update_password'])) {
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];

        $stmt_check = $conn->prepare("SELECT password_hash FROM accounts WHERE account_id = ?");
        $stmt_check->bind_param("i", $account_id);
        $stmt_check->execute();
        $res = $stmt_check->get_result()->fetch_assoc();

        if ($res && password_verify($old_pass, $res['password_hash'])) {
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_pass = $conn->prepare("UPDATE accounts SET password_hash = ? WHERE account_id = ?");
            $update_pass->bind_param("si", $hashed_password, $account_id);
            $update_pass->execute();
            $alert_msg = '<div class="alert alert-success fw-bold">Password updated successfully!</div>';
        } else {
            $alert_msg = '<div class="alert alert-danger fw-bold">Current password incorrect.</div>';
        }
    }

    // SECTION B: Staff Record & Email Update
    if (isset($_POST['update_staff'])) {
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $state = trim($_POST['assigned_state']);
        $phone = trim($_POST['phone_number']);
        
        $upd_acc = $conn->prepare("UPDATE accounts SET email = ? WHERE account_id = ?");
        $upd_acc->bind_param("si", $email, $account_id);
        $upd_acc->execute();

        $sql = "INSERT INTO staff (staff_id, full_name, assigned_state, phone_number) VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), assigned_state = VALUES(assigned_state), phone_number = VALUES(phone_number)";
        $stmt_upd = $conn->prepare($sql);
        $stmt_upd->bind_param("isss", $account_id, $full_name, $state, $phone);
        
        if ($stmt_upd->execute()) {
            $alert_msg = '<div class="alert alert-success fw-bold">Staff record updated successfully!</div>';
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
                            <input type="password" name="new_password" class="form-control" required>
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
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Assigned State</label>
                                <input type="text" name="assigned_state" class="form-control" value="<?php echo htmlspecialchars($user['assigned_state'] ?? ''); ?>" required>
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