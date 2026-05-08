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

include '../includes/header.php';
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container my-5">
    <h2 class="fw-bold mb-4"><i class="fas fa-users-cog text-primary me-2"></i>Global Lead Assignment Pipeline</h2>
    <!-- Table content same as original -->
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
                            <!-- Modal logic follows original -->
                        <?php endwhile;?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal and Scripts follow original -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#assignTable').DataTable();
    });
</script>

<?php include '../includes/footer.php';?>
