<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: property_detail.php
 * DESCRIPTION: Upgraded Internal Layout and Dynamic Google Maps Proximity Engine.
 */

include_once 'includes/header.php';
require_once 'includes/auth_check.php';
require_once 'includes/property_images.php';
require_once 'includes/regional_proximity.php';
protect_staff_admin_page($conn);

$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

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

$dbType = strtolower(trim($property['property_type']));
$baseDir = "SYS Property Catalog/";
$finalImg = property_catalog_image_path($property);
$proximityAmenities = regional_proximity_amenities($property['state'] ?? '');
$proximityMapQuery = regional_proximity_map_query($property);

// 1. FLOOR PLAN PATH RESOLUTION
if ($is_afford || $dbType === 'affordable') {
    $floorPlanName = "Affordable_Floor_Plan.jpg";
    
    // Linux Web Hosting Strict Case-Sensitivity Scan Guard
    $checkDir = $baseDir;
    if (!file_exists($checkDir . "Affordable_Floor_Plan.jpg")) {
        if (file_exists($checkDir . "affordable_floor_plan.jpg")) {
            $floorPlanName = "affordable_floor_plan.jpg";
        } elseif (file_exists($checkDir . "Affordable_Floor_plan.jpg")) {
            $floorPlanName = "Affordable_Floor_plan.jpg";
        } elseif (file_exists($checkDir . "Affordable_Floor_Plan.JPG")) {
            $floorPlanName = "Affordable_Floor_Plan.JPG";
        } elseif (file_exists($checkDir . "affordable_floor_plan.JPG")) {
            $floorPlanName = "affordable_floor_plan.JPG";
        }
    }
} else {
    $floorPlanName = ucfirst($dbType) . "_Floor_Plan.jpg";
}

$finalFloorPlan = $baseDir . $floorPlanName; 

// ==================================================================
// SECTION 1: DETAILED INTERNAL LAYOUT SPECIFICATION
// ==================================================================
$layoutHtml = "";
$sqft = intval($property['built_up_sqft']);

if ($is_afford) {
    $layoutHtml = '
        <div class="row text-center g-4">
            <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-bed fa-2x text-success mb-2"></i><h5 class="fw-bold mb-0">3 Bedrooms</h5><p class="text-muted small mt-2 mb-0">Optimal family spacing</p></div></div>
            <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-bath fa-2x text-success mb-2"></i><h5 class="fw-bold mb-0">2 Bathrooms</h5><p class="text-muted small mt-2 mb-0">Standard sanitary fittings</p></div></div>
            <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-couch fa-2x text-muted mb-2"></i><h5 class="fw-bold mb-0">1 Living Hall</h5><p class="text-muted small mt-2 mb-0">Open concept design</p></div></div>
            <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-utensils fa-2x text-muted mb-2"></i><h5 class="fw-bold mb-0">1 Kitchen</h5><p class="text-muted small mt-2 mb-0">Ventilated cooking area</p></div></div>
        </div>';
} else {
    if ($dbType === 'commercial') {
        $layoutHtml = '
            <div class="row text-center g-4">
                <div class="col-md-4 col-12"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-store fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">2 Open Workspaces</h5><p class="text-muted small mt-2 mb-0">Flexible partition ready</p></div></div>
                <div class="col-md-4 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-briefcase fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">1 Manager Office</h5><p class="text-muted small mt-2 mb-0">Private executive suite</p></div></div>
                <div class="col-md-4 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-toilet fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">2 Restrooms</h5><p class="text-muted small mt-2 mb-0">Client & staff designated</p></div></div>
            </div>';
    } else {
        if ($dbType === 'bungalow') {
            $layoutHtml = '
                <div class="row text-center g-4">
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-bed fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">6 Bedrooms</h5><p class="text-muted small mt-2 mb-0">Premium suite sizing</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-bath fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">5 Bathrooms</h5><p class="text-muted small mt-2 mb-0">Luxury en-suite setups</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-swimming-pool fa-2x text-info mb-2"></i><h5 class="fw-bold mb-0">1 Private Pool</h5><p class="text-muted small mt-2 mb-0">Resort-style recreation</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-car-side fa-2x text-muted mb-2"></i><h5 class="fw-bold mb-0">3 Car Porches</h5><p class="text-muted small mt-2 mb-0">Expansive driveway</p></div></div>
                </div>';
        } elseif ($dbType === 'apartment') {
            $rooms = ($sqft >= 1200) ? "4" : "3";
            $baths = ($sqft >= 1200) ? "3" : "2";
            $layoutHtml = '
                <div class="row text-center g-4">
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-bed fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">'.$rooms.' Bedrooms</h5><p class="text-muted small mt-2 mb-0">High-rise scenic views</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-bath fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">'.$baths.' Bathrooms</h5><p class="text-muted small mt-2 mb-0">Modern ventilation</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-dumbbell fa-2x text-info mb-2"></i><h5 class="fw-bold mb-0">1 Gym Facility</h5><p class="text-muted small mt-2 mb-0">Resident exclusive access</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-water fa-2x text-info mb-2"></i><h5 class="fw-bold mb-0">1 Public Pool</h5><p class="text-muted small mt-2 mb-0">Maintained infinity pool</p></div></div>
                </div>';
        } else { 
            $rooms = ($sqft >= 1800) ? "5" : "4";
            $baths = ($sqft >= 1800) ? "4" : "3";
            $layoutHtml = '
                <div class="row text-center g-4">
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-bed fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">'.$rooms.' Bedrooms</h5><p class="text-muted small mt-2 mb-0">Multi-generational layout</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-bath fa-2x text-primary mb-2"></i><h5 class="fw-bold mb-0">'.$baths.' Bathrooms</h5><p class="text-muted small mt-2 mb-0">Functional water heating</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-warehouse fa-2x text-muted mb-2"></i><h5 class="fw-bold mb-0">2 Car Porches</h5><p class="text-muted small mt-2 mb-0">Covered parking space</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded shadow-sm h-100"><i class="fas fa-box-open fa-2x text-muted mb-2"></i><h5 class="fw-bold mb-0">1 Store Room</h5><p class="text-muted small mt-2 mb-0">Built-in storage solution</p></div></div>
                </div>';
        }
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 overflow-hidden h-100">
                <div class="position-relative zoom-container" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageZoom">
                    <img loading="lazy" src="<?php echo $finalImg; ?>" class="w-100" style="height: 400px; object-fit: cover;">
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-25 zoom-overlay">
                        <span class="badge bg-dark bg-opacity-75 fs-5 py-2 px-3 rounded-pill shadow"><i class="fas fa-search-plus me-2"></i>Click to Enlarge</span>
                    </div>
                </div>

                <div class="card-body p-5">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <img loading="lazy" src="SYS Property Catalog/SYS_Property_Holdings_Icon.jpeg" alt="SYS Property Holdings" style="max-height: 40px; border-radius: 4px;">
                            <h2 class="fw-bold mb-0"><?php echo htmlspecialchars($property['project_name']); ?></h2>
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
            <div class="card shadow-lg border-0 h-100 bg-dark text-white rounded-4 estimator-card">
                <div class="card-header text-white p-4 border-0 rounded-top-4 estimator-header">
                    <h5 class="fw-light text-uppercase tracking-wider mb-0 text-gold"><i class="fas fa-calculator me-2"></i>Payment Estimator</h5>
                </div>
                <div class="card-body p-4 p-lg-5 d-flex flex-column">
                    <h3 class="text-white fw-light mb-4 border-bottom border-secondary border-opacity-50 pb-4 luxury-title">RM <?php echo number_format($property['price'], 2); ?></h3>
                    <input type="hidden" id="propertyPrice" value="<?php echo $property['price']; ?>">

                    <div class="mb-3">
                        <label class="form-label fw-light text-uppercase tracking-wider small panel-label">Select Partner Bank</label>
                        <select id="bankSelect" class="form-select panel-select">
                            <?php while ($bank = $banks_result->fetch_assoc()): ?>
                                <option data-rate="<?php echo $bank['interest_rate']; ?>"><?php echo htmlspecialchars($bank['bank_name']); ?> (<?php echo $bank['interest_rate']; ?>%)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-light text-uppercase tracking-wider small panel-label">Initial Deposit</label>
                        <select id="downpayment" class="form-select panel-select">
                            <option value="10">10% (Minimum)</option>
                            <option value="20">20%</option>
                            <option value="30">30%</option>
                        </select>
                    </div>

                    <div class="mb-5">
                        <label class="form-label fw-light text-uppercase tracking-wider small panel-label">Tenure: <span id="tenureLabel" class="text-gold fs-5 fw-bold">35</span> Years</label>
                        <div class="pt-3">
                            <input type="range" id="tenure" class="form-range custom-slider" value="35" min="5" max="35">
                        </div>
                    </div>

                    <div class="p-4 rounded-4 text-center shadow-sm mb-4 monthly-card">
                        <p class="mb-2 panel-label text-uppercase tracking-wider" style="font-size: 0.7rem;">Estimated Monthly</p>
                        <h2 class="text-gold fw-light m-0 luxury-title" id="monthlyResult">RM 0.00</h2>
                        <small class="panel-note d-block mt-3" style="font-size: 0.75rem;">Effective Rate: <strong id="displayRate" class="text-white">0.00</strong>% (p.a)</small>
                    </div>

                    <div class="d-grid mt-auto">
                        <button type="button" class="btn btn-outline-secondary btn-lg fw-bold py-3 shadow text-uppercase tracking-wider" disabled>
                            <i class="fas fa-lock me-2"></i>INTERNAL DISABLE
                        </button>
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
                    <img loading="lazy" src="<?php echo htmlspecialchars($finalFloorPlan); ?>" class="img-fluid rounded" alt="Floor Plan" style="max-height: 500px; object-fit: contain;">
                </div>
            </div>

            <div class="card border-0 bg-light shadow-sm rounded-4 p-4 p-md-5 mb-5 reveal-card">
                <h3 class="fw-bold text-dark mb-4 border-bottom border-primary border-2 pb-3"><i class="fas fa-th-large text-primary me-2"></i> Internal Layout Specification</h3>
                <div class="p-2"><?php echo $layoutHtml; ?></div>
            </div>

            <div class="card border-0 bg-light shadow-sm rounded-4 p-4 p-md-5 mb-5 reveal-card">
                <div class="d-flex justify-content-between align-items-center border-bottom border-primary border-2 pb-3 mb-4">
                    <h3 class="fw-bold text-dark m-0"><i class="fas fa-map-marked-alt text-primary me-2"></i> Interactive Regional Proximity</h3>
                    <span class="badge bg-primary px-3 py-2 rounded-pill"><i class="fas fa-location-dot me-1"></i> Regional Amenities</span>
                </div>
                
                <div class="row mt-3 g-4">
                    <div class="col-lg-8">
                        <div id="propertyMap" class="rounded shadow-sm border border-secondary border-opacity-25" style="height: 480px; width: 100%;">
                            <iframe loading="lazy"
                                title="Map for <?php echo htmlspecialchars($property['project_name']); ?>"
                                src="https://maps.google.com/maps?q=<?php echo urlencode($proximityMapQuery); ?>&t=&z=13&ie=UTF8&iwloc=&output=embed"
                                class="w-100 h-100 rounded border-0"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="bg-white border rounded shadow-sm h-100 d-flex flex-column">
                            <div class="p-3 rounded-top d-flex justify-content-between align-items-center gap-3 flex-wrap" style="background: #020617;">
                                <h6 class="m-0 fw-bold" style="color: #fff !important; font-size: 1rem; text-shadow: 0 1px 3px rgba(0,0,0,0.75);">
                                    <i class="fas fa-location-arrow text-warning me-2"></i>Surrounding Amenities
                                </h6>
                                <span class="badge bg-light text-dark" id="placesCount"><?php echo count($proximityAmenities); ?> Found</span>
                            </div>
                            <div id="placesList" class="p-0 overflow-auto" style="height: 425px;">
                                <?php foreach ($proximityAmenities as $amenity): ?>
                                    <div class="d-flex align-items-center p-3 border-bottom hover-place">
                                        <div class="bg-light rounded-circle d-flex justify-content-center align-items-center me-3 border shadow-sm flex-shrink-0" style="width: 45px; height: 45px;">
                                            <i class="<?php echo htmlspecialchars($amenity['icon']); ?> fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden pe-2">
                                            <h6 class="mb-0 fw-bold text-dark text-truncate" title="<?php echo htmlspecialchars($amenity['name']); ?>"><?php echo htmlspecialchars($amenity['name']); ?></h6>
                                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;"><?php echo htmlspecialchars($amenity['type']); ?></small>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            <span class="badge bg-dark text-white fw-bold shadow-sm px-2 py-1"><?php echo htmlspecialchars($amenity['distance']); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="imageZoom" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body text-center p-0"><img loading="lazy" src="<?php echo $finalImg; ?>" class="img-fluid rounded shadow-lg"></div>
        </div>
    </div>
</div>

<style>
    @keyframes driftUp {
        0% { opacity: 0; transform: translateY(60px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .reveal-card { animation: driftUp 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards; }
    .zoom-overlay { opacity: 0; transition: 0.3s; }
    .zoom-container:hover .zoom-overlay { opacity: 1; }
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Montserrat:wght@300;400;500;600&display=swap');
    
    .luxury-title { font-family: 'Playfair Display', serif; }
    .tracking-wider { letter-spacing: 0.1em; }
    .text-gold { color: #FFC000 !important; }
    .bg-gold { background-color: #FFC000 !important; }

    .estimator-card {
        background: linear-gradient(145deg, #101318 0%, #07090d 100%) !important;
        border: 1px solid rgba(255,255,255,0.1) !important;
        box-shadow: 0 22px 50px rgba(0,0,0,0.28) !important;
        overflow: hidden;
    }
    .estimator-header {
        background: rgba(0,0,0,0.45);
        border-bottom: 1px solid rgba(255,255,255,0.08) !important;
    }
    .panel-label,
    .panel-note,
    .estimator-card label,
    .estimator-card small {
        color: #f8f5ed !important;
    }
    .panel-select {
        background-color: #05070a !important;
        color: #ffffff !important;
        border: 1px solid rgba(255,255,255,0.32) !important;
        min-height: 48px;
    }
    .panel-select:focus {
        border-color: #FFC000 !important;
        box-shadow: 0 0 0 0.18rem rgba(255,192,0,0.25) !important;
    }
    .monthly-card {
        background: #020304;
        border: 1px solid rgba(255,192,0,0.35);
    }
    .custom-slider { -webkit-appearance: none; width: 100%; height: 6px; border-radius: 3px; background: #f8f5ed; outline: none; }
    .custom-slider::-webkit-slider-thumb { -webkit-appearance: none; width: 20px; height: 20px; border-radius: 50%; background: #FFC000; border: 2px solid #000; cursor: pointer; box-shadow: 0 0 10px rgba(255,192,0,0.5); }
    .custom-slider::-moz-range-thumb { width: 20px; height: 20px; border-radius: 50%; background: #FFC000; border: 2px solid #000; cursor: pointer; box-shadow: 0 0 10px rgba(255,192,0,0.5); }
    .hover-place { transition: background-color 0.2s; }
    .hover-place:hover { background-color: #f8f9fa; }
    
    /* Scrollbar styling for places list */
    #placesList::-webkit-scrollbar { width: 6px; }
    #placesList::-webkit-scrollbar-track { background: #f1f1f1; }
    #placesList::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 10px; }
    #placesList::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
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
