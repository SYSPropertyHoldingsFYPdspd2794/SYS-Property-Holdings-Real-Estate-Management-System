<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';
include '../includes/functions.php';

protect_admin_page('ADMIN', $conn); 

$account_id = $_SESSION['account_id'];
$alert_msg = ''; $alert_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    <div class="mb-4">
        <h2 class="fw-bold text-white mb-1">Setting</h2>
        <p class="text-light opacity-75 mb-0">Manage your admin profile and security preferences.</p>
    </div>

    <?php if ($alert_msg): ?>
        <div class="alert alert-<?php echo $alert_type; ?> shadow-sm"><?php echo htmlspecialchars($alert_msg); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-3 col-md-4">
            <div class="profile-settings-nav shadow-sm">
                <a href="profile.php" class="profile-settings-link active">
                    <i class="fas fa-user-shield me-2"></i>Profile
                </a>
                <a href="change_password.php" class="profile-settings-link">
                    <i class="fas fa-lock me-2"></i>Change Password
                </a>
            </div>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4 border-bottom pb-2">Profile</h4>
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
</style>
<?php include '../includes/footer.php'; ?>
