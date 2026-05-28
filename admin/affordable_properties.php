<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';
require_once '../includes/property_images.php';

protect_admin_page('ADMIN', $conn);

$malaysia_regions = [
    'Johor',
    'Kedah',
    'Kelantan',
    'Melaka',
    'Negeri Sembilan',
    'Pahang',
    'Penang',
    'Perak',
    'Perlis',
    'Sabah',
    'Sarawak',
    'Selangor',
    'Terengganu',
    'Kuala Lumpur',
    'Labuan',
    'Putrajaya'
];

$property_types = [
    'TERRACE' => 'Terrace',
    'BUNGALOW' => 'Bungalow',
    'COMMERCIAL' => 'Commercial',
    'APARTMENT' => 'Apartment'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $redirect_page = "affordable_properties.php";

    if ($_POST['action'] === 'add') {
        $code = $_POST['property_code'];
        $name = $_POST['project_name'];
        $state = $_POST['state'];
        $type = $_POST['property_type'];
        $price = (float)$_POST['price'];
        $total = (int)$_POST['total_units'];
        $built_up = (int)$_POST['built_up_sqft'];
        $income_limit = (float)$_POST['income_limit_rm'];
        
        // ENHANCEMENT: Auto-generate filename based on code, hardcode keyword to NA
        $image_filename = $code . '.jpg';
        $image_search_keyword = 'NA';
        $uploaded_image = save_property_image_upload('property_image', $code, __DIR__ . '/../SYS Property Catalog');
        if ($uploaded_image !== null) {
            $image_filename = $uploaded_image;
        }
        
        $stmt = $conn->prepare("INSERT INTO properties (property_code, project_name, state, property_type, price, total_units, built_up_sqft, income_limit_rm, image_filename, image_search_keyword, is_affordable, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'ACTIVE')");
        $stmt->bind_param("ssssdiiiss", $code, $name, $state, $type, $price, $total, $built_up, $income_limit, $image_filename, $image_search_keyword);
        $stmt->execute();
    } elseif ($_POST['action'] === 'edit') {
        $id = (int)$_POST['property_id'];
        $code = trim($_POST['property_code']);
        $name = trim($_POST['project_name']);
        $state = $_POST['state'];
        $type = $_POST['property_type'];
        $price = (float)$_POST['price'];
        $total = (int)$_POST['total_units'];
        $built_up = (int)$_POST['built_up_sqft'];
        $income_limit = (float)$_POST['income_limit_rm'];
        
        // BUG FIX: Ensure the property remains affordable regardless of its physical type
        $is_affordable = 1;

        $stmt = $conn->prepare("UPDATE properties SET property_code = ?, project_name = ?, state = ?, property_type = ?, price = ?, total_units = ?, built_up_sqft = ?, income_limit_rm = ?, is_affordable = ? WHERE property_id = ?");
        $stmt->bind_param("ssssdiidii", $code, $name, $state, $type, $price, $total, $built_up, $income_limit, $is_affordable, $id);
        $stmt->execute();
    } elseif ($_POST['action'] === 'archive') {
        $id = (int)$_POST['property_id'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE properties SET status = ? WHERE property_id = ? AND is_affordable = 1");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
    }
    header("Location: " . $redirect_page);
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
                        <option value="">All Regions</option>
                        <optgroup label="States">
                            <option value="Johor">Johor</option>
                            <option value="Kedah">Kedah</option>
                            <option value="Kelantan">Kelantan</option>
                            <option value="Melaka">Melaka</option>
                            <option value="Negeri Sembilan">Negeri Sembilan</option>
                            <option value="Pahang">Pahang</option>
                            <option value="Penang">Penang</option>
                            <option value="Perak">Perak</option>
                            <option value="Perlis">Perlis</option>
                            <option value="Sabah">Sabah</option>
                            <option value="Sarawak">Sarawak</option>
                            <option value="Selangor">Selangor</option>
                            <option value="Terengganu">Terengganu</option>
                        </optgroup>
                        <optgroup label="Federal Territories">
                            <option value="Kuala Lumpur">Kuala Lumpur</option>
                            <option value="Labuan">Labuan</option>
                            <option value="Putrajaya">Putrajaya</option>
                        </optgroup>
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
                                <td class="fw-bold text-success"><?php echo htmlspecialchars($p['property_code'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($p['project_name']); ?></td>
                                <td><?php echo htmlspecialchars($p['state']); ?></td>
                                <td><?php echo number_format($p['built_up_sqft'] ?? 0); ?> sqft</td>
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
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="fw-bold text-dark">Modify Affordable Property Information</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-dark">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="property_id" value="<?php echo $p['property_id']; ?>">
                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Property Code</label>
                                                        <input type="text" name="property_code" class="form-control" value="<?php echo htmlspecialchars($p['property_code'] ?? ''); ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Property Name</label>
                                                        <input type="text" name="project_name" class="form-control" value="<?php echo htmlspecialchars($p['project_name']); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">State</label>
                                                        <select name="state" class="form-select" required>
                                                            <?php foreach ($malaysia_regions as $region): ?>
                                                                <option value="<?php echo htmlspecialchars($region); ?>" <?php echo $p['state'] === $region ? 'selected' : ''; ?>><?php echo htmlspecialchars($region); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Type</label>
                                                        <select name="property_type" class="form-select" required>
                                                            <?php foreach ($property_types as $type_value => $type_label): ?>
                                                                <option value="<?php echo htmlspecialchars($type_value); ?>" <?php echo $p['property_type'] === $type_value ? 'selected' : ''; ?>><?php echo htmlspecialchars($type_label); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Regulated House Price (RM)</label>
                                                        <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $p['price']; ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Applicant Maximum Income Eligibility (Had Pendapatan)</label>
                                                        <input type="number" step="0.01" name="income_limit_rm" class="form-control" value="<?php echo $p['income_limit_rm']; ?>" required>
                                                    </div>
                                                </div>
                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Total Allocation Units</label>
                                                        <input type="number" name="total_units" class="form-control" value="<?php echo $p['total_units']; ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Built Up (Sqft)</label>
                                                        <input type="number" name="built_up_sqft" class="form-control" value="<?php echo $p['built_up_sqft']; ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-info fw-bold text-dark">Save Changes</button>
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
                                                <h5 class="fw-bold text-dark">Archive Regulated Housing Unit</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-dark">
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
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="fw-bold text-dark">New Scheme Registration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-dark">
                    <input type="hidden" name="action" value="add">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Unique Structural Code</label>
                            <input type="text" name="property_code" class="form-control" placeholder="e.g. J-AF-JB001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Government Scheme Project Name</label>
                            <input type="text" name="project_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">State Territory Bound</label>
                            <select name="state" class="form-select" required>
                                <option value="Johor">Johor</option>
                                <option value="Kedah">Kedah</option>
                                <option value="Kelantan">Kelantan</option>
                                <option value="Kuala Lumpur">Kuala Lumpur</option>
                                <option value="Labuan">Labuan</option>
                                <option value="Melaka">Melaka</option>
                                <option value="Negeri Sembilan">Negeri Sembilan</option>
                                <option value="Pahang">Pahang</option>
                                <option value="Penang">Penang</option>
                                <option value="Perak">Perak</option>
                                <option value="Perlis">Perlis</option>
                                <option value="Putrajaya">Putrajaya</option>
                                <option value="Sabah">Sabah</option>
                                <option value="Sarawak">Sarawak</option>
                                <option value="Selangor">Selangor</option>
                                <option value="Terengganu">Terengganu</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Layout Core Categorization</label>
                            <select name="property_type" class="form-select" required>
                                <?php foreach ($property_types as $type_value => $type_label): ?>
                                    <option value="<?php echo htmlspecialchars($type_value); ?>"><?php echo htmlspecialchars($type_label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Regulated Subsidized Price (RM)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Applicant Maximum Income Cap</label>
                            <input type="number" step="0.01" name="income_limit_rm" class="form-control" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Total Scheme Allocation Units</label>
                            <input type="number" name="total_units" class="form-control" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Floor Plan Area Space (Sqft)</label>
                            <input type="number" name="built_up_sqft" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Property Photo</label>
                            <input type="file" name="property_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info fw-bold text-dark">Publish Scheme</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Force DataTables length menu to be a single inline sentence */
    .dataTables_length label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0;
        white-space: nowrap;
        font-weight: normal;
    }
    .dataTables_length select {
        width: auto !important;
        display: inline-block;
    }
    /* Ensure pagination and info align properly without extra margins */
    .dataTables_paginate {
        margin: 0 !important;
    }
    .dataTables_info {
        padding-top: 0 !important;
    }
</style>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        const table = $('#affTable').DataTable({
            "order": [[0, "desc"]],
            "dom": "<'row'<'col-sm-12'tr>>" +
                   "<'row mt-3 align-items-center'<'col-sm-12 col-md-4 text-start'i><'col-sm-12 col-md-4 d-flex justify-content-center'p><'col-sm-12 col-md-4 d-flex justify-content-end'l>>"
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
