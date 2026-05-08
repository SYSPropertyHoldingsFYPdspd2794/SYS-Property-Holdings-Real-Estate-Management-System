<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn);

$alert = '';
$allowed_roles = ['CUSTOMER', 'STAFF', 'ADMIN'];
$allowed_marital = ['SINGLE', 'MARRIED'];

function redirect_user_page($message)
{
    header("Location: user.php?msg=" . urlencode($message));
    exit();
}

function alert_box($type, $message)
{
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show shadow-sm" role="alert">'
        . htmlspecialchars($message)
        . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $transaction_started = false;

    try {
        if ($action === 'create') {
            $email = trim($_POST['email'] ?? '');
            $raw_password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? '';
            $name = trim($_POST['full_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $raw_password === '' || $name === '' || !in_array($role, $allowed_roles, true)) {
                throw new Exception('Invalid user details. Please check all required fields.');
            }

            $password = password_hash($raw_password, PASSWORD_DEFAULT);
            $conn->begin_transaction();
            $transaction_started = true;

            $stmt = $conn->prepare("INSERT INTO accounts (email, password_hash, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $password, $role);
            $stmt->execute();
            $new_id = $conn->insert_id;

            if ($role === 'CUSTOMER') {
                $marital = in_array($_POST['marital_status'] ?? '', $allowed_marital, true) ? $_POST['marital_status'] : 'SINGLE';
                $dependents = max(0, (int)($_POST['dependents_count'] ?? 0));
                $occupation = trim($_POST['occupation'] ?? '');
                $income = (float)($_POST['monthly_income'] ?? 0);

                $st = $conn->prepare("INSERT INTO customers (customer_id, full_name, phone_number, marital_status, dependents_count, occupation, monthly_income) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $st->bind_param("isssisd", $new_id, $name, $phone, $marital, $dependents, $occupation, $income);
                $st->execute();
            } elseif ($role === 'STAFF') {
                $state = trim($_POST['assigned_state'] ?? '');
                $st = $conn->prepare("INSERT INTO staff (staff_id, full_name, phone_number, assigned_state) VALUES (?, ?, ?, ?)");
                $st->bind_param("isss", $new_id, $name, $phone, $state);
                $st->execute();
            } else {
                $st = $conn->prepare("INSERT INTO admins (admin_id, full_name) VALUES (?, ?)");
                $st->bind_param("is", $new_id, $name);
                $st->execute();
            }

            $conn->commit();
            $transaction_started = false;
            redirect_user_page('created');
        }

        if ($action === 'update') {
            $account_id = (int)($_POST['account_id'] ?? 0);
            $role = $_POST['role'] ?? '';
            $email = trim($_POST['email'] ?? '');
            $new_password = $_POST['password'] ?? '';
            $name = trim($_POST['full_name'] ?? '');

            if ($account_id <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '' || !in_array($role, $allowed_roles, true)) {
                throw new Exception('Invalid update details. Please check the form again.');
            }

            $conn->begin_transaction();
            $transaction_started = true;

            if ($new_password !== '') {
                $password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE accounts SET email = ?, password_hash = ?, role = ? WHERE account_id = ?");
                $stmt->bind_param("sssi", $email, $password, $role, $account_id);
            } else {
                $stmt = $conn->prepare("UPDATE accounts SET email = ?, role = ? WHERE account_id = ?");
                $stmt->bind_param("ssi", $email, $role, $account_id);
            }
            $stmt->execute();

            if ($role === 'CUSTOMER') {
                $phone = trim($_POST['phone'] ?? '');
                $marital = in_array($_POST['marital_status'] ?? '', $allowed_marital, true) ? $_POST['marital_status'] : 'SINGLE';
                $dependents = max(0, (int)($_POST['dependents_count'] ?? 0));
                $occupation = trim($_POST['occupation'] ?? '');
                $income = (float)($_POST['monthly_income'] ?? 0);

                $st = $conn->prepare("INSERT INTO customers (customer_id, full_name, phone_number, marital_status, dependents_count, occupation, monthly_income) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), phone_number = VALUES(phone_number), marital_status = VALUES(marital_status), dependents_count = VALUES(dependents_count), occupation = VALUES(occupation), monthly_income = VALUES(monthly_income)");
                $st->bind_param("isssisd", $account_id, $name, $phone, $marital, $dependents, $occupation, $income);
                $st->execute();
            } elseif ($role === 'STAFF') {
                $phone = trim($_POST['phone'] ?? '');
                $state = trim($_POST['assigned_state'] ?? '');

                $st = $conn->prepare("INSERT INTO staff (staff_id, full_name, phone_number, assigned_state) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), phone_number = VALUES(phone_number), assigned_state = VALUES(assigned_state)");
                $st->bind_param("isss", $account_id, $name, $phone, $state);
                $st->execute();
            } else {
                $st = $conn->prepare("INSERT INTO admins (admin_id, full_name) VALUES (?, ?) ON DUPLICATE KEY UPDATE full_name = VALUES(full_name)");
                $st->bind_param("is", $account_id, $name);
                $st->execute();
            }

            $conn->commit();
            $transaction_started = false;
            redirect_user_page('updated');
        }
    } catch (Exception $e) {
        if ($transaction_started) {
            try {
                $conn->rollback();
            } catch (Exception $ignored) {
            }
        }
        $alert = alert_box('danger', $e->getMessage());
    }
}

$query = "
    SELECT
        a.account_id,
        a.email,
        a.role,
        CASE
            WHEN a.role = 'CUSTOMER' THEN c.full_name
            WHEN a.role = 'STAFF' THEN s.full_name
            ELSE ad.full_name
        END AS full_name,
        COALESCE(c.phone_number, s.phone_number, '') AS phone_number,
        c.marital_status,
        c.dependents_count,
        c.occupation,
        c.monthly_income,
        s.assigned_state,
        CASE
            WHEN a.role = 'CUSTOMER' THEN COALESCE(c.occupation, 'Customer')
            WHEN a.role = 'STAFF' THEN COALESCE(s.assigned_state, 'Not Assigned')
            ELSE 'HQ Administration'
        END AS detail
    FROM accounts a
    LEFT JOIN customers c ON a.account_id = c.customer_id
    LEFT JOIN staff s ON a.account_id = s.staff_id
    LEFT JOIN admins ad ON a.account_id = ad.admin_id
    ORDER BY a.account_id DESC
";
$users = $conn->query($query);
$edit_modals = '';

include '../includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container my-5">
    <?php echo $alert; ?>

    <?php if (isset($_GET['msg'])): ?>
        <?php
        $message = $_GET['msg'] === 'updated' ? 'User updated successfully.' : 'User registered successfully.';
        ?>
        <?php echo alert_box('success', $message); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-users-cog text-primary me-2"></i>User Management</h2>
        <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#registerModal">
            <i class="fas fa-user-plus me-2"></i>Add User
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="usersTable" class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Detail</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = $users->fetch_assoc()): ?>
                            <?php
                            $role = $u['role'];
                            $badge = 'secondary';
                            if ($role === 'ADMIN') $badge = 'danger';
                            if ($role === 'STAFF') $badge = 'info text-dark';
                            if ($role === 'CUSTOMER') $badge = 'success';
                            ?>
                            <tr>
                                <td>#<?php echo (int)$u['account_id']; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($u['full_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><span class="badge bg-<?php echo $badge; ?>"><?php echo htmlspecialchars($role); ?></span></td>
                                <td><?php echo htmlspecialchars($u['detail'] ?? 'N/A'); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-dark fw-bold" data-bs-toggle="modal" data-bs-target="#editModal<?php echo (int)$u['account_id']; ?>">
                                        Edit
                                    </button>
                                </td>
                            </tr>

                            <?php ob_start(); ?>
                            <div class="modal fade" id="editModal<?php echo (int)$u['account_id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-dark text-white">
                                            <h5 class="modal-title fw-bold">Edit <?php echo htmlspecialchars($role); ?> Account</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body p-4">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="account_id" value="<?php echo (int)$u['account_id']; ?>">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Full Name</label>
                                                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($u['full_name'] ?? ''); ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Email Address</label>
                                                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($u['email']); ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">New Password</label>
                                                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Role</label>
                                                        <select name="role" class="form-select" required>
                                                            <option value="CUSTOMER" <?php echo ($role === 'CUSTOMER') ? 'selected' : ''; ?>>Customer</option>
                                                            <option value="STAFF" <?php echo ($role === 'STAFF') ? 'selected' : ''; ?>>Staff</option>
                                                            <option value="ADMIN" <?php echo ($role === 'ADMIN') ? 'selected' : ''; ?>>Administrator</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-12">
                                                        <hr class="my-2">
                                                        <h6 class="fw-bold text-muted mb-0">Customer Fields</h6>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Phone Number</label>
                                                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($u['phone_number'] ?? ''); ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Marital Status</label>
                                                        <select name="marital_status" class="form-select">
                                                            <option value="SINGLE" <?php echo ($u['marital_status'] === 'SINGLE') ? 'selected' : ''; ?>>Single</option>
                                                            <option value="MARRIED" <?php echo ($u['marital_status'] === 'MARRIED') ? 'selected' : ''; ?>>Married</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold">Dependents</label>
                                                        <input type="number" name="dependents_count" class="form-control" min="0" value="<?php echo htmlspecialchars($u['dependents_count'] ?? 0); ?>">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold">Occupation</label>
                                                        <input type="text" name="occupation" class="form-control" value="<?php echo htmlspecialchars($u['occupation'] ?? ''); ?>">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold">Monthly Income (RM)</label>
                                                        <input type="number" step="0.01" name="monthly_income" class="form-control" value="<?php echo htmlspecialchars($u['monthly_income'] ?? 0); ?>">
                                                    </div>

                                                    <div class="col-12">
                                                        <hr class="my-2">
                                                        <h6 class="fw-bold text-muted mb-0">Staff Fields</h6>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Assigned State</label>
                                                        <input type="text" name="assigned_state" class="form-control" value="<?php echo htmlspecialchars($u['assigned_state'] ?? ''); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary fw-bold px-4">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php $edit_modals .= ob_get_clean(); ?>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php echo $edit_modals; ?>

<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">New User Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="create">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">System Role</label>
                            <select name="role" id="roleSelect" class="form-select" required onchange="toggleCreateFields()">
                                <option value="CUSTOMER">Customer</option>
                                <option value="STAFF">Staff</option>
                                <option value="ADMIN">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6 role-field customer-field staff-field">
                            <label class="form-label fw-bold">Phone Number</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6 role-field staff-field">
                            <label class="form-label fw-bold">Assigned State</label>
                            <input type="text" name="assigned_state" class="form-control">
                        </div>
                        <div class="col-md-6 role-field customer-field">
                            <label class="form-label fw-bold">Marital Status</label>
                            <select name="marital_status" class="form-select">
                                <option value="SINGLE">Single</option>
                                <option value="MARRIED">Married</option>
                            </select>
                        </div>
                        <div class="col-md-4 role-field customer-field">
                            <label class="form-label fw-bold">Dependents</label>
                            <input type="number" name="dependents_count" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-4 role-field customer-field">
                            <label class="form-label fw-bold">Occupation</label>
                            <input type="text" name="occupation" class="form-control">
                        </div>
                        <div class="col-md-4 role-field customer-field">
                            <label class="form-label fw-bold">Monthly Income (RM)</label>
                            <input type="number" step="0.01" name="monthly_income" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Register User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            order: [[0, 'desc']]
        });
        toggleCreateFields();
    });

    function toggleCreateFields() {
        const role = document.getElementById('roleSelect').value;
        document.querySelectorAll('.role-field').forEach((field) => {
            field.classList.add('d-none');
        });
        if (role === 'CUSTOMER') {
            document.querySelectorAll('.customer-field').forEach((field) => field.classList.remove('d-none'));
        }
        if (role === 'STAFF') {
            document.querySelectorAll('.staff-field').forEach((field) => field.classList.remove('d-none'));
        }
    }
</script>

<?php include '../includes/footer.php'; ?>
