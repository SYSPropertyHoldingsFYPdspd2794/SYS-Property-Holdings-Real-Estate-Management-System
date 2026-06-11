<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn);

$alert = '';
$allowed_roles = ['CUSTOMER', 'STAFF', 'ADMIN'];
$allowed_marital = ['SINGLE', 'MARRIED'];
$allowed_states = [
    'Johor',
    'Kedah',
    'Kelantan',
    'Kuala Lumpur',
    'Labuan',
    'Melaka',
    'Negeri Sembilan',
    'Pahang',
    'Penang',
    'Perak',
    'Perlis',
    'Putrajaya',
    'Sabah',
    'Sarawak',
    'Selangor',
    'Terengganu',
];

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

function table_column_exists($conn, $table, $column)
{
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->bind_param("ss", $table, $column);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return ((int)($row['total'] ?? 0)) > 0;
    } catch (Throwable $e) {
        return false;
    }
}

function table_exists($conn, $table)
{
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $stmt->bind_param("s", $table);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return ((int)($row['total'] ?? 0)) > 0;
    } catch (Throwable $e) {
        return false;
    }
}

function render_state_options($selected_state, $allowed_states)
{
    $html = '<option value="">Select assigned state</option>';
    foreach ($allowed_states as $state) {
        $selected = ($selected_state === $state) ? ' selected' : '';
        $html .= '<option value="' . htmlspecialchars($state) . '"' . $selected . '>' . htmlspecialchars($state) . '</option>';
    }
    return $html;
}

function execute_account_delete($conn, $account_id)
{
    if (table_exists($conn, 'audit_logs')) {
        $stmt = $conn->prepare("UPDATE audit_logs SET account_id = NULL WHERE account_id = ?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
    }

    if (table_exists($conn, 'affordable_housing_applications')) {
        $stmt = $conn->prepare("UPDATE affordable_housing_applications SET reviewed_by_staff_id = NULL WHERE reviewed_by_staff_id = ?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM affordable_housing_applications WHERE customer_id = ?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
    }

    if (table_exists($conn, 'appointments')) {
        $stmt = $conn->prepare("UPDATE appointments SET assigned_staff_id = NULL WHERE assigned_staff_id = ?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM appointments WHERE customer_id = ?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
    }

    if (table_exists($conn, 'documents')) {
        $stmt = $conn->prepare("DELETE FROM documents WHERE customer_id = ?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
    }

    if (table_exists($conn, 'wishlists')) {
        $stmt = $conn->prepare("DELETE FROM wishlists WHERE customer_id = ?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
    }

    foreach (['admins' => 'admin_id', 'staff' => 'staff_id', 'customers' => 'customer_id'] as $table => $id_column) {
        if (table_exists($conn, $table)) {
            $stmt = $conn->prepare("DELETE FROM $table WHERE $id_column = ?");
            $stmt->bind_param("i", $account_id);
            $stmt->execute();
        }
    }

    $stmt = $conn->prepare("DELETE FROM accounts WHERE account_id = ?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();

    if ($stmt->affected_rows < 1) {
        throw new Exception('Account was not found or has already been removed.');
    }
}

$admin_has_department = table_column_exists($conn, 'admins', 'department');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $transaction_started = false;

    try {
        if ($action === 'delete') {
            $account_id = (int)($_POST['account_id'] ?? 0);

            if ($account_id <= 0) {
                throw new Exception('Invalid account selected for removal.');
            }

            if ($account_id === (int)($_SESSION['account_id'] ?? 0)) {
                throw new Exception('You cannot remove your own admin account while logged in.');
            }

            $conn->begin_transaction();
            $transaction_started = true;

            execute_account_delete($conn, $account_id);

            $conn->commit();
            $transaction_started = false;
            redirect_user_page('deleted');
        }

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
                if (!in_array($state, $allowed_states, true)) {
                    throw new Exception('Please select a valid assigned state for staff.');
                }

                $st = $conn->prepare("INSERT INTO staff (staff_id, full_name, phone_number, assigned_state) VALUES (?, ?, ?, ?)");
                $st->bind_param("isss", $new_id, $name, $phone, $state);
                $st->execute();
            } elseif ($admin_has_department) {
                $department = trim($_POST['department'] ?? '');
                if ($department === '') {
                    $department = 'HQ Administration';
                }

                $st = $conn->prepare("INSERT INTO admins (admin_id, full_name, department) VALUES (?, ?, ?)");
                $st->bind_param("iss", $new_id, $name, $department);
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
                if (!in_array($state, $allowed_states, true)) {
                    throw new Exception('Please select a valid assigned state for staff.');
                }

                $st = $conn->prepare("INSERT INTO staff (staff_id, full_name, phone_number, assigned_state) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), phone_number = VALUES(phone_number), assigned_state = VALUES(assigned_state)");
                $st->bind_param("isss", $account_id, $name, $phone, $state);
                $st->execute();
            } elseif ($admin_has_department) {
                $department = trim($_POST['department'] ?? '');
                if ($department === '') {
                    $department = 'HQ Administration';
                }

                $st = $conn->prepare("INSERT INTO admins (admin_id, full_name, department) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), department = VALUES(department)");
                $st->bind_param("iss", $account_id, $name, $department);
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

$admin_department_select = $admin_has_department ? "ad.department" : "'HQ Administration' AS department";
$admin_detail_expr = $admin_has_department ? "COALESCE(ad.department, 'HQ Administration')" : "'HQ Administration'";

$query = "
    SELECT
        a.account_id,
        a.email,
        a.role,
        CASE
            WHEN a.role = 'CUSTOMER' THEN COALESCE(c.full_name, s.full_name, ad.full_name)
            WHEN a.role = 'STAFF' THEN COALESCE(s.full_name, c.full_name, ad.full_name)
            ELSE COALESCE(ad.full_name, c.full_name, s.full_name)
        END AS full_name,
        COALESCE(c.phone_number, s.phone_number, '') AS phone_number,
        c.marital_status,
        c.dependents_count,
        c.occupation,
        c.monthly_income,
        s.assigned_state,
        $admin_department_select,
        CASE
            WHEN a.role = 'CUSTOMER' THEN COALESCE(c.occupation, 'Customer')
            WHEN a.role = 'STAFF' THEN COALESCE(s.assigned_state, 'Not Assigned')
            ELSE $admin_detail_expr
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
        if ($_GET['msg'] === 'updated') {
            $message = 'User updated successfully.';
        } elseif ($_GET['msg'] === 'deleted') {
            $message = 'User removed successfully.';
        } else {
            $message = 'User registered successfully.';
        }
        ?>
        <?php echo alert_box('success', $message); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white"><i class="fas fa-users-cog text-primary me-2"></i>User Management</h2>
        <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#registerModal">
            <i class="fas fa-user-plus me-2"></i>Add User
        </button>
    </div>

    <div class="card shadow-sm border-0 bg-white text-dark">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="usersTable" class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>State</th>
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
                                <td data-order="<?php echo (int)$u['account_id']; ?>">#<?php echo (int)$u['account_id']; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($u['full_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><span class="badge bg-<?php echo $badge; ?>"><?php echo htmlspecialchars($role); ?></span></td>
                                <td><span class="badge border border-secondary text-secondary"><?php echo htmlspecialchars($u['assigned_state'] ?: 'N/A'); ?></span></td>
                                <td><?php echo htmlspecialchars($u['detail'] ?? 'N/A'); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-dark fw-bold" data-bs-toggle="modal" data-bs-target="#editModal<?php echo (int)$u['account_id']; ?>">
                                            Edit
                                        </button>
                                        <form method="POST" class="m-0 confirm-action-form" data-confirm-title="Remove Account" data-confirm-message="Remove this account permanently?">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="account_id" value="<?php echo (int)$u['account_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Remove account" <?php echo ((int)$u['account_id'] === (int)($_SESSION['account_id'] ?? 0)) ? 'disabled' : ''; ?>>
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <?php ob_start(); ?>
                            <div class="modal fade" id="editModal<?php echo (int)$u['account_id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content bg-white text-dark">
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
                                                        <input type="password" name="password" class="form-control" placeholder="Enter the new password">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Role</label>
                                                        <select name="role" class="form-select edit-role-select" required onchange="toggleEditFields(this)">
                                                            <option value="CUSTOMER" <?php echo ($role === 'CUSTOMER') ? 'selected' : ''; ?>>Customer</option>
                                                            <option value="STAFF" <?php echo ($role === 'STAFF') ? 'selected' : ''; ?>>Staff</option>
                                                            <option value="ADMIN" <?php echo ($role === 'ADMIN') ? 'selected' : ''; ?>>Administrator</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-12 edit-role-field edit-customer-field">
                                                        <hr class="my-2">
                                                        <h6 class="fw-bold text-muted mb-0">Customer Fields</h6>
                                                    </div>
                                                    <div class="col-md-6 edit-role-field edit-customer-field edit-staff-field">
                                                        <label class="form-label fw-bold">Phone Number</label>
                                                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($u['phone_number'] ?? ''); ?>">
                                                    </div>
                                                    <div class="col-md-6 edit-role-field edit-customer-field">
                                                        <label class="form-label fw-bold">Marital Status</label>
                                                        <select name="marital_status" class="form-select">
                                                            <option value="SINGLE" <?php echo ($u['marital_status'] === 'SINGLE') ? 'selected' : ''; ?>>Single</option>
                                                            <option value="MARRIED" <?php echo ($u['marital_status'] === 'MARRIED') ? 'selected' : ''; ?>>Married</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 edit-role-field edit-customer-field">
                                                        <label class="form-label fw-bold">Dependents</label>
                                                        <input type="number" name="dependents_count" class="form-control" min="0" value="<?php echo htmlspecialchars($u['dependents_count'] ?? 0); ?>">
                                                    </div>
                                                    <div class="col-md-4 edit-role-field edit-customer-field">
                                                        <label class="form-label fw-bold">Occupation</label>
                                                        <input type="text" name="occupation" class="form-control" value="<?php echo htmlspecialchars($u['occupation'] ?? ''); ?>">
                                                    </div>
                                                    <div class="col-md-4 edit-role-field edit-customer-field">
                                                        <label class="form-label fw-bold">Monthly Income (RM)</label>
                                                        <input type="number" step="0.01" name="monthly_income" class="form-control" value="<?php echo htmlspecialchars($u['monthly_income'] ?? 0); ?>">
                                                    </div>

                                                    <div class="col-12 edit-role-field edit-staff-field">
                                                        <hr class="my-2">
                                                        <h6 class="fw-bold text-muted mb-0">Staff Fields</h6>
                                                    </div>
                                                    <div class="col-md-6 edit-role-field edit-staff-field">
                                                        <label class="form-label fw-bold">Assigned State</label>
                                                        <select name="assigned_state" class="form-select">
                                                            <?php echo render_state_options($u['assigned_state'] ?? '', $allowed_states); ?>
                                                        </select>
                                                    </div>

                                                    <?php if ($admin_has_department): ?>
                                                        <div class="col-12 edit-role-field edit-admin-field">
                                                            <hr class="my-2">
                                                            <h6 class="fw-bold text-muted mb-0">Admin Fields</h6>
                                                        </div>
                                                        <div class="col-md-6 edit-role-field edit-admin-field">
                                                            <label class="form-label fw-bold">Department</label>
                                                            <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($u['department'] ?? 'HQ Administration'); ?>">
                                                        </div>
                                                    <?php endif; ?>
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
        <div class="modal-content bg-white text-dark">
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
                            <select name="assigned_state" class="form-select">
                                <?php echo render_state_options('', $allowed_states); ?>
                            </select>
                        </div>
                        <?php if ($admin_has_department): ?>
                            <div class="col-md-6 role-field admin-field">
                                <label class="form-label fw-bold">Department</label>
                                <input type="text" name="department" class="form-control" value="HQ Administration">
                            </div>
                        <?php endif; ?>
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
        const table = $('#usersTable').DataTable({
            order: [[0, 'desc']]
        });
        
        const filterHtml = `
            <select id="dtRoleFilter" class="form-select form-select-sm d-inline-block w-auto me-2">
                <option value="">All Roles</option>
                <option value="ADMIN">ADMIN</option>
                <option value="STAFF">STAFF</option>
                <option value="CUSTOMER">CUSTOMER</option>
            </select>
            <select id="dtStateFilter" class="form-select form-select-sm d-inline-block w-auto me-2">
                <option value="">All States</option>
            </select>
        `;
        $('.dataTables_filter').addClass('d-flex align-items-center justify-content-end').prepend(filterHtml);

        const stateSet = new Set();
        table.rows().every(function() {
            const data = this.data();
            const stateNode = document.createElement('div');
            stateNode.innerHTML = data[4];
            const state = stateNode.textContent.trim();
            if (state && state !== 'N/A') stateSet.add(state);
        });
        
        const stateFilter = $('#dtStateFilter');
        Array.from(stateSet).sort().forEach(s => {
            stateFilter.append(new Option(s, s));
        });
        
        $('#dtRoleFilter').on('change', function() {
            table.column(3).search(this.value).draw();
        });
        $('#dtStateFilter').on('change', function() {
            table.column(4).search(this.value ? '^' + $.fn.dataTable.util.escapeRegex(this.value) + '$' : '', true, false).draw();
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
        if (role === 'ADMIN') {
            document.querySelectorAll('.admin-field').forEach((field) => field.classList.remove('d-none'));
        }
    }

    function toggleEditFields(select) {
        const role = select.value;
        const modal = select.closest('.modal');
        modal.querySelectorAll('.edit-role-field').forEach((field) => {
            field.classList.add('d-none');
        });
        if (role === 'CUSTOMER') {
            modal.querySelectorAll('.edit-customer-field').forEach((field) => field.classList.remove('d-none'));
        }
        if (role === 'STAFF') {
            modal.querySelectorAll('.edit-staff-field').forEach((field) => field.classList.remove('d-none'));
        }
        if (role === 'ADMIN') {
            modal.querySelectorAll('.edit-admin-field').forEach((field) => field.classList.remove('d-none'));
        }
    }

    document.querySelectorAll('.edit-role-select').forEach((select) => toggleEditFields(select));
</script>

<?php include '../includes/footer.php'; ?>
