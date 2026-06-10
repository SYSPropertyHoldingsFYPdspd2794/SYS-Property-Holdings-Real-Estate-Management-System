<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';

protect_customer_page('CUSTOMER', $conn);

$account_id = $_SESSION['account_id'];
$alert_msg = '';
$alert_type = '';

function customer_storage_path($public_path) {
    if (!$public_path || strpos($public_path, '/storage/') !== 0) {
        return null;
    }

    $root = realpath(__DIR__ . '/..');
    if (!$root) {
        return null;
    }

    $full_path = $root . str_replace('/', DIRECTORY_SEPARATOR, $public_path);
    $storage_root = realpath($root . DIRECTORY_SEPARATOR . 'storage');
    $target_dir = realpath(dirname($full_path));

    if (!$storage_root || !$target_dir || strpos($target_dir, $storage_root) !== 0) {
        return null;
    }

    return $full_path;
}

function destroy_customer_session() {
    $_SESSION = array();

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $password = $_POST['password'] ?? '';

    if ($password === '') {
        $alert_msg = 'Please enter your current password.';
        $alert_type = 'danger';
    } else {
        $stmt = $conn->prepare("SELECT password_hash FROM accounts WHERE account_id = ? AND role = 'CUSTOMER'");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $account = $stmt->get_result()->fetch_assoc();

        if (!$account || !password_verify($password, $account['password_hash'])) {
            $alert_msg = 'Password is incorrect.';
            $alert_type = 'danger';
        } else {
            $files_to_delete = array();

            $profile_stmt = $conn->prepare("SELECT profile_image FROM customers WHERE customer_id = ?");
            $profile_stmt->bind_param("i", $account_id);
            $profile_stmt->execute();
            $profile = $profile_stmt->get_result()->fetch_assoc();
            if (!empty($profile['profile_image'])) {
                $files_to_delete[] = $profile['profile_image'];
            }

            $doc_stmt = $conn->prepare("SELECT file_path FROM documents WHERE customer_id = ?");
            $doc_stmt->bind_param("i", $account_id);
            $doc_stmt->execute();
            $docs = $doc_stmt->get_result();
            while ($doc = $docs->fetch_assoc()) {
                if (!empty($doc['file_path'])) {
                    $files_to_delete[] = $doc['file_path'];
                }
            }

            $conn->begin_transaction();
            try {
                $delete_stmt = $conn->prepare("DELETE FROM accounts WHERE account_id = ? AND role = 'CUSTOMER'");
                $delete_stmt->bind_param("i", $account_id);
                $delete_stmt->execute();

                if ($delete_stmt->affected_rows !== 1) {
                    throw new Exception('Account deletion failed.');
                }

                $conn->commit();

                foreach (array_unique($files_to_delete) as $public_path) {
                    $file_path = customer_storage_path($public_path);
                    if ($file_path && is_file($file_path)) {
                        unlink($file_path);
                    }
                }

                destroy_customer_session();
                header('Location: ../index.php');
                exit();
            } catch (Throwable $e) {
                $conn->rollback();
                $alert_msg = 'Unable to delete account right now. Please try again.';
                $alert_type = 'danger';
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex align-items-center mb-4">
        <img src="../SYS Property Catalog/SYS_Property_Holdings_Icon.jpeg" alt="SYS Property Holdings" style="max-height: 50px; margin-right: 15px; border-radius: 6px;">
        <div>
            <h2 class="fw-bold text-white mb-1">Setting</h2>
            <p class="text-light opacity-75 mb-0">Manage your account profile and security preferences.</p>
        </div>
    </div>

    <?php if ($alert_msg): ?>
        <div class="alert alert-<?php echo $alert_type; ?> shadow-sm"><?php echo htmlspecialchars($alert_msg); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-3 col-md-4">
            <div class="profile-settings-nav shadow-sm">
                <a href="profile.php" class="profile-settings-link">
                    <i class="fas fa-user me-2"></i>Profile
                </a>
                <a href="change_password.php" class="profile-settings-link">
                    <i class="fas fa-lock me-2"></i>Change Password
                </a>
                <a href="privacy_data.php" class="profile-settings-link active danger">
                    <i class="fas fa-shield-alt me-2"></i>Privacy & Data
                </a>
            </div>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4 border-bottom pb-2">Privacy & Data</h4>

                    <div class="p-4 rounded border bg-light mb-4">
                        <h5 class="fw-bold text-danger mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Delete Account</h5>
                        <p class="text-muted mb-0">
                            Deleting your account will permanently remove your customer profile, wishlist, appointments, affordable housing applications, uploaded documents, and profile avatar.
                        </p>
                    </div>

                    <form method="POST" id="deleteAccountForm" class="delete-account-form">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Current Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="button" class="btn btn-danger fw-bold px-4" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            <i class="fas fa-trash-alt me-2"></i>Delete My Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" id="deleteAccountModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2 fw-bold text-dark">Are you sure you want to permanently delete your account?</p>
                <p class="text-muted mb-0">This will remove your profile, wishlist, appointments, applications, uploaded documents, and profile avatar.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="delete_account" form="deleteAccountForm" class="btn btn-danger fw-bold">
                    Confirm Delete
                </button>
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
    .profile-settings-link.danger.active {
        color: #dc3545;
        background: #fff5f5;
        border-left-color: #dc3545;
    }
    .delete-account-form {
        max-width: 560px;
    }
</style>

<?php include '../includes/footer.php'; ?>
