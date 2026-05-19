<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $code = $_POST['property_code'];
        $name = $_POST['project_name'];
        $state = $_POST['state'];
        $price = (float)$_POST['price'];
        $total = (int)$_POST['total_units'];
        $built_up = (int)$_POST['built_up_sqft'];
        $income_limit = (float)$_POST['income_limit_rm'];
        
        // AUTO-GENERATION: Filename dynamically binds to Code + .jpg, Keyword hardcoded to 'NA'
        $image_filename = $code . '.jpg';
        $image_search_keyword = 'NA';
        
        $stmt = $conn->prepare("INSERT INTO properties (property_code, project_name, state, property_type, price, total_units, built_up_sqft, income_limit_rm, image_filename, image_search_keyword, is_affordable, status) VALUES (?, ?, ?, 'AFFORDABLE', ?, ?, ?, ?, ?, ?, 1, 'ACTIVE')");
        $stmt->bind_param("sssdiiiss", $code, $name, $state, $price, $total, $built_up, $income_limit, $image_filename, $image_search_keyword);
        $stmt->execute();
    } elseif ($_POST['action'] === 'edit') {
        $id = (int)$_POST['property_id'];
        $price = (float)$_POST['price'];
        $total = (int)$_POST['total_units'];
        $income_limit = (float)$_POST['income_limit_rm'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE properties SET price = ?, total_units = ?, income_limit_rm = ?, status = ? WHERE property_id = ? AND is_affordable = 1");
        $stmt->bind_param("diisi", $price, $total, $income_limit, $status, $id);
        $stmt->execute();
    }
    header("Location: affordable_properties.php");
    exit();
}

include_once '../includes/header.php';

$res = $conn->query("SELECT * FROM properties WHERE is_affordable = 1 ORDER BY property_id DESC");
?>

<div class="container-fluid my-5 px-4">
    <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom">
        <div>
            <h1 class="fw-bold text-dark mb-1"><i class="fas fa-gavel me-3 text-info"></i>Government Affordable Housing</h1>
            <p class="text-muted mb-0">Manage restricted tier schemes, regulatory constraints, and financial income limits.</p>
        </div>
        <button class="btn btn-info btn-lg fw-bold text-dark shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addAffModal">
            <i class="fas fa-plus-circle me-2"></i>Publish Scheme Asset
        </button>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4 bg-light">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Search Scheme Project</label>
                    <input type="text" id="filterAffName" class="form-control form-control-lg border-0 shadow-sm" placeholder="Search affordable schemes...">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary">Filter State Boundary</label>
                    <select id="filterAffState" class="form-select form-select-lg border-0 shadow-sm">
                        <option value="">All States / Territories</option>
                        <option value="Johor">Johor</option>
                        <option value="Kedah">Kedah</option>
                        <option value="Kelantan">Kelantan</option>
                        <option value="Kuala Lumpur">Kuala Lumpur (WPKL)</option>
                        <option value="Labuan">Labuan (WPL)</option>
                        <option value="Melaka">Melaka</option>
                        <option value="Negeri Sembilan">Negeri Sembilan</option>
                        <option value="Pahang">Pahang</option>
                        <option value="Penang">Penang (Pulau Pinang)</option>
                        <option value="Perak">Perak</option>
                        <option value="Perlis">Perlis</option>
                        <option value="Putrajaya">Putrajaya (WPP)</option>
                        <option value="Sabah">Sabah</option>
                        <option value="Sarawak">Sarawak</option>
                        <option value="Selangor">Selangor</option>
                        <option value="Terengganu">Terengganu</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle custom-table" id="affTable" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Scheme Name</th>
                            <th>State Location</th>
                            <th>Pricing Floor (RM)</th>
                            <th>Regulatory Income Ceiling</th>
                            <th>Units Available</th>
                            <th>Built-up</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $res->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold text-secondary">#<?php echo $row['property_id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-icon bg-info bg-opacity-10 text-info me-3 rounded-3 p-2">
                                            <i class="fas fa-home fa-lg"></i>
                                        </div>
                                        <div>
                                            <span class="d-block fw-bold text-dark"><?php echo htmlspecialchars($row['project_name']); ?></span>
                                            <small class="text-muted font-monospace"><?php echo htmlspecialchars($row['property_code']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 py-2 px-3 rounded-pill fw-bold"><?php echo htmlspecialchars($row['state']); ?></span></td>
                                <td class="fw-bold text-dark">RM <?php echo number_format($row['price'], 2); ?></td>
                                <td class="fw-bold text-danger"><i class="fas fa-id-card me-1 small"></i>RM <?php echo number_format($row['income_limit_rm'], 2); ?> / mo</td>
                                <td><span class="fw-bold"><?php echo $row['total_units']; ?></span> <span class="text-muted small">units</span></td>
                                <td><?php echo number_format($row['built_up_sqft']); ?> <small class="text-muted">sqft</small></td>
                                <td>
                                    <?php if ($row['status'] === 'ACTIVE'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill"><i class="fas fa-broadcast-tower me-1"></i>Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill"><i class="fas fa-ban me-1"></i>Offline</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-dark fw-bold rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editAffModal<?php echo $row['property_id']; ?>">
                                        <i class="fas fa-wrench me-1"></i>Modify
                                    </button>
                                </td>
                            </tr>

                            <div class="modal fade" id="editAffModal<?php echo $row['property_id']; ?>" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow rounded-4">
                                        <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                                            <h5 class="modal-title fw-bold"><i class="fas fa-sliders-h me-2"></i>Adjust Scheme Parameters #<?php echo $row['property_id']; ?></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="property_id" value="<?php echo $row['property_id']; ?>">
                                            <div class="modal-body p-4">
                                                <div class="mb-4">
                                                    <label class="form-label fw-bold">Scheme Base Price (RM)</label>
                                                    <input type="number" step="0.01" name="price" class="form-control form-control-lg bg-light" value="<?php echo $row['price']; ?>" required>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="form-label fw-bold">Total Allocation Units</label>
                                                    <input type="number" name="total_units" class="form-control form-control-lg bg-light" value="<?php echo $row['total_units']; ?>" required>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="form-label fw-bold">Regulatory Income Cap (RM)</label>
                                                    <input type="number" step="0.01" name="income_limit_rm" class="form-control form-control-lg bg-light" value="<?php echo $row['income_limit_rm']; ?>" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label fw-bold">Publish State</label>
                                                    <select name="status" class="form-select form-select-lg bg-light" required>
                                                        <option value="ACTIVE" <?php if($row['status'] === 'ACTIVE') echo 'selected'; ?>>ACTIVE / BROADCASTING</option>
                                                        <option value="INACTIVE" <?php if($row['status'] === 'INACTIVE') echo 'selected'; ?>>SUSPENDED / OFFLINE</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer p-3 bg-light rounded-bottom-4">
                                                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-info text-dark rounded-pill px-4 fw-bold">Save Scheme Constraints</button>
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

<div class="modal fade" id="addAffModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-info text-dark rounded-top-4 py-3">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Publish New Controlled Housing Scheme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Unique Structural Code</label>
                            <input type="text" name="property_code" class="form-control" placeholder="e.g. J-AF-JB001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Government Scheme Project Name</label>
                            <input type="text" name="project_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">State Territory Bound</label>
                            <select name="state" class="form-select" required>
                                <option value="Johor">Johor</option>
                                <option value="Kedah">Kedah</option>
                                <option value="Kelantan">Kelantan</option>
                                <option value="Kuala Lumpur">Kuala Lumpur (WPKL)</option>
                                <option value="Labuan">Labuan (WPL)</option>
                                <option value="Melaka">Melaka</option>
                                <option value="Negeri Sembilan">Negeri Sembilan</option>
                                <option value="Pahang">Pahang</option>
                                <option value="Penang">Penang (Pulau Pinang)</option>
                                <option value="Perak">Perak</option>
                                <option value="Perlis">Perlis</option>
                                <option value="Putrajaya">Putrajaya (WPP)</option>
                                <option value="Sabah">Sabah</option>
                                <option value="Sarawak">Sarawak</option>
                                <option value="Selangor">Selangor</option>
                                <option value="Terengganu">Terengganu</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Applicant Maximum Income Cap (Had Pendapatan)</label>
                            <input type="number" step="0.01" name="income_limit_rm" class="form-control" placeholder="e.g. 5000.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Regulated Subsidized Price (RM)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Total Scheme Allocation Units</label>
                            <input type="number" name="total_units" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Floor Plan Area Space (Sqft)</label>
                            <input type="number" name="built_up_sqft" class="form-control" required>
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