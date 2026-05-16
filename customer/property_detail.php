<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/property_detail.php
 * DESCRIPTION: Complete property detail with structural icons, local floor plans, and scroll drift animation.
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

// MAIN IMAGE LOGIC
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

// --- DYNAMIC CONTENT DELEGATION (NO EXTRA COLUMNS) ---

// A. Floor Plan Matrix (Strictly fetching from directory architecture)
if ($is_afford) {
    $floorPlanName = "Floor_Plan_Affordable.webp";
} else {
    switch ($dbType) {
        case 'commercial': $floorPlanName = "Floor_Plan_Commercial.webp"; break;
        case 'terrace': $floorPlanName = "Floor_Plan_Terrace.webp"; break;
        case 'bungalow': $floorPlanName = "Floor_Plan_Bungalow.webp"; break;
        default: $floorPlanName = "Floor_Plan_Apartment.webp"; break;
    }
}
$finalFloorPlan = $baseDir . $floorPlanName;

// B. Internal Layout Algorithm with HTML Icon Tags
$layoutSpecsHtml = "";
if ($is_afford) {
    // Rigid compliance: Government affordable housing is permanently 3 Bedrooms, 2 Bathrooms
    $layoutSpecsHtml = '
        <div class="row text-center g-3">
            <div class="col-6 col-sm-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-bed fa-2x text-success mb-2"></i><h6 class="fw-bold m-0">3 Bedrooms</h6></div></div>
            <div class="col-6 col-sm-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-bath fa-2x text-success mb-2"></i><h6 class="fw-bold m-0">2 Bathrooms</h6></div></div>
            <div class="col-6 col-sm-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-couch fa-2x text-muted mb-2"></i><h6 class="fw-bold m-0">Family Hall</h6></div></div>
            <div class="col-6 col-sm-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-utensils fa-2x text-muted mb-2"></i><h6 class="fw-bold m-0">Standard Kitchen</h6></div></div>
        </div>';
} else {
    if ($dbType === 'commercial') {
        $layoutSpecsHtml = '
            <div class="row text-center g-3">
                <div class="col-6 col-sm-4"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-door-open fa-2x text-primary mb-2"></i><h6 class="fw-bold m-0">Open Workspace</h6></div></div>
                <div class="col-6 col-sm-4"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-briefcase fa-2x text-primary mb-2"></i><h6 class="fw-bold m-0">1 Executive Office</h6></div></div>
                <div class="col-6 col-sm-4"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-toilet fa-2x text-primary mb-2"></i><h6 class="fw-bold m-0">2 Washrooms</h6></div></div>
                <div class="col-12"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-boxes fa-2x text-muted mb-2"></i><h6 class="fw-bold m-0">High-Ceiling Stock Storage Zone</h6></div></div>
            </div>';
    } else {
        $sqft = intval($property['built_up_sqft']);
        if ($dbType === 'bungalow') {
            $layoutSpecsHtml = '
                <div class="row text-center g-3">
                    <div class="col-6 col-md-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-bed fa-2x text-primary mb-2"></i><h6 class="fw-bold m-0">5 Bedrooms</h6></div></div>
                    <div class="col-6 col-md-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-bath fa-2x text-primary mb-2"></i><h6 class="fw-bold m-0">5 Bathrooms</h6></div></div>
                    <div class="col-6 col-md-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-swimming-pool fa-2x text-info mb-2"></i><h6 class="fw-bold m-0">Private Pool</h6></div></div>
                    <div class="col-6 col-md-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-book-reader fa-2x text-muted mb-2"></i><h6 class="fw-bold m-0">1 Study Room</h6></div></div>
                </div>';
        } elseif ($dbType === 'apartment') {
            // Standard Apartment including public entertainment amenities
            $layoutSpecsHtml = '
                <div class="row text-center g-3">
                    <div class="col-6 col-md-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-bed fa-2x text-primary mb-2"></i><h6 class="fw-bold m-0">3 Bedrooms</h6></div></div>
                    <div class="col-6 col-md-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-bath fa-2x text-primary mb-2"></i><h6 class="fw-bold m-0">2 Bathrooms</h6></div></div>
                    <div class="col-6 col-md-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-dumbbell fa-2x text-info mb-2"></i><h6 class="fw-bold m-0">Clubhouse Gym</h6></div></div>
                    <div class="col-6 col-md-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-water fa-2x text-info mb-2"></i><h6 class="fw-bold m-0">Public Pool</h6></div></div>
                </div>';
        } else { // Terrace / Standard housings
            if ($sqft >= 1500) {
                $layoutSpecsHtml = '
                    <div class="row text-center g-3">
                        <div class="col-6 col-sm-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-bed fa-2x text-primary mb-2"></i><h6 class="fw-bold m-0">5 Bedrooms</h6></div></div>
                        <div class="col-6 col-sm-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-bath fa-2x text-primary mb-2"></i><h6 class="fw-bold m-0">4 Bathrooms</h6></div></div>
                        <div class="col-6 col-sm-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-shield-alt fa-2x text-muted mb-2"></i><h6 class="fw-bold m-0">Gated Area</h6></div></div>
                        <div class="col-6 col-sm-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-blender fa-2x text-muted mb-2"></i><h6 class="fw-bold m-0">Extended Kitchen</h6></div></div>
                    </div>';
            } else {
                $layoutSpecsHtml = '
                    <div class="row text-center g-3">
                        <div class="col-6 col-sm-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-bed fa-2x text-primary mb-2"></i><h6 class="fw-bold m-0">4 Bedrooms</h6></div></div>
                        <div class="col-6 col-sm-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-bath fa-2x text-primary mb-2"></i><h6 class="fw-bold m-0">3 Bathrooms</h6></div></div>
                        <div class="col-6 col-sm-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-car fa-2x text-muted mb-2"></i><h6 class="fw-bold m-0">Double Porch</h6></div></div>
                        <div class="col-6 col-sm-3"><div class="p-3 bg-white border rounded shadow-sm"><i class="fas fa-sink fa-2x text-muted mb-2"></i><h6 class="fw-bold m-0">Dry Kitchen</h6></div></div>
                    </div>';
            }
        }
    }
}

// C. Proximity & Neighborhood Matrix Mapping
$proximityMap = [
    'Johor' => 'Located inside a high-growth corridor. Within 5 minutes to Universiti Teknologi Malaysia (UTM) campus, AEON Mall, localized public primary/secondary institutes, and connected to the Pasir Gudang & Skudai Expressway.',
    'Kuala Lumpur' => 'Transit-oriented urban zone. Walking distance to MRT/LRT central interchange networks, 8 minutes to Pavilion Bukit Bintang retail enclave, KLCC area, and premier private international medical hubs.',
    'Penang' => 'Highly accessible economic bridge. 5 minutes to Penang Bridge entry gate, fast accessibility to Bayan Lepas Free Industrial Zone (FIZ), local municipal schools, and heritage food centers.',
    'Selangor' => 'Integrated model master township. Immediate connectivity to ELITE & NKVE expressways, close proximity to major educational campuses, community hypermarkets, and integrated commercial squares.',
    'Sarawak' => 'Serene premium neighborhood hub. Placed 10 minutes away from Kuching International Airport, nearby local secondary schools, government administrative offices, and specialized regional healthcare centers.'
];
$proximityInfoText = $proximityMap[$property['state']] ?? 'Excellently located property ecosystem with swift reach to public transit corridors, surrounding local school systems, district clinics, and retail convenience outlets.';
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
            <div class="card shadow-sm border-0 overflow-hidden mb-4">
                <div class="position-relative zoom-container" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageZoom">
                    <img src="<?php echo $finalImg; ?>" class="w-100" style="height: 450px; object-fit: cover;">
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
                    <div class="row text-center mb-5">
                        <div class="col-6 border-end">
                            <small class="d-block text-muted fw-bold mb-1">SQFT</small>
                            <span class="fs-4 fw-bold"><?php echo number_format($property['built_up_sqft']); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="d-block text-muted fw-bold mb-1">AVAILABLE UNITS</small>
                            <span class="fs-4 fw-bold text-success"><?php echo $property['total_units']; ?></span>
                        </div>
                    </div>

                    <div class="card bg-light border-0 rounded-4 p-4 mb-4 reveal-card">
                        <h5 class="fw-bold text-dark mb-3"><i class="fas fa-th-large text-primary me-2"></i> Internal Layout Specification</h5>
                        <div class="p-2"><?php echo $layoutSpecsHtml; ?></div>
                    </div>

                    <div class="card bg-light border-0 rounded-4 p-4 mb-4 reveal-card">
                        <h5 class="fw-bold text-dark mb-3"><i class="fas fa-map-signs text-primary me-2"></i> Proximity & Neighborhood Amenities</h5>
                        <p class="text-secondary fs-6 mb-0 lh-base"><?php echo $proximityInfoText; ?></p>
                    </div>

                    <div class="card bg-light border-0 rounded-4 p-4 reveal-card">
                        <h5 class="fw-bold text-dark mb-3"><i class="fas fa-drafting-compass text-primary me-2"></i> Architectural Floor Plan</h5>
                        <div class="text-center bg-white p-3 border rounded-3 mt-2">
                            <img src="<?php echo $finalFloorPlan; ?>" class="img-fluid rounded" alt="Floor Plan Layout" style="max-height: 400px; object-fit: contain;">
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
</div>

<div class="modal fade" id="imageZoom" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body text-center p-0"><img src="<?php echo $finalImg; ?>" class="img-fluid rounded shadow-lg"></div>
        </div>
    </div>
</div>

<style>
    @keyframes driftUp {
        0% { opacity: 0; transform: translateY(40px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .reveal-card {
        animation: driftUp 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards;
    }

    .zoom-overlay { opacity: 0; transition: 0.3s; }
    .zoom-container:hover .zoom-overlay { opacity: 1; }
    
    /* STRIKING CUSTOM RANGE SLIDER SHAPE */
    .custom-slider { -webkit-appearance: none; width: 100%; height: 12px; border-radius: 6px; background: #ced4da; outline: none; }
    .custom-slider::-webkit-slider-thumb { -webkit-appearance: none; width: 32px; height: 32px; border-radius: 50%; background: #0d6efd; border: 4px solid #fff; cursor: pointer; box-shadow: 0 0 10px rgba(13,110,253,0.5); }
    .custom-slider::-moz-range-thumb { width: 32px; height: 32px; border-radius: 50%; background: #0d6efd; border: 4px solid #fff; cursor: pointer; box-shadow: 0 0 10px rgba(13,110,253,0.5); }
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

<?php include_once '../includes/footer.php'; ?>