<?php
session_start();
if (!isset($_SESSION['role']) |

| $_SESSION['role']!== 'ADMIN') {
    header("Location:../login.php");
    exit();
}
include '../includes/db_connect.php';

if ($_SERVER === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = $_POST['project_name'];
        $state = $_POST['state'];
        $type = $_POST['property_type'];
        $price = $_POST['price'];
        $total = $_POST['total_units'];
        $avail = $_POST['available_units'];
        $stmt = $conn->prepare("INSERT INTO properties (project_name, state, property_type, price, total_units, available_units, status) VALUES (?,?,?,?,?,?, 'ACTIVE')");
        $stmt->bind_param("sssdii", $name, $state, $type, $price, $total, $avail);
        $stmt->execute();
    } elseif ($_POST['action'] === 'edit') {
        $id = $_POST['property_id'];
        $price = $_POST['price'];
        $avail = $_POST['available_units'];
        $stmt = $conn->prepare("UPDATE properties SET price =?, available_units =? WHERE property_id =?");
        $stmt->bind_param("dii", $price, $avail, $id);
        $stmt->execute();
    } elseif ($_POST['action'] === 'archive') {
        $id = $_POST['property_id'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE properties SET status =? WHERE property_id =?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
    }
    header("Location: properties.php");
    exit();
}

$res = $conn->query("SELECT * FROM properties ORDER BY property_id DESC");

include '../includes/header.php';
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-building text-primary me-2"></i>Global Property Inventory</h2>
        <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>Add New Property</button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="propsTable" class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Project Name</th>
                            <th>State</th>
                            <th>Type</th>
                            <th>Price (RM)</th>
                            <th>Available</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $res->fetch_assoc()):?>
                            <tr>
                                <td><?php echo $row['property_id'];?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['project_name']);?></td>
                                <td><?php echo htmlspecialchars($row['state']);?></td>
                                <td><?php echo htmlspecialchars($row['property_type']);?></td>
                                <td><?php echo number_format($row['price'], 2);?></td>
                                <td><?php echo $row['available_units']. ' / '. $row['total_units'];?></td>
                                <td>
                                    <?php
                                    $bg = 'success';
                                    if ($row['status'] === 'ARCHIVED') $bg = 'secondary';
                                    if ($row['status'] === 'SOLD_OUT') $bg = 'danger';
                                   ?>
                                    <span class="badge bg-<?php echo $bg;?>"><?php echo htmlspecialchars($row['status']);?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-dark fw-bold mb-1" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['property_id'];?>">Edit</button>
                                    <button class="btn btn-sm btn-danger fw-bold mb-1" data-bs-toggle="modal" data-bs-target="#archiveModal<?php echo $row['property_id'];?>">Archive</button>
                                </td>
                            </tr>

                            <div class="modal fade" id="editModal<?php echo $row['property_id'];?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-dark text-white">
                                            <h5 class="modal-title fw-bold">Edit Property Data</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="property_id" value="<?php echo $row['property_id'];?>">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Price (RM)</label>
                                                    <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $row['price'];?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Available Units</label>
                                                    <input type="number" name="available_units" class="form-control" value="<?php echo $row['available_units'];?>" max="<?php echo $row['total_units'];?>" required>
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

                            <div class="modal fade" id="archiveModal<?php echo $row['property_id'];?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title fw-bold">Update Status</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="archive">
                                                <input type="hidden" name="property_id" value="<?php echo $row['property_id'];?>">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">New Status</label>
                                                    <select name="status" class="form-select" required>
                                                        <option value="ACTIVE" <?php echo $row['status'] === 'ACTIVE'? 'selected' : '';?>>Active</option>
                                                        <option value="SOLD_OUT" <?php echo $row['status'] === 'SOLD_OUT'? 'selected' : '';?>>Sold Out</option>
                                                        <option value="ARCHIVED" <?php echo $row['status'] === 'ARCHIVED'? 'selected' : '';?>>Archived</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-danger fw-bold">Confirm Status</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endwhile;?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Add New Property</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Project Name</label>
                            <input type="text" name="project_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">State</label>
                            <input type="text" name="state" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Property Type</label>
                            <select name="property_type" class="form-select" required>
                                <option value="AFFORDABLE">Affordable</option>
                                <option value="TERRACE">Terrace</option>
                                <option value="BUNGALOW">Bungalow</option>
                                <option value="COMMERCIAL">Commercial</option>
                                <option value="APARTMENT">Apartment</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Price (RM)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Total Units</label>
                            <input type="number" name="total_units" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Available Units</label>
                            <input type="number" name="available_units" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary fw-bold">Create Property</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#propsTable').DataTable({
            "order": [[0, "desc"]]
        });
    });
</script>

<?php include '../includes/footer.php';?>

