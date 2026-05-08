<?php
// 1. Include dependencies
include '../includes/db_connect.php';
include '../includes/auth_check.php';

// 2. Validate session and restrict DB connection
// This fixed the "Call to undefined function" error
protect_staff_page('STAFF', $conn);

$account_id = $_SESSION['account_id']; 

$stmt_staff = $conn->prepare("SELECT assigned_state FROM staff WHERE staff_id = ?");
$stmt_staff->bind_param("i", $account_id);
$stmt_staff->execute();
$staff = $stmt_staff->get_result()->fetch_assoc();
$assigned_state = $staff['assigned_state'] ?? '';

// 3. Handle Approval/Rejection Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'], $_POST['action'])) {
    $app_id = intval($_POST['application_id']);
    $status = ($_POST['action'] === 'APPROVE') ? 'APPROVED_FOR_DRAW' : 'REJECTED';
    
    $stmt_upd = $conn->prepare("UPDATE affordable_housing_applications a JOIN properties p ON a.property_id = p.property_id SET a.status = ?, a.reviewed_by_staff_id = ? WHERE a.application_id = ? AND p.state = ?");
    $stmt_upd->bind_param("siis", $status, $account_id, $app_id, $assigned_state);
    
    if ($stmt_upd->execute()) {
        header("Location: verifications.php?msg=success");
        exit();
    }
}

include '../includes/header.php';

// 4. Fetch pending applications for the staff's region
$stmt_apps = $conn->prepare("SELECT a.application_id, a.application_date, c.full_name, c.monthly_income, p.project_name, d.file_path 
                             FROM affordable_housing_applications a 
                             JOIN customers c ON a.customer_id = c.customer_id 
                             JOIN properties p ON a.property_id = p.property_id 
                             JOIN documents d ON a.application_id = d.related_to_id AND d.related_to_type = 'APPLICATION' 
                             WHERE p.state = ? AND a.status = 'PENDING_REVIEW'");
$stmt_apps->bind_param("s", $assigned_state);
$stmt_apps->execute();
$result = $stmt_apps->get_result();
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container my-5">
    <div class="mb-5">
        <h2 class="fw-bold m-0"><i class="fas fa-file-signature text-success me-2"></i>Affordable Housing Verifications</h2>
    </div>
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <strong>Success!</strong> Application has been processed.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="alert alert-warning text-dark fw-bold mb-4 shadow-sm">
        <i class="fas fa-exclamation-triangle me-2"></i>Regional Check: Currently viewing <strong><?php echo htmlspecialchars($assigned_state); ?></strong>.
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="verificationsTable" class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Customer Name</th>
                            <th>Income (RM)</th>
                            <th>Property</th>
                            <th>Document</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['application_date']))); ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td class="fw-bold text-success"><?php echo number_format($row['monthly_income'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file-pdf"></i> View
                                        </a>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Process this application?');">
                                            <input type="hidden" name="application_id" value="<?php echo $row['application_id']; ?>">
                                            <button type="submit" name="action" value="APPROVE" class="btn btn-sm btn-success">Approve</button>
                                            <button type="submit" name="action" value="REJECT" class="btn btn-sm btn-danger">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
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
            "order": [[0, "desc"]],
            "language": {
                "emptyTable": "No pending applications for your assigned region."
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
