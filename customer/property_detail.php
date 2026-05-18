<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/property_detail.php
 * DESCRIPTION: Customer view with correct top layout, fixed __DIR__ absolute path fallback for shared JPG floorplans.
 */

include_once '../includes/header.php';
require_once '../includes/auth_check.php';
protect_customer_page('CUSTOMER', $conn);

$account_id = $_SESSION['account_id'];
$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// WISHLIST TOGGLE LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_wishlist'])) {
    $chk = $conn->prepare("SELECT wishlist_id FROM wishlists WHERE customer_id = ? AND property_id = ?");
    $chk->bind_param("ii", $account_id, $property_id);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        $del = $conn->prepare("DELETE FROM wishlists WHERE customer_id = ? AND property_id = ?");
        $del->bind_param("ii", $account_id, $property_id);
        $del->execute();
    } else {
        $ins = $conn->prepare("INSERT INTO wishlists (customer_id, property_id) VALUES (?, ?)");
        $ins->bind_param("ii", $account_id, $property_id);
        $ins->execute();
    }
    header("Location: property_detail.php?id=" . $property_id);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM properties WHERE property_id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    echo "<div class='container my-5 text-center'><h3>Property not found.</h3></div>";
    include_once '../includes/footer.php'; exit();
}

$is_wish_stmt = $conn->prepare("SELECT wishlist_id FROM wishlists WHERE customer_id = ? AND property_id = ?");
$is_wish_stmt->bind_param("ii", $account_id, $property_id);
$is_wish_stmt->execute();
$is_wishlisted = ($is_wish_stmt->get_result()->num_rows > 0);

$is_afford = (intval($property['is_affordable']) === 1);
$banks_result = $conn->query("SELECT bank_name, interest_rate FROM banks ORDER BY interest_rate ASC");

// CATALOG MAIN IMAGE RESOLUTION
$dbType = strtolower(trim($property['property_type']));
$rawState = trim($property['state']);
if (strtoupper($rawState) === 'PENANG') $rawState = 'Pulau Pinang';
if (strtoupper($rawState) === 'MALACCA') $rawState = 'Melaka';
$stateName = ucwords(strtolower($rawState)); 

$folder = ($dbType === 'commercial') ? "Commercial/" : (($dbType === 'terrace') ? "Terrace/" : (($dbType === 'bungalow') ? "Bungalow/" : "Apartment/"));
$filePrefix = ucfirst($dbType);

$baseDir = $root_prefix . "SYS Property Catalog/";
$finalImg = $baseDir . "placeholder.jpg"; 
$fileName = $filePrefix . " - " . $stateName;

foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
    $testPath = $baseDir . $folder . $fileName . "." . $ext;
    if (file_exists("../" . str_replace('../', '', $testPath))) { $finalImg = $testPath; break; }
}

// ------------------------------------------------------------------
// PROPERTIES DESCRIPTION LOGIC (FIXED DYNAMIC DETECTOR)
// ------------------------------------------------------------------

// 1. FLOOR PLAN PATH (Strictly couples Affordable housing to Affordable_Floor_Plan.jpg without disk checks)
if ($is_afford || $dbType === 'affordable') {
    $floorPlanName = "Affordable_Floor_Plan.jpg";
} else {
    $floorPlanName = ucfirst($dbType) . "_Floor_Plan.jpg";
}

// Smart Environment Detection (Handles both local testing and server deployment flawlessly)
if ($_SERVER['HTTP_HOST'] === 'localhost:3000' || $_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    $finalFloorPlan = $baseDir . $floorPlanName; 
} else {
    $finalFloorPlan = "../" . $baseDir . $floorPlanName; 
}

// 2. INTERNAL LAYOUT (Numeric Specification Matrix)
$layoutHtml = "";
$sqft = intval($property['built_up_sqft']);

if ($is_afford) {
    $layoutHtml = '
        <div class="row text-center g-4">
            <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-bed fa-2x text-success mb-2"></i><h5 class="fw-bold mb-0">3 Bedrooms</h5></div></div>
            <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-bath fa-2x text-success mb-2"></i><h5 class="fw-bold mb-0">2 Bathrooms</h5></div></div>
            <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-couch fa-2x text-muted mb-2"></i><h5 class="fw-bold mb-0">1 Living Hall</h5></div></div>
            <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-utensils fa-2x text-muted mb-2"></i><h5 class="fw-bold mb-0">1 Kitchen</h5></div></div>
        </div>';
} else {
    if ($dbType === 'commercial') {
        $layoutHtml = '
            <div class="row text-center g-4">
                <div class="col-md-4 col-12"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-store fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">2 Open Layout Workspaces</h5></div></div>
                <div class="col-md-4 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-briefcase fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">1 Manager Office</h5></div></div>
                <div class="col-md-4 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-toilet fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">2 Restrooms</h5></div></div>
            </div>';
    } else {
        if ($dbType === 'bungalow') {
            $layoutHtml = '
                <div class="row text-center g-4">
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-bed fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">6 Bedrooms</h5></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-bath fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">5 Bathrooms</h5></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-swimming-pool fa-2x text-info mb-2"></i><h5 class="fw-bold mb-0">1 Private Pool</h5></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-car-side fa-2x text-muted mb-2"></i><h5 class="fw-bold mb-0">3 Car Porches</h5></div></div>
                </div>';
        } elseif ($dbType === 'apartment') {
            $layoutHtml = '
                <div class="row text-center g-4">
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-bed fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">3 Bedrooms</h5></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-bath fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">2 Bathrooms</h5></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-dumbbell fa-2x text-info mb-2"></i><h5 class="fw-bold mb-0">1 Gym Facility</h5></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-water fa-2x text-info mb-2"></i><h5 class="fw-bold mb-0">1 Public Pool</h5></div></div>
                </div>';
        } else { 
            $rooms = ($sqft >= 1800) ? "5" : "4";
            $baths = ($sqft >= 1800) ? "4" : "3";
            $layoutHtml = '
                <div class="row text-center g-4">
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-bed fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">'.$rooms.' Bedrooms</h5></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-bath fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">'.$baths.' Bathrooms</h5></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-warehouse fa-2x text-muted mb-2"></i><h5 class="fw-bold mb-0">2 Car Porches</h5></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm"><i class="fas fa-box-open fa-2x text-muted mb-2"></i><h5 class="fw-bold mb-0">1 Store Room</h5></div></div>
                </div>';
        }
    }
}

// 3. PROXIMITY (WITH PRECISE KM)
$proximityHtml = "";
switch ($property['state']) {
    case 'Johor':
        $proximityHtml = '
            <div class="row g-4 fs-5 text-secondary">
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-university text-danger fa-fw me-3"></i> <span>Universiti Teknologi Malaysia (UTM) <strong class="text-dark">3.2 KM</strong></span></div></div>
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-shopping-cart text-primary fa-fw me-3"></i> <span>AEON Mall Tebrau City <strong class="text-dark">5.5 KM</strong></span></div></div>
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-hospital text-success fa-fw me-3"></i> <span>Sultanah Aminah Hospital <strong class="text-dark">8.1 KM</strong></span></div></div>
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-plane text-warning fa-fw me-3"></i> <span>Senai International Airport <strong class="text-dark">18.0 KM</strong></span></div></div>
            </div>';
        break;
    case 'Kuala Lumpur':
        $proximityHtml = '
            <div class="row g-4 fs-5 text-secondary">
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-train text-danger fa-fw me-3"></i> <span>KLCC LRT Station <strong class="text-dark">1.2 KM</strong></span></div></div>
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-shopping-bag text-primary fa-fw me-3"></i> <span>Pavilion Bukit Bintang <strong class="text-dark">2.0 KM</strong></span></div></div>
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-school text-success fa-fw me-3"></i> <span>International School of KL <strong class="text-dark">4.3 KM</strong></span></div></div>
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-tree text-warning fa-fw me-3"></i> <span>Perdana Botanical Garden <strong class="text-dark">5.0 KM</strong></span></div></div>
            </div>';
        break;
    case 'Penang':
        $proximityHtml = '
            <div class="row g-4 fs-5 text-secondary">
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-archway text-danger fa-fw me-3"></i> <span>Penang Bridge <strong class="text-dark">4.5 KM</strong></span></div></div>
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-industry text-primary fa-fw me-3"></i> <span>Bayan Lepas FIZ <strong class="text-dark">6.2 KM</strong></span></div></div>
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-shopping-cart text-success fa-fw me-3"></i> <span>Queensbay Mall <strong class="text-dark">3.8 KM</strong></span></div></div>
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-hospital-alt text-warning fa-fw me-3"></i> <span>Penang General Hospital <strong class="text-dark">7.1 KM</strong></span></div></div>
            </div>';
        break;
    case 'Selangor':
        $proximityHtml = '
            <div class="row g-4 fs-5 text-secondary">
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-shopping-bag text-danger fa-fw me-3"></i> <span>Sunway Pyramid Mall <strong class="text-dark">4.2 KM</strong></span></div></div>
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-graduation-cap text-primary fa-fw me-3"></i> <span>Monash University <strong class="text-dark">3.5 KM</strong></span></div></div>
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-clinic-medical text-success fa-fw me-3"></i> <span>Subang Medical Centre <strong class="text-dark">5.0 KM</strong></span></div></div>
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-subway text-warning fa-fw me-3"></i> <span>Nearest LRT Station <strong class="text-dark">1.8 KM</strong></span></div></div>
            </div>';
        break;
    default:
        $proximityHtml = '
            <div class="row g-4 fs-5 text-secondary">
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-school text-danger fa-fw me-3"></i> <span>Regional Secondary School <strong class="text-dark">1.5 KM</strong></span></div></div>
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-store text-primary fa-fw me-3"></i> <span>Local Commercial Complex <strong class="text-dark">2.3 KM</strong></span></div></div>
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-clinic-medical text-success fa-fw me-3"></i> <span>Community Health Center <strong class="text-dark">3.0 KM</strong></span></div></div>
                <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-bus text-warning fa-fw me-3"></i> <span>Central Bus Terminal <strong class="text-dark">4.2 KM</strong></span></div></div>
            </div>';
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 overflow-hidden h-100">
                <div class="position-relative zoom-container" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageZoom">
                    <img src="<?php echo $finalImg; ?>" class="w-100" style="height: 400px; object-fit: cover;">
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-25 zoom-overlay">
                        <span class="badge bg-dark bg-opacity-75 fs-5 py-2 px-3 rounded-pill shadow"><i class="fas fa-search-plus me-2"></i>Click to Enlarge</span>
                    </div>
                </div>

                <div class="card-body p-5">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <h2 class="fw-bold mb-0"><?php echo htmlspecialchars($property['project_name']); ?></h2>
                            <form method="POST" class="m-0">
                                <button type="submit" name="toggle_wishlist" class="btn <?php echo $is_wishlisted ? 'btn-danger' : 'btn-outline-danger'; ?> rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                                    <i class="<?php echo $is_wishlisted ? 'fas' : 'far'; ?> fa-heart fs-5"></i>
                                </button>
                            </form>
                        </div>
                        <span class="badge <?php echo $is_afford ? 'bg-success' : 'bg-primary'; ?> px-3 py-2 fs-6 shadow-sm">
                            <?php echo $is_afford ? 'GOV AFFORDABLE' : htmlspecialchars($property['property_type']); ?>
                        </span>
                    </div>

                    <p class="fs-5 text-muted mb-4"><i class="fas fa-map-marker-alt text-danger me-2"></i> <?php echo htmlspecialchars($property['state']); ?></p>

                    <?php if ($is_afford): ?>
                        <div class="alert alert-danger border-0 shadow-sm p-4 mb-4" style="background-color: #fff5f5; border-left: 5px solid #dc3545 !important;">
                            <h5 class="fw-bold text-danger mb-2"><i class="fas fa-exclamation-triangle me-2"></i> Eligibility Restriction</h5>
                            <p class="mb-0 text-dark">This is a government-subsidized unit. Applicants must have a combined household income <strong>Below RM <?php echo number_format($property['income_limit_rm'] ?? 0); ?></strong>. Documents must be verified offline.</p>
                        </div>
                    <?php endif; ?>

                    <hr class="my-4">
                    <div class="row text-center mb-4">
                        <div class="col-4 border-end">
                            <small class="d-block text-muted fw-bold mb-1">SQFT</small>
                            <span class="fs-4 fw-bold"><?php echo number_format($property['built_up_sqft']); ?></span>
                        </div>
                        <div class="col-4 border-end">
                            <small class="d-block text-muted fw-bold mb-1">AVAILABLE UNITS</small>
                            <span class="fs-4 fw-bold text-success"><?php echo $property['total_units']; ?></span>
                        </div>
                        <div class="col-4">
                            <small class="d-block text-muted fw-bold mb-1">PROPERTY CODE</small>
                            <span class="fs-5 fw-bold text-dark"><?php echo htmlspecialchars($property['property_code']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-header bg-dark text-white p-4">
                    <h4 class="fw-bold mb-0"><i class="fas fa-calculator me-2 text-warning"></i>Payment Estimator</h4>
                </div>
                <div class="card-body p-4 p-lg-5 d-flex flex-column">
                    <h3 class="text-success fw-bold mb-4 border-bottom pb-3">Price: RM <?php echo number_format($property['price'], 2); ?></h3>
                    <input type="hidden" id="propertyPrice" value="<?php echo $property['price']; ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Finance Bank</label>
                        <select id="bankSelect" class="form-select border-primary">
                            <?php while ($bank = $banks_result->fetch_assoc()): ?>
                                <option data-rate="<?php echo $bank['interest_rate']; ?>"><?php echo htmlspecialchars($bank['bank_name']); ?> (<?php echo $bank['interest_rate']; ?>%)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Downpayment (10% - 30%)</label>
                        <select id="downpayment" class="form-select">
                            <option value="10">10% (Minimum)</option>
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

                    <div class="p-4 bg-white border border-primary border-opacity-25 rounded text-center shadow-sm mb-4">
                        <p class="mb-1 text-muted fw-bold small">MONTHLY REPAYMENT</p>
                        <h2 class="text-primary fw-bold m-0" id="monthlyResult">RM 0.00</h2>
                        <small class="text-muted d-block mt-2">Rate Applied: <strong id="displayRate">0.00</strong>% (p.a)</small>
                    </div>

                    <div class="d-grid mt-auto">
                        <?php if ($is_afford): ?>
                            <a href="apply_affordable.php?id=<?php echo $property_id; ?>" class="btn btn-success btn-lg fw-bold py-3 shadow">Submit Application</a>
                        <?php else: ?>
                            <a href="book_appointment.php?id=<?php echo $property_id; ?>" class="btn btn-primary btn-lg fw-bold py-3 shadow">Book Viewing</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            
            <div class="card border-0 bg-light shadow-sm rounded-4 p-4 p-md-5 mb-5 reveal-card">
                <h3 class="fw-bold text-dark mb-4 border-bottom border-primary border-2 pb-3"><i class="fas fa-drafting-compass text-primary me-2"></i> Architectural Floor Plan</h3>
                <div class="text-center bg-white p-4 border rounded-3 shadow-sm">
                    <img src="<?php echo htmlspecialchars($finalFloorPlan); ?>" class="img-fluid rounded" alt="Floor Plan" style="max-height: 500px; object-fit: contain;">
                </div>
            </div>

            <div class="card border-0 bg-light shadow-sm rounded-4 p-4 p-md-5 mb-5 reveal-card">
                <h3 class="fw-bold text-dark mb-4 border-bottom border-primary border-2 pb-3"><i class="fas fa-th-large text-primary me-2"></i> Internal Layout Specification</h3>
                <div class="p-2"><?php echo $layoutHtml; ?></div>
            </div>

            <div class="card border-0 bg-light shadow-sm rounded-4 p-4 p-md-5 mb-5 reveal-card">
                <h3 class="fw-bold text-dark mb-4 border-bottom border-primary border-2 pb-3"><i class="fas fa-map-signs text-primary me-2"></i> Regional Proximity & Neighborhood</h3>
                <div class="p-3 bg-white border rounded shadow-sm mt-3">
                    <?php echo $proximityHtml; ?>
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
    /* ANIMATION: DRIFT UP */
    @keyframes driftUp {
        0% { opacity: 0; transform: translateY(60px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .reveal-card { animation: driftUp 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards; }
    
    .zoom-overlay { opacity: 0; transition: 0.3s; }
    .zoom-container:hover .zoom-overlay { opacity: 1; }
    
    /* CUSTOM RIGID BLUE SLIDER */
    .custom-slider { -webkit-appearance: none; width: 100%; height: 12px; border-radius: 6px; background: #ced4da; outline: none; }
    .custom-slider::-webkit-slider-thumb { -webkit-appearance: none; width: 32px; height: 32px; border-radius: 50%; background: #0d6efd; border: 4px solid #fff; cursor: pointer; box-shadow: 0 0 10px rgba(13,110,253,0.5); }
    .custom-slider::-moz-range-thumb { width: 32px; height: 32px; border-radius: 50%; background: #0d6efd; border: 4px solid #fff; cursor: pointer; box-shadow: 0 0 10px rgba(13,110,253,0.5); }
</style>

<script { sandbox: 'allow-scripts' }>
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

<?php include_once '../includes/footer.php'; ?>