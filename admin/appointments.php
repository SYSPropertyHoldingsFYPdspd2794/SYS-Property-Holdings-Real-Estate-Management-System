<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn);

$account_id = $_SESSION['account_id'];
$assign_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['assigned_staff_id'])) {
    $appt_id = (int)$_POST['appointment_id'];
    $staff_id = (int)$_POST['assigned_staff_id'];
    $stmt = $conn->prepare("UPDATE appointments SET assigned_staff_id = ?, status = 'ASSIGNED' WHERE appointment_id = ? AND status = 'REQUESTED' AND TIMESTAMP(appointment_date, appointment_time) > NOW()");
    $stmt->bind_param("ii", $staff_id, $appt_id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $log_stmt = $conn->prepare("INSERT INTO audit_logs (account_id, action_type, entity_type, entity_id) VALUES (?, 'LEAD_ASSIGNED', 'appointment_id',?)");
        $log_stmt->bind_param("ii", $account_id, $appt_id);
        $log_stmt->execute();
        header("Location: appointments.php?msg=assigned");
        exit();
    }
    header("Location: appointments.php?msg=expired");
    exit();
}

$res = $conn->query("SELECT a.appointment_id, a.appointment_date, a.appointment_time, c.full_name, p.project_name, p.state FROM appointments a JOIN customers c ON a.customer_id = c.customer_id JOIN properties p ON a.property_id = p.property_id WHERE a.status = 'REQUESTED' AND TIMESTAMP(a.appointment_date, a.appointment_time) > NOW() ORDER BY a.appointment_date ASC, a.appointment_time ASC");

include '../includes/header.php';
?>

<div class="container mt-5">
    <?php if (($_GET['msg'] ?? '') === 'assigned'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            Appointment assigned successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (($_GET['msg'] ?? '') === 'expired'): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            This appointment is no longer available for assignment because it has expired or is no longer pending.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Appointment Assignments</h4>
            </div>
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
                            $staff_res = $conn->prepare("SELECT staff_id, full_name, assigned_state FROM staff ORDER BY CASE WHEN assigned_state = ? THEN 0 ELSE 1 END, assigned_state, full_name");
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
                                                <h5 class="fw-bold text-dark">Assign Representative</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-dark">
                                                <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                                                <p>Assigning staff for: <strong><?php echo htmlspecialchars($row['project_name']); ?></strong> in <strong><?php echo htmlspecialchars($state); ?></strong></p>
                                                <?php if (count($staff_members) > 0): ?>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Select Available Staff</label>
                                                        <select name="assigned_staff_id" class="form-select" required>
                                                            <option value="">Select Staff...</option>
                                                            <?php foreach ($staff_members as $s): ?>
                                                                <?php
                                                                    $staff_state = $s['assigned_state'] ?: 'Unassigned';
                                                                    $is_recommended = $s['assigned_state'] === $state;
                                                                    $staff_label = $staff_state . ' - ' . $s['full_name'] . ($is_recommended ? ' (Recommended)' : '');
                                                                ?>
                                                                <option value="<?php echo $s['staff_id']; ?>"><?php echo htmlspecialchars($staff_label); ?></option>
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
        const table = $('#assignTable').DataTable();

        const stateSet = new Set();
        table.rows().every(function() {
            const data = this.data();
            const stateNode = document.createElement('div');
            stateNode.innerHTML = data[4];
            const state = stateNode.textContent.trim();
            if (state) stateSet.add(state);
        });

        const filterHtml = `<select id="filterState" class="form-select form-select-sm d-inline-block w-auto ms-2" style="height: 31px; font-size: 0.875rem; padding-top: 0.25rem; padding-bottom: 0.25rem;">
                                <option value="">All States</option>
                            </select>`;
        
        $('.dataTables_filter').addClass('d-flex align-items-center justify-content-end');
        $('.dataTables_filter label').addClass('d-flex align-items-center mb-0');
        $('.dataTables_filter').append(filterHtml);
        
        const stateFilter = $('#filterState');
        if (stateSet.size > 0) {
            Array.from(stateSet).sort().forEach(s => {
                stateFilter.append(new Option(s, s));
            });
        } else {
            stateFilter.hide();
        }

        stateFilter.on('change', function() {
            table.column(4).search(this.value ? '^' + $.fn.dataTable.util.escapeRegex(this.value) + '$' : '', true, false).draw();
        });
    });
</script>

<?php include '../includes/footer.php';?>
