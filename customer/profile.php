<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';
include '../includes/functions.php';

protect_customer_page('CUSTOMER', $conn);

$account_id = $_SESSION['account_id'];
$alert_msg = ''; $alert_type = '';
$profile_image_ready = ensure_profile_image_column($conn, 'customers');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_password'])) {
        $old_pass = $_POST['old_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        if (!validate_password_strength($new_pass)) {
            $alert_msg = 'Password must follow all security rules.';
            $alert_type = 'danger';
        } elseif ($new_pass !== $confirm_pass) {
            $alert_msg = 'Passwords do not match.';
            $alert_type = 'danger';
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
                $alert_msg = 'Password successfully updated.';
                $alert_type = 'success';
            } else {
                $alert_msg = 'Current password incorrect.';
                $alert_type = 'danger';
            }
        }
    }

    if (isset($_POST['save_details'])) {
        $email = trim($_POST['email'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone_number'] ?? '');
        $marital = $_POST['marital_status'] ?? 'SINGLE';
        $dependents = max(0, intval($_POST['dependents_count'] ?? 0));
        $occupation = trim($_POST['occupation'] ?? '');
        $income = max(0, floatval($_POST['monthly_income'] ?? 0));
        $profile_image = null;
        $can_save_details = true;

        if (!$profile_image_ready) {
            $alert_msg = 'Profile image column is not ready. Please check database permissions.';
            $alert_type = 'danger';
            $can_save_details = false;
        } else {
            $profile_image = upload_profile_image($_FILES['profile_image'] ?? null, 'customer', $account_id, $alert_msg);
            if ($profile_image === false) {
                $alert_type = 'danger';
                $can_save_details = false;
            }
        }

        if ($can_save_details) {
            $upd_acc = $conn->prepare("UPDATE accounts SET email = ? WHERE account_id = ?");
            $upd_acc->bind_param("si", $email, $account_id);
            $upd_acc->execute();

            if ($profile_image !== null) {
                $stmt_upd = $conn->prepare("UPDATE customers SET full_name = ?, phone_number = ?, marital_status = ?, dependents_count = ?, occupation = ?, monthly_income = ?, profile_image = ? WHERE customer_id = ?");
                $stmt_upd->bind_param("sssisdsi", $full_name, $phone, $marital, $dependents, $occupation, $income, $profile_image, $account_id);
            } else {
                $stmt_upd = $conn->prepare("UPDATE customers SET full_name = ?, phone_number = ?, marital_status = ?, dependents_count = ?, occupation = ?, monthly_income = ? WHERE customer_id = ?");
                $stmt_upd->bind_param("sssisdi", $full_name, $phone, $marital, $dependents, $occupation, $income, $account_id);
            }

            if ($stmt_upd->execute()) {
                $alert_msg = $profile_image !== null ? 'Profile details and avatar updated successfully.' : 'Profile details updated successfully.';
                $alert_type = 'success';
                $_SESSION['user_email'] = $email;
            }
        }
    }
}

$profile_image_select = $profile_image_ready ? 'c.profile_image' : 'NULL AS profile_image';
$stmt = $conn->prepare("SELECT c.customer_id, c.full_name, c.phone_number, c.marital_status, c.dependents_count, c.occupation, c.monthly_income, $profile_image_select, a.email FROM accounts a LEFT JOIN customers c ON a.account_id = c.customer_id WHERE a.account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$avatar_src = !empty($user['profile_image']) ? '..' . $user['profile_image'] : '';
$avatar_initial = strtoupper(substr($user['full_name'] ?? 'C', 0, 1));

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
                    <h4 class="fw-bold mb-4">Security</h4>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Current Password</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">New Password</label>
                            <input type="password" name="new_password" class="form-control" required onkeyup="checkPasswordRealtime(this.value, 'cus_')">
                            
                            <div class="mt-3 p-3 border rounded bg-light" style="font-size: 0.85rem;">
                                <ul class="list-unstyled mb-0">
                                    <li id="cus_length" class="text-danger"><i class="fas fa-times-circle me-2"></i>8+ Characters</li>
                                    <li id="cus_upper" class="text-danger"><i class="fas fa-times-circle me-2"></i>Uppercase (A-Z)</li>
                                    <li id="cus_lower" class="text-danger"><i class="fas fa-times-circle me-2"></i>Lowercase (a-z)</li>
                                    <li id="cus_number" class="text-danger"><i class="fas fa-times-circle me-2"></i>Number (0-9)</li>
                                    <li id="cus_symbol" class="text-danger"><i class="fas fa-times-circle me-2"></i>Symbol (@#$!)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-dark w-100 fw-bold">Update Password</button>
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
                    <h4 class="fw-bold mb-4 border-bottom pb-2">Customer Information</h4>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="d-flex align-items-center gap-4 mb-4 pb-4 border-bottom">
                            <label for="customerProfileImage" class="avatar-upload-wrap position-relative flex-shrink-0" title="Upload profile avatar">
                                <?php if ($avatar_src !== ''): ?>
                                    <img src="<?php echo htmlspecialchars($avatar_src); ?>" alt="Profile avatar" class="profile-avatar rounded-circle border shadow-sm object-fit-cover">
                                <?php else: ?>
                                    <span class="profile-avatar rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm">
                                        <?php echo htmlspecialchars($avatar_initial); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="avatar-upload-icon rounded-circle bg-dark text-white d-flex align-items-center justify-content-center shadow-sm">
                                    <i class="fas fa-camera"></i>
                                </span>
                            </label>
                            <div class="flex-grow-1">
                                <label class="form-label fw-bold">Profile Avatar</label>
                                <input type="file" id="customerProfileImage" name="profile_image" class="visually-hidden" accept="image/jpeg,image/png,image/webp">
                                <div id="customerProfileImageName" class="small fw-bold text-primary mt-2"></div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone</label>
                                <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Marital Status</label>
                                <select name="marital_status" class="form-select" required>
                                    <option value="SINGLE" <?php echo (isset($user['marital_status']) && $user['marital_status'] === 'SINGLE') ? 'selected' : ''; ?>>Single</option>
                                    <option value="MARRIED" <?php echo (isset($user['marital_status']) && $user['marital_status'] === 'MARRIED') ? 'selected' : ''; ?>>Married</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Occupation</label>
                                <input type="text" name="occupation" class="form-control" value="<?php echo htmlspecialchars($user['occupation'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Dependents</label>
                                <input type="number" name="dependents_count" class="form-control" min="0" step="1" value="<?php echo htmlspecialchars($user['dependents_count'] ?? 0); ?>" required>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Monthly Income (RM)</label>
                                <input type="number" name="monthly_income" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars($user['monthly_income'] ?? 0); ?>" required>
                            </div>
                        </div>
                        <button type="submit" name="save_details" class="btn btn-primary fw-bold px-4">Save Details</button>
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
const customerProfileImage = document.getElementById('customerProfileImage');
const customerProfileImageName = document.getElementById('customerProfileImageName');
if (customerProfileImage && customerProfileImageName) {
    customerProfileImage.addEventListener('change', function () {
        customerProfileImageName.textContent = this.files.length ? this.files[0].name : '';
    });
}
</script>
<?php include '../includes/footer.php'; ?>
