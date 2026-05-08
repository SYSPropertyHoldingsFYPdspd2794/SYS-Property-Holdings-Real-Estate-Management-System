<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';

// Secure the page
protect_staff_page('STAFF', $conn);

$account_id = $_SESSION['account_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $appt_id = intval($_POST['appointment_id']);
    $status = $_POST['status'];
    $remarks = $_POST['staff_remarks'];
    
    $stmt_upd = $conn->prepare("UPDATE appointments SET status = ?, staff_remarks = ? WHERE appointment_id = ? AND assigned_staff_id = ?");
    $stmt_upd->bind_param("ssii", $status, $remarks, $appt_id, $account_id);
    
    if ($stmt_upd->execute()) {
        header("Location: appointments.php?msg=updated");
        exit();
    }
}

include '../includes/header.php';

$stmt = $conn->prepare("SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.service_type, a.status, c.full_name, c.phone_number, p.project_name 
                        FROM appointments a 
                        JOIN customers c ON a.customer_id = c.customer_id 
                        JOIN properties p ON a.property_id = p.property_id 
                        WHERE a.assigned_staff_id = ? 
                        ORDER BY a.appointment_date DESC");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container my-5">
    <h2 class="fw-bold mb-4"><i class="fas fa-calendar-check text-primary me-2"></i>My Appointments</h2>

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
                            <th>Property</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['appointment_date'] . ' ' . $row['appointment_time']); ?></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                                <td>
                                    <?php $bg = ($row['status'] === 'ASSIGNED') ? 'primary' : (($row['status'] === 'COMPLETED') ? 'success' : 'danger'); ?>
                                    <span class="badge bg-<?php echo $bg; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'ASSIGNED'): ?>
                                        <button class="btn btn-sm btn-dark fw-bold" data-bs-toggle="modal" data-bs-target="#modalAppt<?php echo $row['appointment_id']; ?>">Update</button>
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
        $('#appointmentsTable').DataTable({ "order": [[0, "desc"]] });
    });
</script>

<?php include '../includes/footer.php'; ?>