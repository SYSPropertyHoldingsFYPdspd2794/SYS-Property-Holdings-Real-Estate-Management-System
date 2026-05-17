<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn);

$query = "SELECT aha.application_date, p.project_name, p.property_code, c.full_name AS customer_name, c.dependents_count, c.monthly_income, s.full_name AS staff_name, aha.status FROM affordable_housing_applications aha JOIN customers c ON aha.customer_id = c.customer_id JOIN properties p ON aha.property_id = p.property_id LEFT JOIN staff s ON aha.reviewed_by_staff_id = s.staff_id WHERE aha.status IN ('APPROVED_FOR_DRAW', 'WINNER', 'REJECTED') ORDER BY aha.application_date DESC";
$result = $conn->query($query);

include '../includes/header.php';
?>

<div class="container mt-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Affordable Housing Staff Approvals Log</h4>
                <a href="dashboard.php" class="btn btn-outline-dark fw-bold">Back to Dashboard</a>
            </div>
            <div class="table-responsive">
                <table id="approvalsTable" class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Property Code</th>
                            <th>Property Name</th>
                            <th>Customer Name</th>
                            <th>Deps</th>
                            <th>Monthly Income</th>
                            <th>Reviewed By (Staff)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('Y-m-d H:i', strtotime($row['application_date'])); ?></td>
                                <td class="fw-bold text-primary"><?php echo htmlspecialchars($row['property_code']); ?></td>
                                <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td><?php echo $row['dependents_count']; ?></td>
                                <td class="fw-bold">RM <?php echo number_format($row['monthly_income'], 2); ?></td>
                                <td><?php echo $row['staff_name'] ? htmlspecialchars($row['staff_name']) : '<span class="text-muted">System Allocated</span>'; ?></td>
                                <td>
                                    <?php if ($row['status'] === 'APPROVED_FOR_DRAW'): ?>
                                        <span class="badge bg-info text-dark">Approved for Draw</span>
                                    <?php elseif ($row['status'] === 'WINNER'): ?>
                                        <span class="badge bg-success">Winner</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Rejected</span>
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
        $('#approvalsTable').DataTable({
            "order": [[0, "desc"]]
        });
    });
</script>

<?php include '../includes/footer.php'; ?>