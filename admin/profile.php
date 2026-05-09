<?php
// 1. Include Dependencies
include '../includes/db_connect.php';
include '../includes/auth_check.php';

// 2. Security Check
protect_admin_page('ADMIN', $conn); 

$account_id = $_SESSION['account_id'];
$alert_msg = '';
$alert_type = '';

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // SECTION A: Handle Password Update (Task #9)
    if (isset($_POST['update_password'])) {
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];

        // FETCH CURRENT HASH - Using 'password_hash' column as per your DB
        $stmt_check = $conn->prepare("SELECT password_hash FROM accounts WHERE account_id = ?");
        $stmt_check->bind_param("i", $account_id);
        $stmt_check->execute();
        $res = $stmt_check->get_result()->fetch_assoc();

        if ($res && password_verify($old_pass, $res['password_hash'])) {
            // HASH NEW PASSWORD string
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);

            // UPDATE accounts table
            $update_pass = $conn->prepare("UPDATE accounts SET password_hash = ? WHERE account_id = ?");
            $update_pass->bind_param("si", $hashed_password, $account_id);

            if ($update_pass->execute()) {
                $alert_msg = 'Password successfully hashed and updated.';
                $alert_type = 'success';
            }
        } else {
            $alert_msg = 'Verification failed: Current password incorrect.';
            $alert_type = 'danger';
        }
    }

    // SECTION B: Handle Profile Update
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name']);
        $department = trim($_POST['department']);

        $update_stmt = $conn->prepare("INSERT INTO admins (admin_id, full_name, department) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), department = VALUES(department)");
        $update_stmt->bind_param("iss", $account_id, $full_name, $department);

        if ($update_stmt->execute()) {
            $alert_msg = 'Profile details updated.';
            $alert_type = 'success';
        }
    }
}

// 4. Fetch Refreshed Data for Display
$stmt = $conn->prepare("SELECT ad.full_name, ad.department, a.email FROM accounts a LEFT JOIN admins ad ON a.account_id = ad.admin_id WHERE a.account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if ($alert_msg): ?>
                <div class="alert alert-<?php echo $alert_type; ?> shadow-sm"><?php echo htmlspecialchars($alert_msg); ?></div>
            <?php endif; ?>

            <!-- Password Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-3"><i class="fas fa-key text-warning me-2"></i>Security Settings</h4>
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Current Password</label>
                                <input type="password" name="old_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-warning fw-bold mt-3 w-100">Update Credentials</button>
                    </form>
                </div>
            </div>

            <!-- Profile Card -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-3"><i class="fas fa-user-shield text-danger me-2"></i>Admin Profile</h4>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($admin['full_name'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Department</label>
                            <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($admin['department'] ?? ''); ?>" required>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary fw-bold w-100">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>