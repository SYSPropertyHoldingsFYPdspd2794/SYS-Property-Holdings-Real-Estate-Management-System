<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: admin/affordable_approvals.php
 * DESCRIPTION: Redesigned Affordable Housing Staff Approvals Log with cascading state filters, real-time metrics, and a dual-axis scrollable data grid.
 */

require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn);

// GATHER COMPREHENSIVE DATA MATRIX FROM AFFORDABLE APPLICATIONS FOR THE MAIN STREAM
$query = "SELECT aha.application_date, p.project_name, p.property_code, p.state, p.price,
                 c.full_name AS customer_name, c.dependents_count, c.monthly_income, 
                 s.full_name AS staff_name, aha.status 
          FROM affordable_housing_applications aha 
          JOIN customers c ON aha.customer_id = c.customer_id 
          JOIN properties p ON aha.property_id = p.property_id 
          LEFT JOIN staff s ON aha.reviewed_by_staff_id = s.staff_id 
          WHERE aha.status IN ('APPROVED_FOR_DRAW', 'WINNER', 'REJECTED') 
          ORDER BY aha.application_date DESC";
$result = $conn->query($query);

// EXTRACT UNIQUE ACTIVE STATES AND AFFORDABLE PROPERTIES FOR ADVANCED FILTER GRID
$filter_props_query = "SELECT DISTINCT project_name, state FROM properties WHERE is_affordable = 1 AND status = 'ACTIVE' ORDER BY project_name ASC";
$filter_props_res = $conn->query($filter_props_query);
$properties_meta_data = [];
$unique_states = [];

while ($p_row = $filter_props_res->fetch_assoc()) {
    $properties_meta_data[] = $p_row;
    if (!in_array($p_row['state'], $unique_states)) {
        $unique_states[] = $p_row['state'];
    }
}
sort($unique_states);

// CALCULATE CURRENT TOTAL VALID CANDIDATES AWAITING LUCKY DRAW RUNS
$pool_count_query = "SELECT COUNT(*) AS total FROM affordable_housing_applications WHERE status = 'APPROVED_FOR_DRAW'";
$pool_count_res = $conn->query($pool_count_query)->fetch_assoc();
$total_approved_pool = (int)($pool_count_res['total'] ?? 0);

include '../includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container-fluid mt-5 px-4">
    
    <div class="row g-4 mb-5">
        
        <div class="col-xl-6 col-lg-7">
            <div class="card shadow-sm border-0 h-100 rounded-4">
                <div class="card-header bg-dark text-white p-4 rounded-top-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-filter text-warning me-2"></i>Cascading Regional Pipeline Filter</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">1. Select Region State</label>
                            <select id="cascadingStateFilter" class="form-select bg-light border-0 shadow-sm">
                                <option value="">All States Location</option>
                                <?php foreach ($unique_states as $st): ?>
                                    <option value="<?php echo htmlspecialchars($st); ?>"><?php echo htmlspecialchars($st); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">2. Select Affordable House Name</label>
                            <select id="cascadingPropFilter" class="form-select bg-light border-0 shadow-sm">
                                <option value="">All Project Schemes</option>
                                </select>
                        </div>
                        <div class="col-12 text-end pt-2">
                            <button type="button" id="executePipelineFilterBtn" class="btn btn-dark fw-bold px-4 rounded-pill shadow-sm">
                                <i class="fas fa-search me-1"></i>Confirm Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-5">
            <div class="card shadow-sm border-0 h-100 rounded-4 bg-light border-start border-info border-4">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="fw-bold text-dark mb-1"><i class="fas fa-bullhorn text-info me-2"></i>Draw Infrastructure Tracker</h5>
                        <p class="text-muted small">Live status count of approved applicants qualified for ballot allocation.</p>
                    </div>
                    
                    <div class="row align-items-center py-2">
                        <div class="col-6 border-end">
                            <small class="d-block text-muted fw-bold text-uppercase tracking-wider small">Approved for Draw</small>
                            <span class="display-6 fw-bold text-info"><?php echo $total_approved_pool; ?></span> <small class="text-secondary fw-bold">Applicants</small>
                        </div>
                        <div class="col-6 text-center">
                            <a href="lucky_draw.php" class="btn btn-warning fw-bold text-dark px-3 py-2 rounded-pill shadow-sm btn-sm">
                                <i class="fas fa-dharmachakra me-1"></i>Go to Lucky Draw
                            </a>
                        </div>
                    </div>
                    <div></div>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-5">
        <div class="card-header bg-dark text-white p-4 rounded-top-4 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold mb-0"><i class="fas fa-clipboard-list text-primary me-2"></i>Affordable Housing Staff Approvals Log</h4>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm fw-bold rounded-pill px-3">Back to Dashboard</a>
        </div>
        <div class="card-body p-4">
            
            <div class="table-responsive border rounded-3 shadow-sm" style="max-height: 600px; overflow: auto; white-space: nowrap;">
                <table id="approvalsTable" class="table table-striped table-hover align-middle mb-0" style="width:100%">
                    <thead class="table-dark sticky-top">
                        <tr>
                            <th>Application Date</th>
                            <th>Scheme Project Name</th>
                            <th>State Located</th>
                            <th>Customer Name</th>
                            <th class="text-center">Dependents</th>
                            <th class="text-end">Monthly Income</th>
                            <th class="text-end">Total Price</th>
                            <th>Reviewed By</th>
                            <th>Application Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="font-monospace text-muted small"><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($row['application_date']))); ?></td>
                                <td>
                                    <span class="d-block fw-bold text-dark"><?php echo htmlspecialchars($row['project_name']); ?></span>
                                    <small class="text-muted font-monospace small"><?php echo htmlspecialchars($row['property_code']); ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border px-3 py-2 rounded-pill"><i class="fas fa-map-marker-alt text-danger me-1"></i><?php echo htmlspecialchars($row['state']); ?></span></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td class="text-center"><span class="badge bg-secondary rounded-pill px-3"><?php echo $row['dependents_count']; ?></span></td>
                                <td class="text-end text-dark fw-bold">RM <?php echo number_format($row['monthly_income'], 2); ?></td>
                                <td class="text-end text-primary fw-bold">RM <?php echo number_format($row['price'], 2); ?></td>
                                <td><?php echo $row['staff_name'] ? htmlspecialchars($row['staff_name']) : '<span class="text-muted italic small"><i class="fas fa-robot me-1"></i>System Allocated</span>'; ?></td>
                                <td>
                                    <?php if ($row['status'] === 'APPROVED_FOR_DRAW'): ?>
                                        <span class="badge bg-info text-dark px-3 py-2 rounded-pill shadow-sm"><i class="fas fa-ticket-alt me-1"></i>Approved for Draw</span>
                                    <?php elseif ($row['status'] === 'WINNER'): ?>
                                        <span class="badge bg-success px-3 py-2 rounded-pill shadow-sm"><i class="fas fa-trophy me-1"></i>Winner</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger px-3 py-2 rounded-pill shadow-sm"><i class="fas fa-times-circle me-1"></i>Rejected</span>
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
    const propertiesMetaData = <?php echo json_encode($properties_data ?? $properties_meta_data); ?>;
    const cascadingStateFilter = document.getElementById('cascadingStateFilter');
    const cascadingPropFilter = document.getElementById('cascadingPropFilter');

    // CASCADING FLOW DROPDOWN LOGIC
    function refreshCascadingProperties() {
        const selectedState = cascadingStateFilter.value;
        cascadingPropFilter.innerHTML = '<option value="">All Project Schemes</option>';
        
        propertiesMetaData.forEach(p => {
            if (selectedState === '' || p.state === selectedState) {
                const opt = document.createElement('option');
                opt.value = p.project_name;
                opt.textContent = p.project_name;
                cascadingPropFilter.appendChild(opt);
            }
        });
    }

    cascadingStateFilter.addEventListener('change', refreshCascadingProperties);
    refreshCascadingProperties(); // Initialize layout links

    // DATATABLES SCRIPT INTEGRATION INTERFACES
    $(document).ready(function() {
        const table = $('#approvalsTable').DataTable({
            "order": [[0, "desc"]],
            "pageLength": 15,
            "dom": "lrtip" // Keeps custom layout filters functional
        });

        // Trigger filter execution instantly when Admin clicks Confirm button
        $('#executePipelineFilterBtn').on('click', function() {
            const stateVal = cascadingStateFilter.value;
            const propVal = cascadingPropFilter.value;

            table.column(2).search(stateVal).column(1).search(propVal).draw();
        });
    });
</script>

<?php include '../includes/footer.php'; ?>