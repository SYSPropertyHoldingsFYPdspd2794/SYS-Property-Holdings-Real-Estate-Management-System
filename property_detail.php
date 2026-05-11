<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: property_detail.php (ROOT)
 * DESCRIPTION: Admin/Staff View. UI Mirrored from Customer but booking functions disabled.
 */

include_once 'includes/header.php';
require_once 'includes/auth_check.php';
protect_staff_admin_page($conn);

$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// FETCH FULL PROPERTY DATA
$stmt = $conn->prepare("SELECT * FROM properties WHERE property_id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    echo "<div class='container my-5 text-center'><h3>Property not found.</h3></div>";
    include_once 'includes/footer.php'; exit();
}

$is_afford = (intval($property['is_affordable']) === 1);
$banks_result = $conn->query("SELECT bank_name, interest_rate FROM banks ORDER BY interest_rate ASC");

// IMAGE MAPPING ENGINE (ROOTED PATHS)
$dbType = strtolower(trim($property['property_type']));
$rawState = trim($property['state']);
if (strtoupper($rawState) === 'PENANG') $rawState = 'Pulau Pinang';
if (strtoupper($rawState) === 'MALACCA') $rawState = 'Melaka';
$stateName = ucwords(strtolower($rawState)); 

$folder = ""; $filePrefix = "";
switch($dbType) {
    case 'commercial': $folder = "Commercial/"; $filePrefix = "Commercial"; break;
    case 'terrace': $folder = "Terrace/"; $filePrefix = "Terrace"; break;
    case 'bungalow': $folder = "Bungalow/"; $filePrefix = "Bungalow"; break;
    case 'affordable': $folder = "Apartment/"; $filePrefix = "Apartment"; break;
    case 'apartment': $folder = "Apartment/"; $filePrefix = "Apartment"; break;
    default: $folder = "Apartment/"; $filePrefix = "Apartment";
}

// Notice the difference: NO $root_prefix here because this file is in the root
$baseDir = "SYS Property Catalog/";
$finalImg = $baseDir . "placeholder.jpg"; 
$fileName = $filePrefix . " - " . $stateName;

foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
    $testPath = $baseDir . $folder . $fileName . "." . $ext;
    if (file_exists($testPath)) {
        $finalImg = $testPath;
        break;
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 overflow-hidden h-100">
                <div class="position-relative zoom-container" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageZoom">
                    <img src="<?php echo $finalImg; ?>" class="w-100" style="height: 450px; object-fit: cover;">
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-25 zoom-overlay">
                        <span class="badge bg-dark bg-opacity-75 fs-5 py-2 px-3 rounded-pill shadow"><i class="fas fa-search-plus me-2"></i>Internal View</span>
                    </div>
                </div>

                <div class="card-body p-5">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h2 class="fw-bold mb-0"><?php echo htmlspecialchars($property['project_name']); ?></h2>
                        <span class="badge <?php echo $is_afford ? 'bg-success' : 'bg-primary'; ?> px-3 py-2 fs-6 shadow-sm">
                            <?php echo $is_afford ? 'GOV AFFORDABLE' : htmlspecialchars($property['property_type']); ?>
                        </span>
                    </div>

                    <p class="fs-5 text-muted mb-4"><i class="fas fa-map-marker-alt text-danger me-2"></i> <?php echo htmlspecialchars($property['state']); ?></p>

                    <?php if ($is_afford): ?>
                        <div class="alert alert-danger border-0 shadow-sm p-4 mb-4" style="background-color: #fff5f5; border-left: 5px solid #dc3545 !important;">
                            <h5 class="fw-bold text-danger mb-2"><i class="fas fa-exclamation-triangle me-2"></i> Eligibility Restriction (Verification Required)</h5>
                            <p class="mb-0 text-dark">Subsidized unit. Staff must verify combining income is <strong>Below RM <?php echo number_format($property['applicant_income_limit']); ?></strong> during offline screening.</p>
                        </div>
                    <?php endif; ?>

                    <hr class="my-4">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <small class="d-block text-muted fw-bold mb-1">SQFT</small>
                            <span class="fs-4 fw-bold"><?php echo number_format($property['built_up_sqft']); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="d-block text-muted fw-bold mb-1">SYSTEM TOTAL UNITS</small>
                            <span class="fs-4 fw-bold text-success"><?php echo $property['total_units']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-header bg-secondary text-white p-4">
                    <h4 class="fw-bold mb-0"><i class="fas fa-calculator me-2"></i>Internal Calculator</h4>
                </div>
                <div class="card-body p-4 p-lg-5 d-flex flex-column">
                    <h3 class="text-dark fw-bold mb-4 border-bottom pb-3">Price: RM <?php echo number_format($property['price'], 2); ?></h3>
                    <input type="hidden" id="propertyPrice" value="<?php echo $property['price']; ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Bank (For Customer Counseling)</label>
                        <select id="bankSelect" class="form-select border-secondary">
                            <?php while ($bank = $banks_result->fetch_assoc()): ?>
                                <option data-rate="<?php echo $bank['interest_rate']; ?>"><?php echo htmlspecialchars($bank['bank_name']); ?> (<?php echo $bank['interest_rate']; ?>%)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Simulated Downpayment</label>
                        <select id="downpayment" class="form-select">
                            <option value="10">10%</option>
                            <option value="20">20%</option>
                            <option value="30">30%</option>
                        </select>
                    </div>

                    <div class="mb-5">
                        <label class="form-label fw-bold">Tenure: <span id="tenureLabel" class="text-primary fs-4 fw-bold">35</span> Years</label>
                        <div class="pt-3">
                            <input type="range" id="tenure" class="form-range custom-slider" value="35" min="5" max="35">
                        </div>
                    </div>

                    <div class="p-4 bg-white border border-secondary border-opacity-25 rounded text-center shadow-sm mb-4">
                        <p class="mb-1 text-muted fw-bold small">ESTIMATED INSTALLMENT</p>
                        <h2 class="text-dark fw-bold m-0" id="monthlyResult">RM 0.00</h2>
                        <small class="text-muted d-block mt-2">Rate Applied: <strong id="displayRate">0.00</strong>% (p.a)</small>
                    </div>

                    <div class="d-grid mt-auto">
                        <button class="btn btn-secondary btn-lg fw-bold py-3 shadow-sm" disabled>
                            <i class="fas fa-lock me-2"></i>Customer Apply Only
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="imageZoom" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body text-center p-0"><img src="<?php echo $finalImg; ?>" class="img-fluid rounded shadow-lg"></div>
        </div>
    </div>
</div>

<style>
    .zoom-overlay { opacity: 0; transition: 0.3s; }
    .zoom-container:hover .zoom-overlay { opacity: 1; }
    .custom-slider { -webkit-appearance: none; width: 100%; height: 12px; border-radius: 6px; background: #ced4da; }
    .custom-slider::-webkit-slider-thumb { -webkit-appearance: none; width: 32px; height: 32px; border-radius: 50%; background: #6c757d; border: 4px solid #fff; cursor: pointer; box-shadow: 0 0 10px rgba(0,0,0,0.2); }
</style>

<script>
function updateCalc() {
    const p = parseFloat(document.getElementById('propertyPrice').value);
    const bank = document.getElementById('bankSelect');
    const r = parseFloat(bank.options[bank.selectedIndex].getAttribute('data-rate'));
    const d = parseFloat(document.getElementById('downpayment').value) / 100;
    const y = parseInt(document.getElementById('tenure').value);
    document.getElementById('tenureLabel').innerText = y;
    document.getElementById('displayRate').innerText = r.toFixed(2);
    
    const monthlyRate = (r / 100) / 12;
    const n = y * 12;
    const loan = p * (1 - d);
    const result = (loan * monthlyRate * Math.pow(1+monthlyRate, n)) / (Math.pow(1+monthlyRate, n) - 1);
    document.getElementById('monthlyResult').innerText = "RM " + result.toLocaleString('en-US', {minimumFractionDigits: 2});
}
document.querySelectorAll('.form-select, .form-range').forEach(el => el.addEventListener('input', updateCalc));
window.onload = updateCalc;
</script>

<?php include_once 'includes/footer.php'; ?>