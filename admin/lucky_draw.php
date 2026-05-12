<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role']!== 'ADMIN') {
    header("Location:../login.php");
    exit();
}
include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];
$alert = '';

function column_exists($conn, $table, $column) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->bind_param("ss", $table, $column);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return (int)$count > 0;
}

$has_available_units = column_exists($conn, 'properties', 'available_units');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['property_id'], $_POST['draw_limit'])) {
    $prop_id = (int)$_POST['property_id'];
    $limit = (int)$_POST['draw_limit'];

    if ($prop_id <= 0 || $limit <= 0) {
        $alert = '<div class="alert alert-danger fw-bold">Please select a property and enter a valid number of winners.</div>';
    } else {
        try {
            $conn->begin_transaction();

            $unit_column = $has_available_units ? 'available_units' : 'total_units';
            $prop_stmt = $conn->prepare("SELECT total_units, $unit_column AS available_units FROM properties WHERE property_id = ? AND is_affordable = 1 AND status = 'ACTIVE' FOR UPDATE");
            $prop_stmt->bind_param("i", $prop_id);
            $prop_stmt->execute();
            $property = $prop_stmt->get_result()->fetch_assoc();

            if (!$property) {
                throw new Exception('Selected property is not available for lucky draw.');
            }

            $available_units = (int)$property['available_units'];
            if (!$has_available_units) {
                $winner_count_stmt = $conn->prepare("SELECT COUNT(*) AS winner_total FROM affordable_housing_applications WHERE property_id = ? AND status = 'WINNER'");
                $winner_count_stmt->bind_param("i", $prop_id);
                $winner_count_stmt->execute();
                $winner_total = (int)$winner_count_stmt->get_result()->fetch_assoc()['winner_total'];
                $available_units = max(0, (int)$property['total_units'] - $winner_total);
            }

            if ($available_units <= 0) {
                throw new Exception('This property has no available units left.');
            }

            $draw_limit = min($limit, $available_units);
            $candidate_stmt = $conn->prepare("SELECT application_id FROM affordable_housing_applications WHERE property_id = ? AND status = 'APPROVED_FOR_DRAW' ORDER BY RAND() LIMIT ?");
            $candidate_stmt->bind_param("ii", $prop_id, $draw_limit);
            $candidate_stmt->execute();
            $candidate_result = $candidate_stmt->get_result();

            $winner_ids = [];
            while ($row = $candidate_result->fetch_assoc()) {
                $winner_ids[] = (int)$row['application_id'];
            }

            if (count($winner_ids) === 0) {
                throw new Exception('No approved applicants are available for this property.');
            }

            $winner_count = count($winner_ids);
            $winner_id_list = implode(',', $winner_ids);
            $update_stmt = $conn->prepare("UPDATE affordable_housing_applications SET status = 'WINNER' WHERE application_id IN ($winner_id_list)");
            $update_stmt->execute();

            $status = $available_units - $winner_count <= 0 ? 'SOLD_OUT' : 'ACTIVE';
            if ($has_available_units) {
                $unit_stmt = $conn->prepare("UPDATE properties SET available_units = available_units - ?, status = ? WHERE property_id = ?");
                $unit_stmt->bind_param("isi", $winner_count, $status, $prop_id);
            } else {
                $unit_stmt = $conn->prepare("UPDATE properties SET status = ? WHERE property_id = ?");
                $unit_stmt->bind_param("si", $status, $prop_id);
            }
            $unit_stmt->execute();

            $log_stmt = $conn->prepare("INSERT INTO audit_logs (account_id, action_type, entity_type, entity_id) VALUES (?, 'LUCKY_DRAW_EXECUTED', 'property_id', ?)");
            $log_stmt->bind_param("ii", $account_id, $prop_id);
            $log_stmt->execute();

            $conn->commit();
            $alert = '<div class="alert alert-success fw-bold">Lucky draw executed successfully. '.$winner_count.' winner(s) selected.</div>';
        } catch (Exception $e) {
            $conn->rollback();
            $alert = '<div class="alert alert-danger fw-bold">'.htmlspecialchars($e->getMessage()).'</div>';
        }
    }
}

$props_sql = $has_available_units
    ? "SELECT property_id, project_name, state, available_units FROM properties WHERE is_affordable = 1 AND status = 'ACTIVE' AND available_units > 0"
    : "SELECT p.property_id, p.project_name, p.state, GREATEST(p.total_units - COALESCE(w.winner_total, 0), 0) AS available_units
       FROM properties p
       LEFT JOIN (
           SELECT property_id, COUNT(*) AS winner_total
           FROM affordable_housing_applications
           WHERE status = 'WINNER'
           GROUP BY property_id
       ) w ON p.property_id = w.property_id
       WHERE p.is_affordable = 1
           AND p.status = 'ACTIVE'
           AND GREATEST(p.total_units - COALESCE(w.winner_total, 0), 0) > 0";
$props = $conn->query($props_sql);
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
                                <?php echo htmlspecialchars($p['project_name']. ' ('. $p['state']. ') - '. $p['available_units']. ' unit(s) available');?>
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

