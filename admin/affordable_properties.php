<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location:../login.php");
    exit();
}
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $code = $_POST['property_code'];
        $name = $_POST['project_name'];
        $state = $_POST['state'];
        $price = (float)$_POST['price'];
        $total = (int)$_POST['total_units'];
        $built_up = (int)$_POST['built_up_sqft'];
        $income_limit = (float)$_POST['income_limit_rm'];
        $image_filename = $_POST['image_filename'];
        $image_search_keyword = $_POST['image_search_keyword'];
        $stmt = $conn->prepare("INSERT INTO properties (property_code, project_name, state, property_type, price, total_units, built_up_sqft, income_limit_rm, image_filename, image_search_keyword, is_affordable, status) VALUES (?, ?, ?, 'AFFORDABLE', ?, ?, ?, ?, ?, ?, 1, 'ACTIVE')");
        $stmt->bind_param("sssdiiiss", $code, $name, $state, $price, $total, $built_up, $income_limit, $image_filename, $image_search_keyword);
        $stmt->execute();
    } elseif ($_POST['action'] === 'edit') {
        $id = (int)$_POST['property_id'];
        $price = (float)$_POST['price'];
        $total = (int)$_POST['total_units'];
        $income_limit = (float)$_POST['income_limit_rm'];
        $stmt = $conn->prepare("UPDATE properties SET price = ?, total_units = ?, income_limit_rm = ? WHERE property_id = ? AND is_affordable = 1");
        $stmt->bind_param("didi", $price, $total, $income_limit, $id);
        $stmt->execute();
    } elseif ($_POST['action'] === 'archive') {
        $id = (int)$_POST['property_id'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE properties SET status = ? WHERE property_id = ? AND is_affordable = 1");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
    }
    header("Location: affordable_properties.php");
    exit();
}

$props = $conn->query("SELECT * FROM properties WHERE is_affordable = 1");
include '../includes/header.php';
?>

<div class="container mt-5">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-4">Affordable House Filter Options</h4>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Project Search Name</label>
                    <input type="text" id="filterAffName" class="form-control" placeholder="Search government properties...">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Regional State Territory</label>
                    <select id="filterAffState" class="form-select">
                        <option value="">All States</option>
                        <option value="Johor">Johor</option>
                        <option value="Selangor">Selangor</option>
                        <option value="Penang">Penang</option>
                        <option value="Kuala Lumpur">Kuala Lumpur</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Government Subsidized Affordable Properties</h4>
                <div>
                    <a href="properties.php" class="btn btn-secondary fw-bold me-2">Back Standard Property</a>
                    <button type="button" class="btn btn-info fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#addAffModal">Add New Affordable House</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="affTable" class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Code</th>
                            <th>Project Name</th>
                            <th>State</th>
                            <th>Built Up</th>
                            <th>Income Ceiling Cap</th>
                            <th>Regulated Price</th>
                            <th>Total Pool Units</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($p = $props->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold text-success"><?php echo htmlspecialchars($p['property_code']); ?></td>
                                <td><?php echo htmlspecialchars($p['project_name']); ?></td>
                                <td><?php echo htmlspecialchars($p['state']); ?></td>
                                <td><?php echo number_format($p['built_up_sqft']); ?> sqft</td>
                                <td class="fw-bold text-danger">RM <?php echo number_format($p['income_limit_rm'], 2); ?></td>
                                <td class="fw-bold text-primary">RM <?php echo number_format($p['price'], 2); ?></td>
                                <td><?php echo $p['total_units']; ?></td>
                                <td>
                                    <?php if ($p['status'] === 'ACTIVE'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php elseif ($p['status'] === 'SOLD_OUT'): ?>
                                        <span class="badge bg-danger">Sold Out</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Archived</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-dark me-1" data-bs-toggle="modal" data-bs-target="#editAffModal<?php echo $p['property_id']; ?>"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#archiveAffModal<?php echo $p['property_id']; ?>"><i class="fas fa-archive"></i></button>
                                </td>
                            </tr>

                            <div class="modal fade" id="editAffModal<?php echo $p['property_id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="fw-bold">Modify Government Eligibility Structures</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="property_id" value="<?php echo $p['property_id']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Regulated House Price (RM)</label>
                                                    <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $p['price']; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Applicant Maximum Income Eligibility (Had Pendapatan)</label>
                                                    <input type="number" step="0.01" name="income_limit_rm" class="form-control" value="<?php echo $p['income_limit_rm']; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Total Allocation Units</label>
                                                    <input type="number" name="total_units" class="form-control" value="<?php echo $p['total_units']; ?>" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-info fw-bold text-dark">Save Policies</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="archiveAffModal<?php echo $p['property_id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="fw-bold">Archive Regulated Housing Unit</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="archive">
                                                <input type="hidden" name="property_id" value="<?php echo $p['property_id']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Target Lifecycle State</label>
                                                    <select name="status" class="form-select">
                                                        <option value="ACTIVE" <?php echo $p['status'] === 'ACTIVE' ? 'selected' : ''; ?>>Active</option>
                                                        <option value="SOLD_OUT" <?php echo $p['status'] === 'SOLD_OUT' ? 'selected' : ''; ?>>Sold Out</option>
                                                        <option value="ARCHIVED" <?php echo $p['status'] === 'ARCHIVED' ? 'selected' : ''; ?>>Archived</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger fw-bold">Apply Status</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addAffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="fw-bold">Deploy Regulated Government Scheme Housing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Unique Structural Code</label>
                            <input type="text" name="property_code" class="form-control" placeholder="e.g. J-AF-JB001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Government Scheme Project Name</label>
                            <input type="text" name="project_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">State Territory Bound</label>
                            <select name="state" class="form-select" required>
                                <option value="Johor">Johor</option>
                                <option value="Selangor">Selangor</option>
                                <option value="Penang">Penang</option>
                                <option value="Kuala Lumpur">Kuala Lumpur</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Applicant Maximum Income Cap (Had Pendapatan)</label>
                            <input type="number" step="0.01" name="income_limit_rm" class="form-control" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Regulated Subsidized Price (RM)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Total Scheme Allocation Units</label>
                            <input type="number" name="total_units" class="form-control" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Floor Plan Area Space (Sqft)</label>
                            <input type="number" name="built_up_sqft" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Storage Target Image Filename</label>
                            <input type="text" name="image_filename" class="form-control" placeholder="example.jpg" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Image Automation Search Keyword</label>
                            <input type="text" name="image_search_keyword" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info fw-bold text-dark">Publish Scheme Asset</button>
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
        const table = $('#affTable').DataTable({
            "order": [[0, "desc"]],
            "dom": "lrtip"
        });

        $('#filterAffName').on('keyup change', function() {
            table.column(1).search(this.value).draw();
        });

        $('#filterAffState').on('change', function() {
            table.column(2).search(this.value).draw();
        });
    });
</script>

<?php include '../includes/footer.php'; ?>