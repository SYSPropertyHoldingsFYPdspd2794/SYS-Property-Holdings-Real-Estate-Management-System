<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: admin/lucky_draw.php
 * DESCRIPTION: Enhanced Algorithmic Allocation Engine (Lucky Draw) for Affordable Housing with interactive matrix, capacity deduction, and historical filtering.
 */

require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn);

$account_id = $_SESSION['account_id'];
$alert = '';
$candidates_json = '[]';
$selected_property_id = 0;
$selected_property_name = '';
$available_units = 0;

// HANDLE REAL DRAW EXECUTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['execute_real_draw'])) {
    $prop_id = (int)$_POST['property_id'];
    $winners_array = json_decode($_POST['winners_data'], true);
    if (!empty($winners_array)) {
        $conn->begin_transaction();
        try {
            foreach ($winners_array as $winner_id) {
                $stmt = $conn->prepare("UPDATE affordable_housing_applications SET status = 'WINNER' WHERE application_id = ? AND property_id = ?");
                $stmt->bind_param("ii", $winner_id, $prop_id);
                $stmt->execute();

                $log_stmt = $conn->prepare("INSERT INTO audit_logs (account_id, action_type, entity_type, entity_id) VALUES (?, 'LUCKY_DRAW_EXECUTED', 'application_id', ?)");
                $log_stmt->bind_param("ii", $account_id, $winner_id);
                $log_stmt->execute();
            }
            $conn->commit();
            $alert = '<div class="alert alert-success alert-dismissible fade show fw-bold shadow-sm" role="alert"><i class="fas fa-check-circle me-2"></i>Lucky Draw executed successfully. ' . count($winners_array) . ' units allocated and records synchronized.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        } catch (Exception $e) {
            $conn->rollback();
            $alert = '<div class="alert alert-danger fw-bold shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i>System Error during execution.</div>';
        }
    }
}

// FETCH ALL AFFORDABLE PROPERTIES AND CALCULATE TRUE AVAILABLE UNITS
$props_query = "SELECT p.property_id, p.project_name, p.property_code, p.state, p.total_units, 
                (SELECT COUNT(*) FROM affordable_housing_applications WHERE property_id = p.property_id AND status = 'WINNER') as winners_count 
                FROM properties p WHERE p.is_affordable = 1 AND p.status = 'ACTIVE'";
$props_result = $conn->query($props_query);
$properties_data = [];
$unique_states = [];

while ($row = $props_result->fetch_assoc()) {
    $row['available_units'] = max(0, (int)$row['total_units'] - (int)$row['winners_count']);
    $properties_data[] = $row;
    if (!in_array($row['state'], $unique_states)) {
        $unique_states[] = $row['state'];
    }
}
sort($unique_states);

// HANDLE POOL LOADING
$candidates_array = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['load_pool'])) {
    $selected_property_id = (int)$_POST['property_id'];
    
    // Find the property name and current available units for UI display
    foreach ($properties_data as $pd) {
        if ((int)$pd['property_id'] === $selected_property_id) {
            $selected_property_name = $pd['project_name'];
            $available_units = $pd['available_units'];
            break;
        }
    }

    $pool_stmt = $conn->prepare("SELECT aha.application_id, c.full_name, c.dependents_count, c.monthly_income 
                                 FROM affordable_housing_applications aha 
                                 JOIN customers c ON aha.customer_id = c.customer_id 
                                 WHERE aha.property_id = ? AND aha.status = 'APPROVED_FOR_DRAW' 
                                 ORDER BY c.dependents_count DESC, c.monthly_income ASC");
    $pool_stmt->bind_param("i", $selected_property_id);
    $pool_stmt->execute();
    $pool_result = $pool_stmt->get_result();

    while ($row = $pool_result->fetch_assoc()) {
        $candidates_array[] = $row;
    }
    $candidates_json = json_encode($candidates_array);
}

// FETCH HISTORICAL WINNERS
$winners = $conn->query("SELECT aha.application_id, aha.application_date, aha.notification_count, c.full_name, c.phone_number, c.monthly_income, p.project_name, p.state 
                         FROM affordable_housing_applications aha 
                         JOIN customers c ON aha.customer_id = c.customer_id 
                         JOIN properties p ON aha.property_id = p.property_id 
                         WHERE aha.status = 'WINNER' 
                         ORDER BY aha.application_id DESC");

include '../includes/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="container-fluid mt-5 px-4">
    <?php echo $alert; ?>
    
    <div class="row g-4 mb-5">
        
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm border-0 h-100 rounded-4">
                <div class="card-header bg-dark text-white p-4 rounded-top-4">
                    <h4 class="fw-bold mb-0"><i class="fas fa-cogs text-warning me-2"></i>Draw Configuration</h4>
                </div>
                <div class="card-body p-4 d-flex flex-column">
                    <form method="POST" id="configForm">
                        <input type="hidden" name="load_pool" value="1">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">Filter by State</label>
                            <select id="stateFilter" class="form-select form-select-lg bg-light border-0 shadow-sm">
                                <option value="">All States</option>
                                <?php foreach ($unique_states as $st): ?>
                                    <option value="<?php echo htmlspecialchars($st); ?>"><?php echo htmlspecialchars($st); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">Target Affordable House</label>
                            <select id="propertySelect" name="property_id" class="form-select form-select-lg bg-light border-0 shadow-sm" required>
                                <option value="" disabled selected>Select a property...</option>
                                </select>
                        </div>

                        <div class="mb-4 p-4 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-4 text-center">
                            <h6 class="text-primary fw-bold text-uppercase tracking-wider mb-2">Current Available Units</h6>
                            <h1 class="display-4 fw-bold text-dark m-0" id="availableUnitsDisplay">--</h1>
                        </div>

                        <div class="mt-auto pt-3">
                            <button type="submit" class="btn btn-dark btn-lg w-100 fw-bold rounded-pill shadow-sm">
                                <i class="fas fa-users me-2"></i>Confirm & Load Pool
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm border-0 h-100 rounded-4">
                <div class="card-header bg-dark text-white p-4 rounded-top-4 d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-0"><i class="fas fa-list-alt text-info me-2"></i>Qualified Candidate Pool</h4>
                    <?php if ($selected_property_id > 0): ?>
                        <span class="badge bg-info text-dark fs-6"><?php echo count($candidates_array); ?> Candidates Loaded</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-4 d-flex flex-column">
                    <?php if ($selected_property_id > 0): ?>
                        
                        <h5 class="fw-bold text-primary mb-3"><?php echo htmlspecialchars($selected_property_name); ?></h5>
                        
                        <div class="table-responsive border rounded-3 shadow-sm mb-4" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light sticky-top shadow-sm">
                                    <tr>
                                        <th>Application ID</th>
                                        <th>Customer Name</th>
                                        <th class="text-center">Dependents</th>
                                        <th class="text-end">Monthly Income (RM)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($candidates_array) > 0): ?>
                                        <?php foreach ($candidates_array as $index => $cand): ?>
                                            <tr>
                                                <td class="font-monospace text-muted">APP-<?php echo str_pad($cand['application_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($cand['full_name']); ?></td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary rounded-pill"><?php echo $cand['dependents_count']; ?></span>
                                                </td>
                                                <td class="text-end text-success fw-bold"><?php echo number_format($cand['monthly_income'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No qualified candidates found for this property.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-auto bg-light p-4 rounded-4 border">
                            <div class="row align-items-center g-3">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold text-dark mb-1">Amount of Winners to Select</label>
                                    <input type="number" id="drawLimitInput" class="form-control form-control-lg fw-bold text-center" min="1" max="<?php echo min($available_units, count($candidates_array)); ?>" placeholder="e.g. 5" <?php echo (count($candidates_array) === 0 || $available_units === 0) ? 'disabled' : ''; ?>>
                                </div>
                                <div class="col-md-7 text-md-end">
                                    <button type="button" id="initDrawBtn" class="btn btn-warning btn-lg fw-bold text-dark px-4 shadow rounded-pill" <?php echo (count($candidates_array) === 0 || $available_units === 0) ? 'disabled' : ''; ?>>
                                        <i class="fas fa-dharmachakra me-2"></i>Execute Lucky Draw
                                    </button>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="h-100 d-flex flex-column justify-content-center align-items-center text-muted opacity-50 py-5">
                            <i class="fas fa-tasks fa-4x mb-3"></i>
                            <h4>Select a property and click Confirm to load the candidate pool.</h4>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-5">
        <div class="card-header bg-dark text-white p-4 rounded-top-4 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold mb-0"><i class="fas fa-trophy text-success me-2"></i>Historical Winners Registry</h4>
            <button id="bulkWaBtn" class="btn btn-sm btn-success fw-bold rounded-pill shadow-sm px-3"><i class="fab fa-whatsapp me-2"></i>Bulk Notify Displayed</button>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary small">Filter by State</label>
                    <select id="histStateFilter" class="form-select border-0 shadow-sm">
                        <option value="">All States</option>
                        <?php foreach ($unique_states as $st): ?>
                            <option value="<?php echo htmlspecialchars($st); ?>"><?php echo htmlspecialchars($st); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary small">Filter by Property Scheme</label>
                    <select id="histPropFilter" class="form-select border-0 shadow-sm">
                        <option value="">All Properties</option>
                        <?php 
                            $unique_props = [];
                            foreach ($properties_data as $pd) { $unique_props[] = $pd['project_name']; }
                            $unique_props = array_unique($unique_props);
                            sort($unique_props);
                            foreach ($unique_props as $up): 
                        ?>
                            <option value="<?php echo htmlspecialchars($up); ?>"><?php echo htmlspecialchars($up); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary small">Search Winner Name</label>
                    <input type="text" id="histNameFilter" class="form-control border-0 shadow-sm" placeholder="Type name...">
                </div>
            </div>

            <div class="table-responsive">
                <table id="winnersTable" class="table table-hover table-striped align-middle custom-table" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>App ID</th>
                            <th>Winner Name</th>
                            <th>Income (RM)</th>
                            <th>Property Scheme</th>
                            <th>State</th>
                            <th>Notification Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($w = $winners->fetch_assoc()): 
                            $phone = preg_replace('/[^0-9]/', '', $w['phone_number']);
                            if (strpos($phone, '60') !== 0 && strpos($phone, '0') === 0) {
                                $phone = '60' . substr($phone, 1);
                            }
                            $appDate = date('Y-m-d', strtotime($w['application_date']));
                            
                            $waTextRaw = "Dear *{$w['full_name']}*,\n\n"
                                       . "Warm greetings from *SYS Property Holdings*.\n\n"
                                       . "We are pleased to officially inform you that your application for the Affordable Housing Scheme (Application Date: {$appDate}) has been SUCCESSFUL. You have been selected for the *{$w['project_name']}* project.\n\n"
                                       . "To view your official allocation status and proceed with the next steps, please follow these instructions:\n"
                                       . "1. Visit our official portal: https://syspropertyholdings.infinityfreeapp.com/\n"
                                       . "2. Log in to your Customer Account.\n"
                                       . "3. Navigate to your Dashboard or 'My Applications' section.\n"
                                       . "4. Your application status will now be reflected as 'WINNER'.\n\n"
                                       . "Congratulations once again. Should you require any assistance, please do not hesitate to reply to this message.\n\n"
                                       . "Best Regards,\n"
                                       . "Management of SYS Property Holdings";
                                       
                            $waText = urlencode($waTextRaw);
                            $waLink = "https://wa.me/{$phone}?text={$waText}";
                            $notifCount = (int)($w['notification_count'] ?? 0);
                        ?>
                            <tr>
                                <td class="font-monospace text-muted">WIN-<?php echo str_pad($w['application_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td class="fw-bold text-success"><?php echo htmlspecialchars($w['full_name']); ?></td>
                                <td class="fw-bold text-dark"><?php echo number_format($w['monthly_income'], 2); ?></td>
                                <td class="fw-bold text-secondary"><?php echo htmlspecialchars($w['project_name']); ?></td>
                                <td><span class="badge bg-primary px-3 py-2 rounded-pill"><?php echo htmlspecialchars($w['state']); ?></span></td>
                                <td>
                                    <?php if ($notifCount > 0): ?>
                                        <span class="badge bg-success rounded-pill notify-status-badge" data-app-id="<?php echo $w['application_id']; ?>">Notified (<?php echo $notifCount; ?>)</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill notify-status-badge" data-app-id="<?php echo $w['application_id']; ?>">Not Notified</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?php echo $waLink; ?>" target="_blank" class="btn btn-sm btn-success rounded-pill shadow-sm wa-notify-btn" title="Send WhatsApp" data-app-id="<?php echo $w['application_id']; ?>">
                                        <i class="fab fa-whatsapp"></i> Notify
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="wheelModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 justify-content-between align-items-start p-4">
                <div>
                    <h3 class="fw-bold text-warning mb-1"><i class="fas fa-dharmachakra me-2"></i>SYS Allocation Wheel</h3>
                    <h5 class="text-info m-0" id="wheelPropNameDisplay">Property Name</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4 pt-0">
                <div class="bg-secondary bg-opacity-25 rounded-pill d-inline-block px-4 py-2 mb-4 border border-secondary border-opacity-50 shadow-sm">
                    <h5 class="m-0 fw-bold">Spots Left to Draw: <span id="wheelSpotsLeftDisplay" class="text-warning display-6 ms-2 align-middle">0</span></h5>
                </div>
                
                <div class="position-relative d-inline-block mb-4">
                    <div style="position: absolute; top: -15px; left: 50%; transform: translateX(-50%); width: 0; height: 0; border-left: 18px solid transparent; border-right: 18px solid transparent; border-top: 30px solid #ffc107; z-index: 10; filter: drop-shadow(0px 4px 2px rgba(0,0,0,0.5));"></div>
                    <canvas id="wheelCanvas" width="380" height="380" style="background:#222; border-radius:50%; border: 10px solid #333; box-shadow: 0 0 30px rgba(0,0,0,0.8);"></canvas>
                </div>
                
                <h4 id="wheelStatus" class="fw-bold text-info mb-4">Ready to initialize draw sequence.</h4>
                <button type="button" id="spinBtn" class="btn btn-warning btn-lg fw-bold px-5 py-3 text-dark rounded-pill shadow">SPIN WHEEL</button>
                
                <form id="hiddenWinnersForm" method="POST" class="d-none">
                    <input type="hidden" name="execute_real_draw" value="1">
                    <input type="hidden" name="property_id" value="<?php echo $selected_property_id; ?>">
                    <input type="hidden" id="winnersDataInput" name="winners_data" value="">
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    const propertiesData = <?php echo json_encode($properties_data); ?>;
    const allCandidates = <?php echo $candidates_json; ?>;
    const selectedPropId = <?php echo $selected_property_id; ?>;
    const selectedPropName = "<?php echo htmlspecialchars($selected_property_name); ?>";
    const availableUnits = <?php echo $available_units; ?>;

    // UPPER LEFT: Dynamic Dropdowns
    const stateFilter = document.getElementById('stateFilter');
    const propertySelect = document.getElementById('propertySelect');
    const availableUnitsDisplay = document.getElementById('availableUnitsDisplay');

    function populateProperties() {
        const state = stateFilter.value;
        propertySelect.innerHTML = '<option value="" disabled selected>Select a property...</option>';
        
        propertiesData.forEach(p => {
            if (state === '' || p.state === state) {
                const opt = document.createElement('option');
                opt.value = p.property_id;
                opt.textContent = `${p.property_code} - ${p.project_name}`;
                if (parseInt(p.property_id) === selectedPropId) opt.selected = true;
                propertySelect.appendChild(opt);
            }
        });
        updateAvailableUnits();
    }

    function updateAvailableUnits() {
        const pId = propertySelect.value;
        if (!pId) {
            availableUnitsDisplay.textContent = '--';
            return;
        }
        const prop = propertiesData.find(p => p.property_id == pId);
        availableUnitsDisplay.textContent = prop ? prop.available_units : '--';
        
        if (prop && prop.available_units <= 0) {
            availableUnitsDisplay.classList.replace('text-dark', 'text-danger');
        } else {
            availableUnitsDisplay.classList.replace('text-danger', 'text-dark');
        }
    }

    stateFilter.addEventListener('change', populateProperties);
    propertySelect.addEventListener('change', updateAvailableUnits);
    
    // Initialize state on load
    populateProperties();

    // HISTORICAL TABLE: DataTables Initialization & Custom Filters
    $(document).ready(function() {
        const table = $('#winnersTable').DataTable({
            "order": [[0, "desc"]],
            "dom": "lrtip", // Hides default search box to use our custom one
            "pageLength": 10
        });

        $('#histStateFilter').on('change', function() {
            const selectedState = this.value;
            table.column(4).search(selectedState).draw();
            
            const propFilter = $('#histPropFilter');
            propFilter.empty().append(new Option('All Properties', ''));
            
            let filteredProps = [];
            if (selectedState) {
                filteredProps = propertiesData.filter(p => p.state === selectedState);
            } else {
                filteredProps = propertiesData;
            }
            
            const uniqueProps = [...new Set(filteredProps.map(p => p.project_name))].sort();
            uniqueProps.forEach(name => {
                propFilter.append(new Option(name, name));
            });
            
            table.column(3).search('').draw();
        });

        $('#histPropFilter').on('change', function() {
            table.column(3).search(this.value).draw();
        });

        $('#histNameFilter').on('keyup change', function() {
            table.column(1).search(this.value).draw();
        });

        $('#bulkWaBtn').on('click', function() {
            let stateFilterVal = $('#histStateFilter').val() || 'All States';
            let propFilterVal = $('#histPropFilter').val() || 'All Properties';
            let count = 0;
            let nodesToNotify = [];
            
            // Get all rows currently matching the filter (search: 'applied')
            table.rows({search: 'applied'}).nodes().each(function(node) {
                const btn = $(node).find('a.wa-notify-btn');
                const waLink = btn.attr('href');
                const appId = btn.data('app-id');
                if (waLink) {
                    nodesToNotify.push({ link: waLink, appId: appId, node: node });
                    count++;
                }
            });
            
            if (count > 0) {
                Swal.fire({
                    title: 'Confirm Bulk Notification',
                    html: `You are about to notify <b>${count}</b> winners.<br><br><div class="text-start bg-light p-3 rounded border"><b>Current Filters:</b><br>State: ${stateFilterVal}<br>Property: ${propFilterVal}</div>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'CONFIRM',
                    cancelButtonText: 'CANCEL',
                    confirmButtonColor: '#198754'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire('Processing Notifications', `Opening ${count} WhatsApp Web tabs...<br><br><small class="text-danger">Ensure pop-ups are allowed in your browser.</small>`, 'info');
                        
                        nodesToNotify.forEach((item, index) => {
                            setTimeout(() => {
                                window.open(item.link, '_blank');
                                updateNotificationStatus(item.appId, item.node);
                            }, index * 1000);
                        });
                    }
                });
            } else {
                Swal.fire('No Records', 'No winners displayed to notify. Please adjust your filters.', 'warning');
            }
        });

        // Individual notify button click
        $('#winnersTable').on('click', '.wa-notify-btn', function() {
            const appId = $(this).data('app-id');
            const node = $(this).closest('tr');
            updateNotificationStatus(appId, node);
        });

        function updateNotificationStatus(appId, rowNode) {
            $.post('update_notification_count.php', { application_id: appId }, function(response) {
                try {
                    const res = JSON.parse(response);
                    if (res.success) {
                        const badge = $(rowNode).find('.notify-status-badge');
                        badge.removeClass('bg-secondary').addClass('bg-success');
                        badge.text(`Notified (${res.new_count})`);
                    }
                } catch(e) {}
            });
        }
    });

    // WHEEL LOGIC
    const initDrawBtn = document.getElementById('initDrawBtn');
    if (initDrawBtn) {
        initDrawBtn.addEventListener('click', function() {
            const limitVal = parseInt(document.getElementById('drawLimitInput').value);
            
            if (isNaN(limitVal) || limitVal <= 0) {
                Swal.fire('Invalid Input', 'Please enter a valid number of winners to draw.', 'error');
                return;
            }
            if (limitVal > availableUnits) {
                Swal.fire('Capacity Exceeded', `You cannot draw ${limitVal} winners. Only ${availableUnits} units are available.`, 'error');
                return;
            }
            if (limitVal > allCandidates.length) {
                Swal.fire('Insufficient Pool', `You requested ${limitVal} winners, but there are only ${allCandidates.length} qualified candidates.`, 'error');
                return;
            }

            // Slice top N from candidates
            setupWheel(allCandidates.slice(0, limitVal), limitVal);
        });
    }

    function setupWheel(preWinners, totalToDraw) {
        const wheelModal = new bootstrap.Modal(document.getElementById('wheelModal'));
        
        document.getElementById('wheelPropNameDisplay').textContent = selectedPropName;
        const spotsDisplay = document.getElementById('wheelSpotsLeftDisplay');
        spotsDisplay.textContent = totalToDraw;
        
        const canvas = document.getElementById('wheelCanvas');
        const ctx = canvas.getContext('2d');
        const spinBtn = document.getElementById('spinBtn');
        const statusText = document.getElementById('wheelStatus');

        let sectors = preWinners.map(w => ({
            id: w.application_id,
            label: w.full_name.substring(0, 14) + '...'
        }));

        if(sectors.length === 1) {
            sectors.push({id: 0, label: "Evaluating Pool"});
        }

        let currentWinnersSubmitted = [];
        let currentWinnerIndex = 0;
        let spotsLeft = totalToDraw;
        let angle = 0;

        function drawWheel() {
            const numSectors = sectors.length;
            const arc = 2 * Math.PI / numSectors;
            ctx.clearRect(0, 0, 380, 380);
            
            for (let i = 0; i < numSectors; i++) {
                ctx.save();
                ctx.beginPath();
                ctx.moveTo(190, 190);
                ctx.arc(190, 190, 180, arc * i + angle, arc * (i + 1) + angle);
                ctx.fillStyle = i % 2 === 0 ? '#198754' : '#0dcaf0'; // Green and Info Blue
                ctx.fill();
                ctx.lineWidth = 3;
                ctx.strokeStyle = '#222';
                ctx.stroke();

                ctx.translate(190, 190);
                ctx.rotate(arc * (i + 0.5) + angle);
                ctx.fillStyle = '#fff';
                ctx.font = 'bold 14px Arial';
                ctx.textAlign = 'right';
                ctx.fillText(sectors[i].label, 160, 5);
                ctx.restore();
            }
        }

        drawWheel();
        statusText.innerText = "Next Draw Sequence Primed!";
        spinBtn.disabled = false;
        
        // Remove old listeners to prevent double firing
        const newSpinBtn = spinBtn.cloneNode(true);
        spinBtn.parentNode.replaceChild(newSpinBtn, spinBtn);

        newSpinBtn.addEventListener('click', () => {
            if (currentWinnerIndex >= preWinners.length) return;
            
            newSpinBtn.disabled = true;
            statusText.innerText = "Spinning Algorithmic Matrix...";
            
            const targetWinnerId = preWinners[currentWinnerIndex].application_id;
            let targetSectorIndex = sectors.findIndex(s => s.id === targetWinnerId);
            
            const numSectors = sectors.length;
            const arc = 2 * Math.PI / numSectors;
            
            const targetAngle = 1.5 * Math.PI - (arc * (targetSectorIndex + 0.5));
            const loops = 5 * 2 * Math.PI; // 5 full rotations for suspense
            const finalAngle = loops + targetAngle;
            
            let startTimestamp = null;
            const duration = 4000;

            function animateWheel(timestamp) {
                if (!startTimestamp) startTimestamp = timestamp;
                const elapsed = timestamp - startTimestamp;
                const progress = Math.min(elapsed / duration, 1);
                
                const easeOut = 1 - Math.pow(1 - progress, 3.5);
                angle = easeOut * finalAngle;
                
                drawWheel();

                if (progress < 1) {
                    requestAnimationFrame(animateWheel);
                } else {
                    const trueWinnerName = preWinners[currentWinnerIndex].full_name;
                    statusText.innerText = "WINNER ACQUIRED!";
                    currentWinnersSubmitted.push(targetWinnerId);
                    
                    spotsLeft--;
                    spotsDisplay.textContent = spotsLeft;
                    
                    // Elegant Popup for the winner
                    Swal.fire({
                        title: 'Congratulations!',
                        text: `Winner Selected: ${trueWinnerName}`,
                        icon: 'success',
                        confirmButtonText: spotsLeft > 0 ? 'Next Spin' : 'Finalize Sync',
                        confirmButtonColor: '#198754',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            currentWinnerIndex++;
                            if (currentWinnerIndex < preWinners.length) {
                                sectors = sectors.filter(s => s.id !== targetWinnerId);
                                if(sectors.length === 1) {
                                    sectors.push({id: 0, label: "Evaluating Pool"});
                                }
                                angle = angle % (2 * Math.PI);
                                drawWheel();
                                newSpinBtn.disabled = false;
                                statusText.innerText = "Next Draw Sequence Primed!";
                            } else {
                                statusText.innerText = "All Units Allocated! Synchronizing Matrix...";
                                document.getElementById('winnersDataInput').value = JSON.stringify(currentWinnersSubmitted);
                                setTimeout(() => {
                                    document.getElementById('hiddenWinnersForm').submit();
                                }, 1000);
                            }
                        }
                    });
                }
            }
            requestAnimationFrame(animateWheel);
        });

        wheelModal.show();
    }
</script>

<?php include '../includes/footer.php'; ?>