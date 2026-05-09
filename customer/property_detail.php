<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/property_detail.php
 * DESCRIPTION: Specific property details with loan calculator (US17, US21, US22).
 */

include_once '../includes/header.php';

include_once '../includes/header.php';
/** @var string $root_prefix */
/** @var mysqli $conn */
require_once '../includes/auth_check.php';

require_once '../includes/auth_check.php';
protect_customer_page('CUSTOMER', $conn);

// Get and Sanitize ID
$pid = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch Data
$stmt = $conn->prepare("SELECT * FROM properties WHERE property_id = ?");
$stmt->bind_param("i", $pid);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    header("Location: properties.php");
    exit();
}

// Fetch Interest Rate from Settings (US21)
$res_rate = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'BASE_INTEREST_RATE'");
$base_rate = ($res_rate && $res_rate->num_rows > 0) ? $res_rate->fetch_assoc()['setting_value'] : 3.5;

/* =========================================================
   IMAGE SCANNER LOGIC (Synced with properties.php)
   ========================================================= */
$typeRaw = strtoupper(trim($property['property_type']));
$typeName = ucfirst(strtolower($typeRaw));
$stateName = ucfirst(strtolower(trim($property['state'])));
$baseName = $typeName . " - " . $stateName;

$folderMap = ['APARTMENT'=>'1. APARTMENT/', 'BUNGALOW'=>'2. BUNGALOW/', 'COMMERCIAL'=>'3. COMMERCIAL/', 'TERRACE'=>'4. TERRACE/'];
$subFolder = isset($folderMap[$typeRaw]) ? $folderMap[$typeRaw] : "";
$extensions = ['jpg','jpeg','png','JPG','JPEG','PNG'];
$finalImg = $root_prefix . "uploads/properties/placeholder.jpg";

foreach ($extensions as $ext) {
    $p1 = $root_prefix . "uploads/properties/" . $subFolder . $baseName . "." . $ext;
    if (file_exists($p1)) { $finalImg = $p1; break; }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <img src="<?php echo $finalImg; ?>" class="img-fluid" style="height: 450px; width: 100%; object-fit: cover;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="fw-bold mb-0"><?php echo htmlspecialchars($property['project_name']); ?></h2>
                        <span class="badge bg-primary px-3 py-2 fs-6"><?php echo $typeName; ?></span>
                    </div>
                    <p class="text-muted fs-5"><i class="fas fa-map-marker-alt text-danger me-2"></i><?php echo $stateName; ?></p>
                    
                    <hr class="my-4">
                    
                    <div class="row g-4 text-center">
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block fw-bold">SQFT</small>
                            <span class="fs-5 fw-bold"><?php echo number_format($property['built_up_sqft']); ?></span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block fw-bold">UNITS</small>
                            <span class="fs-5 fw-bold"><?php echo $property['total_units']; ?></span>
                        </div>
                        <div class="col-12 col-md-6">
                            <small class="text-muted d-block fw-bold">INCOME LIMIT</small>
                            <span class="fs-5 fw-bold text-danger">Below RM <?php echo number_format($property['applicant_income_limit']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 100px;">
                <div class="card-header bg-dark text-white p-4">
                    <h4 class="mb-0"><i class="fas fa-calculator me-2 text-warning"></i>Loan Calculator</h4>
                </div>
                <div class="card-body p-4">
                    <h3 class="text-success fw-bold mb-4">Price: RM <?php echo number_format($property['price'], 2); ?></h3>
                    <input type="hidden" id="rawPrice" value="<?php echo $property['price']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Downpayment (%)</label>
                        <select class="form-select" id="downPerc" onchange="calculate()">
                            <option value="10">10%</option>
                            <option value="20">20%</option>
                            <option value="30">30%</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tenure: <span id="tenureLabel" class="text-primary">35</span> Years</label>
                        <input type="range" class="form-range" min="5" max="35" id="tenureRange" value="35" oninput="calculate()">
                    </div>

                    <div class="mb-4 text-center p-4 bg-light rounded-4">
                        <p class="text-muted small fw-bold mb-1">MONTHLY REPAYMENT</p>
                        <h2 class="text-success fw-bold mb-0" id="resultLabel">RM 0.00</h2>
                        <small class="text-muted italic mt-2 d-block">Interest Rate: <?php echo $base_rate; ?>% (p.a)</small>
                        <input type="hidden" id="intRate" value="<?php echo $base_rate; ?>">
                    </div>

                    <a href="booking.php?id=<?php echo $pid; ?>" class="btn btn-primary w-100 btn-lg rounded-pill fw-bold">Book Appointment</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculate() {
    const price = parseFloat(document.getElementById('rawPrice').value);
    const downPerc = parseFloat(document.getElementById('downPerc').value) / 100;
    const years = parseInt(document.getElementById('tenureRange').value);
    const annualRate = parseFloat(document.getElementById('intRate').value) / 100;
    
    document.getElementById('tenureLabel').innerText = years;
    
    const principal = price * (1 - downPerc);
    const monthlyRate = annualRate / 12;
    const n = years * 12;
    
    const monthly = (principal * monthlyRate * Math.pow(1 + monthlyRate, n)) / (Math.pow(1 + monthlyRate, n) - 1);
    document.getElementById('resultLabel').innerText = "RM " + monthly.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
window.onload = calculate;
</script>

<?php include_once $root_prefix . 'includes/footer.php'; ?>