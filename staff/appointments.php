<?php
session_start();

$_SESSION['role'] = 'STAFF'; 
$_SESSION['account_id'] = 1; 

include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $appt_id = intval($_POST['appointment_id']);
    $status = $_POST['status'];
    $remarks = $_POST['staff_remarks'];
    
    $stmt_upd = $conn->prepare("UPDATE appointments SET status = ?, staff_remarks = ? WHERE appointment_id = ? AND assigned_staff_id = ?");
    $stmt_upd->bind_param("ssii", $status, $remarks, $appt_id, $account_id);
    
    if ($stmt_upd->execute()) {
        header("Location: appointment.php?msg=updated");
        exit();
    }
}

include '../includes/header.php';

$stmt = $conn->prepare("SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.service_type, a.status, c.full_name, c.phone_number, p.project_name, d.file_path 
                        FROM appointments a 
                        JOIN customers c ON a.customer_id = c.customer_id 
                        JOIN properties p ON a.property_id = p.property_id 
                        LEFT JOIN documents d ON d.related_to_type = 'APPOINTMENT' AND d.related_to_id = a.appointment_id 
                        WHERE a.assigned_staff_id = ? 
                        ORDER BY a.appointment_date DESC");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container my-5">
    <div class="mb-5">
        <h2 class="fw-bold m-0"><i class="fas fa-calendar-check text-primary me-2"></i>My Assigned Appointments</h2>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Appointment updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="appointmentsTable" class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Date & Time</th>
                            <th>Customer Name</th>
                            <th>Phone Number</th>
                            <th>Property</th>
                            <th>Service Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['appointment_date'] . ' ' . $row['appointment_time']); ?></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                                <td><span class="text-capitalize"><?php echo str_replace('_', ' ', htmlspecialchars($row['service_type'])); ?></span></td>
                                <td>
                                    <?php
                                    $bg = ($row['status'] === 'ASSIGNED') ? 'primary' : (($row['status'] === 'COMPLETED') ? 'success' : 'danger');
                                    ?>
                                    <span class="badge bg-<?php echo $bg; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'ASSIGNED'): ?>
                                        <button class="btn btn-sm btn-dark fw-bold" data-bs-toggle="modal" data-bs-target="#modalAppt<?php echo $row['appointment_id']; ?>">Update</button>
                                        
                                        <div class="modal fade" id="modalAppt<?php echo $row['appointment_id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-dark text-white">
                                                        <h5 class="modal-title fw-bold">Update Appointment</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="update">
                                                            <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">New Status</label>
                                                                <select name="status" class="form-select" required>
                                                                    <option value="COMPLETED">Completed</option>
                                                                    <option value="NO_SHOW">No Show</option>
                                                                    <option value="CANCELLED">Cancelled</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Staff Remarks</label>
                                                                <textarea name="staff_remarks" class="form-control" rows="3" required placeholder="Enter session notes..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary fw-bold">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary fw-bold" disabled>Processed</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#appointmentsTable').DataTable({ 
            "order": [[0, "desc"]],
            "language": {
                "emptyTable": "No assigned appointments found."
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>