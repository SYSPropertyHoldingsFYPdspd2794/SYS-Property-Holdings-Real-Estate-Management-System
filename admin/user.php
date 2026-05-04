<?php
session_start();
if (!isset($_SESSION['role']) |

| $_SESSION['role']!== 'ADMIN') {
    header("Location:../login.php");
    exit();
}
include '../includes/db_connect.php';

if ($_SERVER === 'POST' && isset($_POST['email'], $_POST['role'])) {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $name = $_POST['full_name'];
    $phone = isset($_POST['phone'])? $_POST['phone'] : '';
    
    $stmt = $conn->prepare("INSERT INTO accounts (email, password_hash, role) VALUES (?,?,?)");
    $stmt->bind_param("sss", $email, $password, $role);
    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        if ($role === 'STAFF') {
            $state = $_POST['assigned_state'];
            $st = $conn->prepare("INSERT INTO staff (staff_id, full_name, phone_number, assigned_state) VALUES (?,?,?,?)");
            $st->bind_param("isss", $new_id, $name, $phone, $state);
            $st->execute();
        } else if ($role === 'ADMIN') {
            $dept = 'HQ Administration';
            $st = $conn->prepare("INSERT INTO admins (admin_id, full_name, department) VALUES (?,?,?)");
            $st->bind_param("iss", $new_id, $name, $dept);
            $st->execute();
        }
    }
    header("Location: users.php");
    exit();
}

$query = "
    SELECT a.account_id, a.email, a.role, s.full_name, s.assigned_state as detail 
    FROM accounts a JOIN staff s ON a.account_id = s.staff_id
    UNION
    SELECT a.account_id, a.email, a.role, ad.full_name, ad.department as detail 
    FROM accounts a JOIN admins ad ON a.account_id = ad.admin_id
";
$users = $conn->query($query);

include '../includes/header.php';
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-users text-primary me-2"></i>Internal User Management</h2>
        <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#registerModal"><i class="fas fa-user-plus me-2"></i>Register Internal User</button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="usersTable" class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Account ID</th>
                            <th>Full Name</th>
                            <th>Email Address</th>
                            <th>System Role</th>
                            <th>Assignment / Dept</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = $users->fetch_assoc()):?>
                            <tr>
                                <td><?php echo $u['account_id'];?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($u['full_name']);?></td>
                                <td><?php echo htmlspecialchars($u['email']);?></td>
                                <td>
                                    <?php $badge = $u['role'] === 'ADMIN'? 'danger' : 'info text-dark';?>
                                    <span class="badge bg-<?php echo $badge;?>"><?php echo htmlspecialchars($u['role']);?></span>
                                </td>
                                <td><?php echo htmlspecialchars($u['detail']);?></td>
                            </tr>
                        <?php endwhile;?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">Register New Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone Number</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">System Role</label>
                            <select name="role" id="roleSelect" class="form-select" required onchange="toggleState()">
                                <option value="STAFF">Staff</option>
                                <option value="ADMIN">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="stateDiv">
                            <label class="form-label fw-bold">Assigned State</label>
                            <input type="text" name="assigned_state" id="assignedState" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary fw-bold">Create Account</button>
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
            stateInput.value = '';
            stateInput.disabled = true;
        } else {
            stateInput.disabled = false;
        }
    }
</script>

<?php include '../includes/footer.php';?>

