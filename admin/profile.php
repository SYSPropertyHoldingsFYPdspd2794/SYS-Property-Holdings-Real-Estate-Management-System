<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn); 

$account_id = $_SESSION['account_id'];

$alert_msg = '';
$alert_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $department = trim($_POST['department'] ?? '');

    if ($full_name === '' || $department === '') {
        $alert_msg = 'Full name and department are required.';
        $alert_type = 'danger';
    } else {
        $update_stmt = $conn->prepare("INSERT INTO admins (admin_id, full_name, department) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), department = VALUES(department)");
        $update_stmt->bind_param("iss", $account_id, $full_name, $department);

        if ($update_stmt->execute()) {
            $alert_msg = 'Profile successfully updated.';
            $alert_type = 'success';
        } else {
            $alert_msg = 'Error updating profile: ' . $conn->error;
            $alert_type = 'danger';
        }

        $update_stmt->close();
    }
}

$stmt = $conn->prepare("SELECT ad.full_name, ad.department, a.email FROM accounts a LEFT JOIN admins ad ON a.account_id = ad.admin_id WHERE a.account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <h3 class="fw-bold mb-4"><i class="fas fa-user-shield text-danger me-2"></i>Admin Profile</h3>

                    <?php if ($alert_msg !== ''): ?>
                        <div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show shadow-sm" role="alert">
                            <i class="fas <?php echo ($alert_type === 'success') ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
                            <?php echo htmlspecialchars($alert_msg); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" name="full_name" class="form-control border-primary" value="<?php echo htmlspecialchars($admin['full_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Role</label>
                                <input type="text" class="form-control bg-light" value="ADMIN" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Department</label>
                                <input type="text" name="department" class="form-control border-primary" value="<?php echo htmlspecialchars($admin['department'] ?? 'HQ Administration'); ?>" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold px-5 shadow-sm">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
