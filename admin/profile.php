<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn); 

$account_id = $_SESSION['account_id'];

$stmt = $conn->prepare("SELECT ad.full_name, a.email FROM accounts a LEFT JOIN admins ad ON a.account_id = ad.admin_id WHERE a.account_id = ?");
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
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($admin['full_name'] ?? 'N/A'); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Role</label>
                            <input type="text" class="form-control bg-light" value="ADMIN" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Department</label>
                            <input type="text" class="form-control bg-light" value="HQ Administration" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
