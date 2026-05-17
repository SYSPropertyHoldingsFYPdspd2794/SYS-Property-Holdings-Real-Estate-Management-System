<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

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

$res = $conn->query("SELECT a.appointment_id, a.appointment_date, a.appointment_time, c.full_name, p.project_name, p.state FROM appointments a JOIN customers c ON a.customer_id = c.customer_id JOIN properties p ON a.property_id = p.property_id WHERE a.status = 'REQUESTED'");

include '../includes/header.php';
?>

<div class="container mt-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-4">Global Leads & Appointment Masterlist</h4>
            <div class="table-responsive">
                <table id="assignTable" class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Customer</th>
                            <th>Property</th>
                            <th>State</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $assign_modals = '';
                        while ($row = $res->fetch_assoc()): 
                            $state = $row['state'];
                            $staff_res = $conn->prepare("SELECT staff_id, full_name FROM staff WHERE assigned_state = ?");
                            $staff_res->bind_param("s", $state);
                            $staff_res->execute();
                            $staff_list = $staff_res->get_result();
                            $staff_members = [];
                            while ($s = $staff_list->fetch_assoc()) {
                                $staff_members[] = $s;
                            }
                        ?>
                            <tr>
                                <td><?php echo $row['appointment_date']; ?></td>
                                <td><?php echo $row['appointment_time']; ?></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($state); ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#assignModal<?php echo $row['appointment_id']; ?>">Assign Staff</button>
                                </td>
                            </tr>
                            <?php ob_start(); ?>
                            <div class="modal fade" id="assignModal<?php echo $row['appointment_id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="fw-bold">Assign Regional Representative</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                                                <p>Assigning staff for: <strong><?php echo htmlspecialchars($row['project_name']); ?></strong> in <strong><?php echo htmlspecialchars($state); ?></strong></p>
                                                <?php if (count($staff_members) > 0): ?>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Select Available Staff</label>
                                                        <select name="assigned_staff_id" class="form-select" required>
                                                            <option value="">Select Staff...</option>
                                                            <?php foreach ($staff_members as $s): ?>
                                                                <option value="<?php echo $s['staff_id']; ?>"><?php echo htmlspecialchars($s['full_name']); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                <?php else: ?>
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