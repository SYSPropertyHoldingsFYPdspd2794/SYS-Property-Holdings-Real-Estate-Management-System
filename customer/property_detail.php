<?php
/**
 * PROJECT: SYS Property Holdings
 * MODULE: Customer - Property Details
 * DESCRIPTION: Features dynamic loan calculator, robust image mapping, and interactive modal zoom.
 */

include_once '../includes/header.php';
include_once '../includes/header.php';
/** @var string $root_prefix */
/** @var mysqli $conn */
require_once '../includes/auth_check.php';
require_once '../includes/auth_check.php';
protect_customer_page('CUSTOMER', $conn);

$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT * FROM properties WHERE property_id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    echo "<div class='container my-5 text-center'><h3 class='text-danger'>Property not found.</h3></div>";
    include_once '../includes/footer.php';
    exit();
}

$rate_stmt = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'BASE_INTEREST_RATE'");
$rate = ($rate_stmt && $rate_stmt->num_rows > 0) ? $rate_stmt->fetch_assoc()['setting_value'] : 3.5;

// Safety Checks for Missing Data
$sqft_display = isset($property['built_up_sqft']) && !empty($property['built_up_sqft']) ? number_format($property['built_up_sqft']) : "N/A";
$income_limit_display = "N/A";
if (isset($property['applicant_income_limit']) && $property['applicant_income_limit'] > 0) {
    $income_limit_display = "Below RM " . number_format($property['applicant_income_limit']);
}
$availableUnits = isset($property['available_units']) ? $property['available_units'] : (isset($property['total_units']) ? $property['total_units'] : 0);

// --- IMAGE MAPPING LOGIC ---
$dbType = strtolower(trim($property['property_type']));
$rawState = trim($property['state']);

if (strtoupper($rawState) === 'PENANG') $rawState = 'Pulau Pinang';
if (strtoupper($rawState) === 'MALACCA') $rawState = 'Melaka';

$stateName = ucwords(strtolower($rawState)); 
$folder = ""; $filePrefix = "";

switch($dbType) {
    case 'commercial': $folder = "Commercial/"; $filePrefix = "Commercial"; break;
    case 'standard': $folder = "Terrace/"; $filePrefix = "Terrace"; break;
    case 'affordable': $folder = "Apartment/"; $filePrefix = "Apartment"; break;
    case 'bungalow': $folder = "Bungalow/"; $filePrefix = "Bungalow"; break;
    case 'apartment': $folder = "Apartment/"; $filePrefix = "Apartment"; break;
    case 'terrace': $folder = "Terrace/"; $filePrefix = "Terrace"; break;
    default: $folder = ucfirst($dbType) . "/"; $filePrefix = ucfirst($dbType);
}

$baseDir = $root_prefix . "SYS Property Catalog/";
$fileName = $filePrefix . " - " . $stateName;
$finalImg = $baseDir . "placeholder.jpg"; 

$exts = ['jpg', 'jpeg', 'png', 'webp', 'JPG', 'JPEG', 'PNG', 'WEBP'];
foreach ($exts as $ext) {
    $testPath = $baseDir . $folder . $fileName . "." . $ext;
    if (file_exists($testPath)) {
        $finalImg = $testPath;
        break;
    }
}
?>

<div class="container my-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="properties.php">Catalog</a></li>
            <li class="breadcrumb-item active fw-bold" aria-current="page"><?php echo htmlspecialchars($property['project_name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 overflow-hidden h-100">
                
                <div class="position-relative" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageZoomModal">
                    <img src="<?php echo htmlspecialchars($finalImg); ?>" 
                         class="w-100 image-zoom-target" 
                         alt="<?php echo htmlspecialchars($property['project_name']); ?>" 
                         style="height: 400px; object-fit: cover; transition: opacity 0.3s;">
                    
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-25 zoom-overlay" style="opacity: 0; transition: opacity 0.3s;">
                        <span class="badge bg-dark bg-opacity-75 fs-5 py-2 px-3 rounded-pill shadow">
                            <i class="fas fa-search-plus me-2"></i>Click to Enlarge
                        </span>
                    </div>
                </div>
                
                <div class="card-body p-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($property['project_name']); ?></h2>
                        <span class="badge bg-primary fs-6 px-3 py-2 shadow-sm"><?php echo htmlspecialchars($property['property_type']); ?></span>
                    </div>
                    
                    <p class="fs-5 text-muted mb-4"><i class="fas fa-map-marker-alt me-2 text-danger"></i><?php echo htmlspecialchars($property['state']); ?></p>
                    <hr class="my-4">
                    
                    <div class="row text-center mb-4">
                        <div class="col-4 border-end">
                            <small class="d-block text-muted text-uppercase fw-bold mb-1">SQFT</small>
                            <span class="fs-5 fw-bold text-dark"><?php echo $sqft_display; ?></span>
                        </div>
                        <div class="col-4 border-end">
                            <small class="d-block text-muted text-uppercase fw-bold mb-1">UNITS</small>
                            <span class="fs-5 fw-bold text-dark"><?php echo $availableUnits; ?></span>
                        </div>
                        <div class="col-4">
                            <small class="d-block text-muted text-uppercase fw-bold mb-1">INCOME LIMIT</small>
                            <span class="fs-6 fw-bold <?php echo ($income_limit_display !== 'N/A') ? 'text-danger' : 'text-dark'; ?>">
                                <?php echo $income_limit_display; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-header bg-dark text-white p-4">
                    <h4 class="fw-bold mb-0"><i class="fas fa-calculator me-2 text-warning"></i>Loan Calculator</h4>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <h3 class="text-success fw-bold mb-4 border-bottom border-2 pb-3">Price: RM <?php echo number_format($property['price'], 2); ?></h3>
                    
                    <input type="hidden" id="propertyPrice" value="<?php echo $property['price']; ?>">
                    <input type="hidden" id="interestRate" value="<?php echo $rate; ?>">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Downpayment (%)</label>
                        <select id="downpayment" class="form-select form-select-lg">
                            <option value="10">10% (Standard)</option>
                            <option value="20">20%</option>
                            <option value="30">30%</option>
                        </select>
                    </div>
                    
                    <div class="mb-5">
                        <label class="form-label fw-bold">Loan Tenure: <span id="tenureLabel" class="text-primary">35</span> Years</label>
                        <input type="range" id="tenure" class="form-range" value="35" min="5" max="35">
                    </div>
                    
                    <div class="p-4 bg-white border border-primary border-opacity-25 rounded text-center shadow-sm mb-4">
                        <p class="mb-2 text-muted fw-bold text-uppercase small">Estimated Monthly Installment</p>
                        <h2 class="text-primary fw-bold m-0" id="monthlyResult">RM 0.00</h2>
                        <small class="text-muted d-block mt-2">Interest Rate: <?php echo htmlspecialchars($rate); ?>% (p.a)</small>
                    </div>
                    
                    <div class="d-grid mt-auto">
                        <?php if ($property['property_type'] === 'AFFORDABLE'): ?>
                            <a href="apply_affordable.php?id=<?php echo $property['property_id']; ?>" class="btn btn-success btn-lg fw-bold py-3 shadow-sm">
                                Apply for Gov Housing
                            </a>
                        <?php else: ?>
                            <a href="book_appointment.php?id=<?php echo $property['property_id']; ?>" class="btn btn-primary btn-lg fw-bold py-3 shadow-sm">
                                Book Showroom Viewing
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="imageZoomModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-header border-0">
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-0">
        <img src="<?php echo htmlspecialchars($finalImg); ?>" class="img-fluid rounded shadow-lg" alt="Zoomed Property Image">
      </div>
    </div>
  </div>
</div>

<style>
    /* CSS logic to reveal the 'Click to Enlarge' hint when hovered */
    .position-relative:hover .zoom-overlay {
        opacity: 1 !important;
    }
    .position-relative:hover .image-zoom-target {
        opacity: 0.85;
    }
</style>

<script>
function calculateLoan() {
    const price = parseFloat(document.getElementById('propertyPrice').value);
    const ratePercentage = parseFloat(document.getElementById('interestRate').value);
    const monthlyRate = (ratePercentage / 100) / 12;
    const downpaymentPerc = parseFloat(document.getElementById('downpayment').value) / 100;
    
    const tenureYears = parseInt(document.getElementById('tenure').value);
    document.getElementById('tenureLabel').innerText = tenureYears;
    const tenureMonths = tenureYears * 12;
    
    const loanAmount = price - (price * downpaymentPerc);
    
    if (loanAmount <= 0 || tenureMonths <= 0 || isNaN(loanAmount) || isNaN(tenureMonths)) {
        document.getElementById('monthlyResult').innerText = "RM 0.00";
        return;
    }
    
    let monthlyInstallment = 0;
    if (monthlyRate > 0) {
        monthlyInstallment = (loanAmount * monthlyRate * Math.pow(1 + monthlyRate, tenureMonths)) / (Math.pow(1 + monthlyRate, tenureMonths) - 1);
    } else {
        monthlyInstallment = loanAmount / tenureMonths;
    }
    
    document.getElementById('monthlyResult').innerText = "RM " + monthlyInstallment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

document.getElementById('downpayment').addEventListener('change', calculateLoan);
document.getElementById('tenure').addEventListener('input', calculateLoan);
window.addEventListener('load', calculateLoan);
</script>

<?php include_once '../includes/footer.php'; ?>