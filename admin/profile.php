<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';
include '../includes/functions.php';
include '../includes/email_change_helper.php';

protect_admin_page('ADMIN', $conn); 

$account_id = $_SESSION['account_id'];
$alert_msg = ''; $alert_type = '';
$show_email_change_otp_modal = false;

function admin_profile_name($conn, $account_id) {
    $stmt = $conn->prepare("SELECT full_name FROM admins WHERE admin_id = ? LIMIT 1");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row['full_name'] ?? 'Admin';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_change_action = $_POST['email_change_action'] ?? '';

    if ($email_change_action === 'verify_otp') {
        $email_change_error = '';
        $email_change_success = '';
        email_change_verify_otp($conn, $account_id, trim($_POST['email_change_otp'] ?? ''), $email_change_error, $email_change_success, $show_email_change_otp_modal);
        $alert_msg = $email_change_success !== '' ? $email_change_success : $email_change_error;
        $alert_type = $email_change_success !== '' ? 'success' : 'danger';
    } elseif ($email_change_action === 'resend_otp') {
        $email_change_error = '';
        $email_change_success = '';
        email_change_resend_otp($conn, $account_id, admin_profile_name($conn, $account_id), $email_change_error, $email_change_success, $show_email_change_otp_modal);
        $alert_msg = $email_change_success !== '' ? $email_change_success : $email_change_error;
        $alert_type = $email_change_success !== '' ? 'success' : 'danger';
    } elseif (isset($_POST['update_profile'])) {
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $can_update_profile = true;
        $email_change_error = '';
        $email_change_success = '';

        if (!email_change_start_request($conn, $account_id, $email, $full_name, $email_change_error, $email_change_success, $show_email_change_otp_modal)) {
            $alert_msg = $email_change_error;
            $alert_type = 'danger';
            $can_update_profile = false;
        }

        if ($can_update_profile) {
            $upd_adm = $conn->prepare("UPDATE admins SET full_name = ? WHERE admin_id = ?");
            $upd_adm->bind_param("si", $full_name, $account_id);
            
            if ($upd_adm->execute()) {
                $alert_msg = 'Profile updated successfully.';
                if ($email_change_success !== '') {
                    $alert_msg .= ' ' . $email_change_success;
                }
                $alert_type = 'success';
            }
        }
    }
}

$stmt = $conn->prepare("SELECT ad.full_name, a.email FROM accounts a LEFT JOIN admins ad ON a.account_id = ad.admin_id WHERE a.account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$pending_email_change = email_change_pending_notice($conn, $account_id);

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
    <?php if ($pending_email_change): ?>
        <div class="alert alert-warning shadow-sm">
            Pending email change from <?php echo htmlspecialchars($pending_email_change['old_email']); ?> to <?php echo htmlspecialchars($pending_email_change['new_email']); ?>.
            Old email approval: <?php echo empty($pending_email_change['old_approved_at']) ? 'waiting' : 'approved'; ?>,
            new email OTP: <?php echo empty($pending_email_change['new_verified_at']) ? 'waiting' : 'verified'; ?>.
        </div>
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
                    <form method="POST" data-email-change-form="1">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" data-original-email="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" required>
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
<?php include '../includes/email_change_modal.php'; ?>
<?php include '../includes/footer.php'; ?>
