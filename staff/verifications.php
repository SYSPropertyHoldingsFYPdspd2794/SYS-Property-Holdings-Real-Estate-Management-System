<?php
session_start();
if (!isset($_SESSION['role']) |

| $_SESSION['role']!== 'STAFF') {
    header("Location:../login.php");
    exit();
}
include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];

$stmt_staff = $conn->prepare("SELECT assigned_state FROM staff WHERE staff_id =?");
$stmt_staff->bind_param("i", $account_id);
$stmt_staff->execute();
$assigned_state = $stmt_staff->get_result()->fetch_assoc()['assigned_state'];

if ($_SERVER === 'POST' && isset($_POST['application_id'], $_POST['action'])) {
    $app_id = $_POST['application_id'];
    $status = ($_POST['action'] === 'APPROVE')? 'APPROVED_FOR_DRAW' : 'REJECTED';
    $stmt_upd = $conn->prepare("UPDATE affordable_housing_applications SET status =?, reviewed_by_staff_id =? WHERE application_id =?");
    $stmt_upd->bind_param("sii", $status, $account_id, $app_id);
    $stmt_upd->execute();
}

$stmt_apps = $conn->prepare("SELECT a.application_id, a.application_date, c.full_name, c.monthly_income, p.project_name, d.file_path FROM affordable_housing_applications a JOIN customers c ON a.customer_id = c.customer_id JOIN properties p ON a.property_id = p.property_id JOIN documents d ON a.application_id = d.related_to_id AND d.related_to_type = 'APPLICATION' WHERE p.state =? AND a.status = 'PENDING_REVIEW'");
$stmt_apps->bind_param("s", $assigned_state);
$stmt_apps->execute();
$result = $stmt_apps->get_result();

include '../includes/header.php';
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container my-5">
    <h2 class="fw-bold mb-4"><i class="fas fa-file-signature text-success me-2"></i>Affordable Housing Verifications</h2>
    <div class="alert alert-warning text-dark fw-bold mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i>Ensure the uploaded financial abstract strictly aligns with the customer's declared monthly income before approving for the algorithm draw.
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="verificationsTable" class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Application Date</th>
                            <th>Customer Name</th>
                            <th>Declared Income (RM)</th>
                            <th>Property Requested</th>
                            <th>Financial Document</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()):?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($row['application_date'])));?></td>
                                <td><?php echo htmlspecialchars($row['full_name']);?></td>
                                <td class="fw-bold text-success"><?php echo number_format($row['monthly_income'], 2);?></td>
                                <td><?php echo htmlspecialchars($row['project_name']);?></td>
                                <td>
                                    <a href="<?php echo htmlspecialchars($row['file_path']);?>" target="_blank" class="btn btn-sm btn-outline-primary fw-bold">
                                        <i class="fas fa-file-pdf me-1"></i>View Abstract
                                    </a>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="application_id" value="<?php echo $row['application_id'];?>">
                                        <button type="submit" name="action" value="APPROVE" class="btn btn-sm btn-success fw-bold mb-1">Approve for Draw</button>
                                        <button type="submit" name="action" value="REJECT" class="btn btn-sm btn-danger fw-bold mb-1">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile;?>
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
        $('#verificationsTable').DataTable({
            "order": [[0, "desc"]]
        });
    });
</script>

<?php include '../includes/footer.php';?>

