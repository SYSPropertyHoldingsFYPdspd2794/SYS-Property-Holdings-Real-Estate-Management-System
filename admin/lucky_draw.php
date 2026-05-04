<?php
session_start();
if (!isset($_SESSION['role']) |

| $_SESSION['role']!== 'ADMIN') {
    header("Location:../login.php");
    exit();
}
include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];
$alert = '';

if ($_SERVER === 'POST' && isset($_POST['property_id'], $_POST['draw_limit'])) {
    $prop_id = intval($_POST['property_id']);
    $limit = intval($_POST['draw_limit']);

    $stmt = $conn->prepare("UPDATE affordable_housing_applications SET status = 'WINNER' WHERE property_id =? AND status = 'APPROVED_FOR_DRAW' ORDER BY RAND() LIMIT?");
    $stmt->bind_param("ii", $prop_id, $limit);
    if ($stmt->execute()) {
        $log_stmt = $conn->prepare("INSERT INTO audit_logs (account_id, action_type, entity_type, entity_id) VALUES (?, 'LUCKY_DRAW_EXECUTED', 'property_id',?)");
        $log_stmt->bind_param("ii", $account_id, $prop_id);
        $log_stmt->execute();
        $alert = '<div class="alert alert-success fw-bold">Algorithmic lucky draw executed successfully.</div>';
    } else {
        $alert = '<div class="alert alert-danger fw-bold">Failed to execute lucky draw.</div>';
    }
}

$props = $conn->query("SELECT property_id, project_name, state FROM properties WHERE property_type = 'AFFORDABLE' AND status = 'ACTIVE'");
$winners = $conn->query("SELECT a.application_id, c.full_name, c.monthly_income, p.project_name, p.state FROM affordable_housing_applications a JOIN customers c ON a.customer_id = c.customer_id JOIN properties p ON a.property_id = p.property_id WHERE a.status = 'WINNER' ORDER BY a.application_id DESC");

include '../includes/header.php';
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container my-5">
    <h2 class="fw-bold mb-4"><i class="fas fa-dice text-danger me-2"></i>Housing Allocation Lucky Draw</h2>
    <?php echo $alert;?>
    <div class="card shadow border-0 border-top border-danger border-5 mb-5">
        <div class="card-body p-5">
            <form method="POST" class="row align-items-end">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Target Affordable Property</label>
                    <select name="property_id" class="form-select form-select-lg" required>
                        <option value="" disabled selected>Select a property pool...</option>
                        <?php while ($p = $props->fetch_assoc()):?>
                            <option value="<?php echo $p['property_id'];?>">
                                <?php echo htmlspecialchars($p['project_name']. ' ('. $p['state']. ')');?>
                            </option>
                        <?php endwhile;?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Number of Winners</label>
                    <input type="number" name="draw_limit" class="form-control form-control-lg" min="1" required>
                </div>
                <div class="col-md-3 mb-3">
                    <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold">Execute Algorithm</button>
                </div>
            </form>
        </div>
    </div>

    <h3 class="fw-bold mb-4">Historical Winners Registry</h3>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="winnersTable" class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Application ID</th>
                            <th>Customer Name</th>
                            <th>Declared Income (RM)</th>
                            <th>Property</th>
                            <th>State</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($w = $winners->fetch_assoc()):?>
                            <tr>
                                <td>APP-<?php echo str_pad($w['application_id'], 5, '0', STR_PAD_LEFT);?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($w['full_name']);?></td>
                                <td><?php echo number_format($w['monthly_income'], 2);?></td>
                                <td><?php echo htmlspecialchars($w['project_name']);?></td>
                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($w['state']);?></span></td>
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
        $('#winnersTable').DataTable();
    });
</script>

<?php include '../includes/footer.php';?>

