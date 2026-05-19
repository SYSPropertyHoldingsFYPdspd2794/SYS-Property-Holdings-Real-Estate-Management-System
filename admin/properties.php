<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $code = $_POST['property_code'];
        $name = $_POST['project_name'];
        $state = $_POST['state'];
        $type = $_POST['property_type'];
        $price = (float)$_POST['price'];
        $total = (int)$_POST['total_units'];
        $built_up = (int)$_POST['built_up_sqft'];
        
        // AUTO-GENERATION: Filename dynamically binds to Code + .jpg, Keyword hardcoded to 'NA'
        $image_filename = $code . '.jpg';
        $image_keyword = 'NA';
        
        $stmt = $conn->prepare("INSERT INTO properties (property_code, project_name, state, property_type, price, total_units, built_up_sqft, image_filename, image_search_keyword, is_affordable, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'ACTIVE')");
        $stmt->bind_param("ssssdiiss", $code, $name, $state, $type, $price, $total, $built_up, $image_filename, $image_keyword);
        $stmt->execute();
    } elseif ($_POST['action'] === 'edit') {
        $id = (int)$_POST['property_id'];
        $price = (float)$_POST['price'];
        $total = (int)$_POST['total_units'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE properties SET price = ?, total_units = ?, status = ? WHERE property_id = ?");
        $stmt->bind_param("diis", $price, $total, $status, $id);
        $stmt->execute();
    }
    header("Location: properties.php");
    exit();
}

include_once '../includes/header.php';

$res = $conn->query("SELECT * FROM properties WHERE is_affordable = 0 ORDER BY property_id DESC");
?>

<div class="container-fluid my-5 px-4">
    <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom">
        <div>
            <h1 class="fw-bold text-dark mb-1"><i class="fas fa-boxes me-3 text-primary"></i>Property Asset Registry</h1>
            <p class="text-muted mb-0">Enterprise administration panel for real estate portfolios and live deployment logs.</p>
        </div>
        <button class="btn btn-dark btn-lg fw-bold shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addPropModal">
            <i class="fas fa-plus-circle me-2"></i>Provision New Asset
        </button>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4 bg-light">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary">Search Project Name</label>
                    <input type="text" id="filterName" class="form-control form-control-lg border-0 shadow-sm" placeholder="Type to search...">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary">Filter State</label>
                    <select id="filterState" class="form-select form-select-lg border-0 shadow-sm">
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
                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary">Filter Type</label>
                    <select id="filterType" class="form-select form-select-lg border-0 shadow-sm">
                        <option value="">All Types</option>
                        <option value="TERRACE">TERRACE</option>
                        <option value="BUNGALOW">BUNGALOW</option>
                        <option value="COMMERCIAL">COMMERCIAL</option>
                        <option value="APARTMENT">APARTMENT</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle custom-table" id="propsTable" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Project Name</th>
                            <th>State</th>
                            <th>Type</th>
                            <th>Price (RM)</th>
                            <th>Total Units</th>
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
                                        <div class="avatar-icon bg-primary bg-opacity-10 text-primary me-3 rounded-3 p-2">
                                            <i class="fas fa-building fa-lg"></i>
                                        </div>
                                        <div>
                                            <span class="d-block fw-bold text-dark"><?php echo htmlspecialchars($row['project_name']); ?></span>
                                            <small class="text-muted font-monospace"><?php echo htmlspecialchars($row['property_code']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-secondary py-2 px-3 rounded-pill"><?php echo htmlspecialchars($row['state']); ?></span></td>
                                <td class="fw-bold text-muted"><?php echo htmlspecialchars($row['property_type']); ?></td>
                                <td class="fw-bold text-success">RM <?php echo number_format($row['price'], 2); ?></td>
                                <td><span class="fw-bold"><?php echo $row['total_units']; ?></span> <span class="text-muted small">units</span></td>
                                <td><?php echo number_format($row['built_up_sqft']); ?> <small class="text-muted">sqft</small></td>
                                <td>
                                    <?php if ($row['status'] === 'ACTIVE'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i>Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill"><i class="fas fa-ban me-1"></i>Suspended</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-dark fw-bold rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editPropModal<?php echo $row['property_id']; ?>">
                                        <i class="fas fa-edit me-1"></i>Configure
                                    </button>
                                </td>
                            </tr>

                            <div class="modal fade" id="editPropModal<?php echo $row['property_id']; ?>" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow rounded-4">
                                        <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                                            <h5 class="modal-title fw-bold"><i class="fas fa-sliders-h me-2"></i>Configure Asset #<?php echo $row['property_id']; ?></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="property_id" value="<?php echo $row['property_id']; ?>">
                                            <div class="modal-body p-4">
                                                <div class="mb-4">
                                                    <label class="form-label fw-bold">Price (RM)</label>
                                                    <input type="number" step="0.01" name="price" class="form-control form-control-lg bg-light" value="<?php echo $row['price']; ?>" required>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="form-label fw-bold">Total Available Units</label>
                                                    <input type="number" name="total_units" class="form-control form-control-lg bg-light" value="<?php echo $row['total_units']; ?>" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label fw-bold">System Status</label>
                                                    <select name="status" class="form-select form-select-lg bg-light" required>
                                                        <option value="ACTIVE" <?php if($row['status'] === 'ACTIVE') echo 'selected'; ?>>ACTIVE / BROADCASTING</option>
                                                        <option value="INACTIVE" <?php if($row['status'] === 'INACTIVE') echo 'selected'; ?>>SUSPENDED / OFFLINE</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer p-3 bg-light rounded-bottom-4">
                                                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold">Save Constraints</button>
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

<div class="modal fade" id="addPropModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-primary text-white rounded-top-4 py-3">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Provision New Real Estate Asset</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Property Code</label>
                            <input type="text" name="property_code" class="form-control" placeholder="e.g. W-TER-JB001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Project Name</label>
                            <input type="text" name="project_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">State</label>
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
                            <label class="form-label fw-bold">Property Type</label>
                            <select name="property_type" class="form-select" required>
                                <option value="TERRACE">TERRACE</option>
                                <option value="BUNGALOW">BUNGALOW</option>
                                <option value="COMMERCIAL">COMMERCIAL</option>
                                <option value="APARTMENT">APARTMENT</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Price (RM)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Total Units</label>
                            <input type="number" name="total_units" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Built-up Area (Sqft)</label>
                            <input type="number" name="built_up_sqft" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary fw-bold">Deploy Asset Record</button>
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
        const table = $('#propsTable').DataTable({
            "order": [[0, "desc"]],
            "dom": "lrtip"
        });

        $('#filterName').on('keyup change', function() {
            table.column(1).search(this.value).draw();
        });

        $('#filterState').on('change', function() {
            table.column(2).search(this.value).draw();
        });

        $('#filterType').on('change', function() {
            table.column(3).search(this.value).draw();
        });
    });
</script>

<?php include '../includes/footer.php'; ?>