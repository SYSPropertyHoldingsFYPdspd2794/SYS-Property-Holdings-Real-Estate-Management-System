<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';
include '../includes/functions.php';

protect_staff_page('STAFF', $conn);

$account_id = $_SESSION['account_id']; 
$alert_msg = '';
$profile_image_ready = ensure_profile_image_column($conn, 'staff');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_password'])) {
        $old_pass = $_POST['old_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        if (!validate_password_strength($new_pass)) {
            $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">Password does not meet requirements.</div>';
        } elseif ($new_pass !== $confirm_pass) {
            $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">Passwords do not match.</div>';
        } else {
            $stmt_check = $conn->prepare("SELECT password_hash FROM accounts WHERE account_id = ?");
            $stmt_check->bind_param("i", $account_id);
            $stmt_check->execute();
            $res = $stmt_check->get_result()->fetch_assoc();

            if ($res && password_verify($old_pass, $res['password_hash'])) {
                $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
                $upd = $conn->prepare("UPDATE accounts SET password_hash = ? WHERE account_id = ?");
                $upd->bind_param("si", $hashed, $account_id);
                $upd->execute();
                $alert_msg = '<div class="alert alert-success fw-bold shadow-sm">Password updated successfully!</div>';
            } else {
                $alert_msg = '<div class="alert alert-danger fw-bold shadow-sm">Current password incorrect.</div>';
            }
        }
    }

    if (isset($_POST['update_staff'])) {
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone_number']);
        $upload_error = '';
        $profile_image = null;
        $can_update_staff = true;

        if (!$profile_image_ready) {
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
            $upd_acc = $conn->prepare("UPDATE accounts SET email = ? WHERE account_id = ?");
            $upd_acc->bind_param("si", $email, $account_id);
            $upd_acc->execute();

            if ($profile_image !== null) {
                $stmt_upd = $conn->prepare("UPDATE staff SET full_name = ?, phone_number = ?, profile_image = ? WHERE staff_id = ?");
                $stmt_upd->bind_param("sssi", $full_name, $phone, $profile_image, $account_id);
            } else {
                $stmt_upd = $conn->prepare("UPDATE staff SET full_name = ?, phone_number = ? WHERE staff_id = ?");
                $stmt_upd->bind_param("ssi", $full_name, $phone, $account_id);
            }
        
            if ($stmt_upd->execute()) {
                $alert_msg = '<div class="alert alert-success fw-bold shadow-sm">' . ($profile_image !== null ? 'Staff record and avatar updated successfully!' : 'Staff record updated successfully!') . '</div>';
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
                            <input type="password" name="new_password" class="form-control" required onkeyup="checkPasswordRealtime(this.value, 'stf_')">
                            
                            <div class="mt-3 p-3 border rounded bg-light" style="font-size: 0.85rem;">
                                <ul class="list-unstyled mb-0">
                                    <li id="stf_length" class="text-danger"><i class="fas fa-times-circle me-2"></i>8+ Characters</li>
                                    <li id="stf_upper" class="text-danger"><i class="fas fa-times-circle me-2"></i>Uppercase (A-Z)</li>
                                    <li id="stf_lower" class="text-danger"><i class="fas fa-times-circle me-2"></i>Lowercase (a-z)</li>
                                    <li id="stf_number" class="text-danger"><i class="fas fa-times-circle me-2"></i>Number (0-9)</li>
                                    <li id="stf_symbol" class="text-danger"><i class="fas fa-times-circle me-2"></i>Symbol (@#$!)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-warning w-100 fw-bold">Update Password</button>
                        <div class="text-center mt-3">
                            <a href="https://wa.link/bzspzh" target="_blank" rel="noopener noreferrer" class="small fw-bold text-decoration-none">
                                Forgot Password?
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4 border-bottom pb-2">Staff Information</h4>
                    <form method="POST" enctype="multipart/form-data">
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
                                <div class="small text-muted">Click the avatar icon to upload JPG, PNG, or WebP. Max size: 2MB.</div>
                                <div id="staffProfileImageName" class="small fw-bold text-primary mt-2"></div>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
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
<?php include '../includes/footer.php'; ?>
