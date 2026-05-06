<?php
session_start();

/** 
 * DIRECT ACCESS BYPASS
 */
$_SESSION['role'] = 'ADMIN'; 
$_SESSION['account_id'] = 1; 
$_SESSION['full_name'] = 'System Admin';

include '../includes/db_connect.php';

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['role'])) {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $name = $_POST['full_name'];
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    
    // 1. Insert into accounts table
    $stmt = $conn->prepare("INSERT INTO accounts (email, password_hash, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $password, $role);
    
    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        
        // 2. Insert into specific sub-tables based on role
        if ($role === 'STAFF') {
            $state = $_POST['assigned_state'];
            $st = $conn->prepare("INSERT INTO staff (staff_id, full_name, phone_number, assigned_state) VALUES (?, ?, ?, ?)");
            $st->bind_param("isss", $new_id, $name, $phone, $state);
            $st->execute();
        } else if ($role === 'ADMIN') {
            $dept = 'HQ Administration';
            $st = $conn->prepare("INSERT INTO admins (admin_id, full_name, department) VALUES (?, ?, ?)");
            $st->bind_param("iss", $new_id, $name, $dept);
            $st->execute();
        }
        
        header("Location: user.php?msg=success");
        exit();
    }
}

/**
 * BUG FIX: SQL Query
 * We use LEFT JOIN so that we don't lose users if their sub-table record is missing.
 * We use COALESCE to pick the name and detail from whichever table has data.
 */
$query = "
    SELECT 
        a.account_id, a.email, a.role, 
        COALESCE(s.full_name, ad.full_name) as full_name,
        COALESCE(s.assigned_state, ad.department) as detail
    FROM accounts a
    LEFT JOIN staff s ON a.account_id = s.staff_id
    LEFT JOIN admins ad ON a.account_id = ad.admin_id
    WHERE a.role IN ('STAFF', 'ADMIN')
    ORDER BY a.account_id DESC
";
$users = $conn->query($query);

include '../includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container my-5">
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> User registered successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-users-cog text-primary me-2"></i>User Management</h2>
        <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#registerModal">
            <i class="fas fa-user-plus me-2"></i>Add Internal User
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
                            <th>Assignment/Dept</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = $users->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $u['account_id']; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($u['full_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <?php $badge = ($u['role'] === 'ADMIN') ? 'danger' : 'info text-dark'; ?>
                                    <span class="badge bg-<?php echo $badge; ?>"><?php echo htmlspecialchars($u['role']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($u['detail'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">New Internal Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
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
                            <label class="form-label fw-bold">Phone Number</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">System Role</label>
                            <select name="role" id="roleSelect" class="form-select" required onchange="toggleState()">
                                <option value="STAFF">Staff</option>
                                <option value="ADMIN">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="stateDiv">
                            <label class="form-label fw-bold">Assigned State / Department</label>
                            <input type="text" name="assigned_state" id="assignedState" class="form-control" required>
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
        $('#usersTable').DataTable();
    });

    function toggleState() {
        var role = document.getElementById('roleSelect').value;
        var stateInput = document.getElementById('assignedState');
        if (role === 'ADMIN') {
            stateInput.value = 'HQ Administration';
            stateInput.readOnly = true;
        } else {
            stateInput.value = '';
            stateInput.readOnly = false;
        }
    }
</script>

<?php include '../includes/footer.php'; ?>