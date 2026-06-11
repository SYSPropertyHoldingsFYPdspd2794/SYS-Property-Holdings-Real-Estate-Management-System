<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';
include '../includes/functions.php';
include '../includes/email_change_helper.php';

protect_staff_page('STAFF', $conn);

$account_id = $_SESSION['account_id']; 
$alert_msg = '';
$profile_image_ready = ensure_profile_image_column($conn, 'staff');
$show_email_change_otp_modal = false;

function staff_profile_name($conn, $account_id) {
    $stmt = $conn->prepare("SELECT full_name FROM staff WHERE staff_id = ? LIMIT 1");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row['full_name'] ?? 'Staff';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_change_action = $_POST['email_change_action'] ?? '';

    if ($email_change_action === 'verify_otp') {
        $email_change_error = '';
        $email_change_success = '';
        email_change_verify_otp($conn, $account_id, trim($_POST['email_change_otp'] ?? ''), $email_change_error, $email_change_success, $show_email_change_otp_modal);
        $alert_class = $email_change_success !== '' ? 'success' : 'danger';
        $alert_msg = '<div class="alert alert-' . $alert_class . ' fw-bold shadow-sm">' . htmlspecialchars($email_change_success !== '' ? $email_change_success : $email_change_error) . '</div>';
    } elseif ($email_change_action === 'resend_otp') {
        $email_change_error = '';
        $email_change_success = '';
        email_change_resend_otp($conn, $account_id, staff_profile_name($conn, $account_id), $email_change_error, $email_change_success, $show_email_change_otp_modal);
        $alert_class = $email_change_success !== '' ? 'success' : 'danger';
        $alert_msg = '<div class="alert alert-' . $alert_class . ' fw-bold shadow-sm">' . htmlspecialchars($email_change_success !== '' ? $email_change_success : $email_change_error) . '</div>';
    } elseif (isset($_POST['update_staff'])) {
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone_number']);
        $upload_error = '';
        $profile_image = null;
        $can_update_staff = true;

        if (preg_match('/^\d{10,11}$/', $phone) !== 1) {
            $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">Phone number must be a valid mobile number with 10 or 11 digits.</div>';
            $can_update_staff = false;
        } else if (!$profile_image_ready) {
            $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">Profile image column is not ready. Please check database permissions.</div>';
            $can_update_staff = false;
        } else {
            $profile_image = upload_profile_image($_FILES['profile_image'] ?? null, 'staff', $account_id, $upload_error);
            if ($profile_image === false) {
                $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">' . htmlspecialchars($upload_error) . '</div>';
                $can_update_staff = false;
            }
        }
        
        if ($can_update_staff) {
            $email_change_error = '';
            $email_change_success = '';

            if (!email_change_start_request($conn, $account_id, $email, $full_name, $email_change_error, $email_change_success, $show_email_change_otp_modal)) {
                $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">' . htmlspecialchars($email_change_error) . '</div>';
                $can_update_staff = false;
            }
        }

        if ($can_update_staff) {
            if ($profile_image !== null) {
                $stmt_upd = $conn->prepare("UPDATE staff SET full_name = ?, phone_number = ?, profile_image = ? WHERE staff_id = ?");
                $stmt_upd->bind_param("sssi", $full_name, $phone, $profile_image, $account_id);
            } else {
                $stmt_upd = $conn->prepare("UPDATE staff SET full_name = ?, phone_number = ? WHERE staff_id = ?");
                $stmt_upd->bind_param("ssi", $full_name, $phone, $account_id);
            }
        
            if ($stmt_upd->execute()) {
                $message = $profile_image !== null ? 'Staff record and avatar updated successfully!' : 'Staff record updated successfully!';
                if ($email_change_success !== '') {
                    $message .= ' ' . $email_change_success;
                }
                $alert_msg = '<div class="alert alert-success fw-bold shadow-sm">' . htmlspecialchars($message) . '</div>';
            }
        }
    }
}

$profile_image_select = $profile_image_ready ? 's.profile_image' : 'NULL AS profile_image';
$stmt = $conn->prepare("SELECT a.email, s.full_name, s.assigned_state, s.phone_number, $profile_image_select FROM accounts a LEFT JOIN staff s ON a.account_id = s.staff_id WHERE a.account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$avatar_src = !empty($user['profile_image']) ? '..' . $user['profile_image'] : '';
$avatar_initial = strtoupper(substr($user['full_name'] ?? 'S', 0, 1));
$pending_email_change = email_change_pending_notice($conn, $account_id);

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="mb-4">
        <h2 class="fw-bold text-white mb-1">Setting</h2>
        <p class="text-light opacity-75 mb-0">Manage your staff profile and security preferences.</p>
    </div>

    <?php echo $alert_msg; ?>
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
                    <i class="fas fa-id-badge me-2"></i>Profile
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
                    <form method="POST" enctype="multipart/form-data" data-email-change-form="1">
                        <div class="d-flex align-items-center gap-4 mb-4 pb-4 border-bottom">
                            <label for="staffProfileImage" class="avatar-upload-wrap position-relative flex-shrink-0" title="Upload profile avatar">
                                <?php if ($avatar_src !== ''): ?>
                                    <img src="<?php echo htmlspecialchars($avatar_src); ?>" alt="Profile avatar" class="profile-avatar rounded-circle border shadow-sm object-fit-cover">
                                <?php else: ?>
                                    <span class="profile-avatar rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold shadow-sm">
                                        <?php echo htmlspecialchars($avatar_initial); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="avatar-upload-icon rounded-circle bg-dark text-white d-flex align-items-center justify-content-center shadow-sm">
                                    <i class="fas fa-camera"></i>
                                </span>
                            </label>
                            <div class="flex-grow-1">
                                <label class="form-label fw-bold">Profile Avatar</label>
                                <input type="file" id="staffProfileImage" name="profile_image" class="visually-hidden" accept="image/jpeg,image/png,image/webp">
                                <div id="staffProfileImageName" class="small fw-bold text-primary mt-2"></div>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" data-original-email="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
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
<style>
    .profile-avatar {
        width: 104px;
        height: 104px;
        font-size: 2.4rem;
    }
    .avatar-upload-wrap {
        cursor: pointer;
        border-radius: 50%;
    }
    .avatar-upload-wrap:hover .profile-avatar {
        filter: brightness(0.88);
    }
    .avatar-upload-icon {
        position: absolute;
        right: 0;
        bottom: 4px;
        width: 34px;
        height: 34px;
        border: 3px solid #fff;
        font-size: 0.9rem;
    }
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
<script>
const staffProfileImage = document.getElementById('staffProfileImage');
const staffProfileImageName = document.getElementById('staffProfileImageName');
if (staffProfileImage && staffProfileImageName) {
    staffProfileImage.addEventListener('change', function () {
        staffProfileImageName.textContent = this.files.length ? this.files[0].name : '';
    });
}
</script>
<?php include '../includes/email_change_modal.php'; ?>
<?php include '../includes/footer.php'; ?>
