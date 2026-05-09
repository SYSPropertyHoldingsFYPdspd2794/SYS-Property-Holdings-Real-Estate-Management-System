<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';

// Secure the page
protect_admin_page('ADMIN', $conn);

$account_id = $_SESSION['account_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['assigned_staff_id'])) {
    $appt_id = $_POST['appointment_id'];
    $staff_id = $_POST['assigned_staff_id'];
    $stmt = $conn->prepare("UPDATE appointments SET assigned_staff_id =?, status = 'ASSIGNED' WHERE appointment_id =?");
    $stmt->bind_param("ii", $staff_id, $appt_id);
    if ($stmt->execute()) {
        $log_stmt = $conn->prepare("INSERT INTO audit_logs (account_id, action_type, entity_type, entity_id) VALUES (?, 'LEAD_ASSIGNED', 'appointment_id',?)");
        $log_stmt->bind_param("ii", $account_id, $appt_id);
        $log_stmt->execute();
    }
    header("Location: appointments.php");
    exit();
}

$res = $conn->query("SELECT a.appointment_id, a.appointment_date, a.appointment_time, c.full_name, p.project_name, p.state FROM appointments a JOIN customers c ON a.customer_id = c.customer_id JOIN properties p ON a.property_id = p.property_id WHERE a.status = 'REQUESTED' ORDER BY a.appointment_date ASC");
$staff_res = $conn->query("SELECT s.staff_id, s.full_name, s.assigned_state FROM staff s JOIN accounts a ON s.staff_id = a.account_id WHERE a.role = 'STAFF' ORDER BY s.assigned_state, s.full_name");
$staff_members = [];
while ($staff = $staff_res->fetch_assoc()) {
    $staff_members[] = $staff;
}
$assign_modals = '';

include '../includes/header.php';
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container my-5">
    <h2 class="fw-bold mb-4"><i class="fas fa-users-cog text-primary me-2"></i>Global Lead Assignment Pipeline</h2>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="assignTable" class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Date & Time</th>
                            <th>Customer</th>
                            <th>Property</th>
                            <th>State</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $res->fetch_assoc()):?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['appointment_date']. ' '. $row['appointment_time']);?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['full_name']);?></td>
                                <td><?php echo htmlspecialchars($row['project_name']);?></td>
                                <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['state']);?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#assignModal<?php echo $row['appointment_id'];?>">Assign Staff</button>
                                </td>
                            </tr>
                            <?php ob_start(); ?>
                            <div class="modal fade" id="assignModal<?php echo (int)$row['appointment_id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title fw-bold">Assign Staff</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="appointment_id" value="<?php echo (int)$row['appointment_id']; ?>">
                                                <p class="mb-3">
                                                    <strong><?php echo htmlspecialchars($row['project_name']); ?></strong><br>
                                                    <span class="text-muted"><?php echo htmlspecialchars($row['full_name'] . ' - ' . $row['appointment_date'] . ' ' . $row['appointment_time']); ?></span>
                                                </p>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Staff Member</label>
                                                    <select name="assigned_staff_id" class="form-select" required>
                                                        <option value="" disabled selected>Select staff...</option>
                                                        <?php foreach ($staff_members as $staff): ?>
                                                            <option value="<?php echo (int)$staff['staff_id']; ?>">
                                                                <?php echo htmlspecialchars($staff['full_name'] . ' (' . ($staff['assigned_state'] ?: 'No state') . ')'); ?>
                                                                <?php echo $staff['assigned_state'] === $row['state'] ? ' - Recommended' : ''; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <?php if (count($staff_members) === 0): ?>
                                                    <div class="alert alert-warning mb-0">No staff accounts are available. Create a staff user first.</div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary fw-bold" <?php echo count($staff_members) === 0 ? 'disabled' : ''; ?>>Assign Lead</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php $assign_modals .= ob_get_clean(); ?>
                        <?php endwhile;?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php echo $assign_modals; ?>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#assignTable').DataTable();
    });
</script>

<?php include '../includes/footer.php';?>
