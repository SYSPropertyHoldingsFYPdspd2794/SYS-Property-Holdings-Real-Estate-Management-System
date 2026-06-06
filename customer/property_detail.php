<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/property_detail.php
 * DESCRIPTION: Customer view. Upgraded Internal Layout, 16-Region Proximity coverage, and SOLD OUT security block bounds.
 */

require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';
require_once '../includes/property_images.php';
require_once '../includes/regional_proximity.php';
protect_customer_page('CUSTOMER', $conn);

$account_id = $_SESSION['account_id'];
$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// WISHLIST TOGGLE LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_wishlist'])) {
    $is_saved = false;

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
        $is_saved = true;
    }

    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'saved' => $is_saved,
            'property_id' => $property_id
        ]);
        exit();
    }

    header("Location: property_detail.php?id=" . $property_id);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM properties WHERE property_id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    include_once '../includes/header.php';
    echo "<div class='container my-5 text-center'><h3>Property not found.</h3></div>";
    include_once '../includes/footer.php'; exit();
}

include_once '../includes/header.php';

$is_wish_stmt = $conn->prepare("SELECT wishlist_id FROM wishlists WHERE customer_id = ? AND property_id = ?");
$is_wish_stmt->bind_param("ii", $account_id, $property_id);
$is_wish_stmt->execute();
$is_wishlisted = ($is_wish_stmt->get_result()->num_rows > 0);

$is_afford = (intval($property['is_affordable']) === 1);
$is_sold_out = (trim($property['status']) === 'SOLD_OUT');
$banks_result = $conn->query("SELECT bank_name, interest_rate FROM banks ORDER BY interest_rate ASC");

$dbType = strtolower(trim($property['property_type']));
$baseDir = $root_prefix . "SYS Property Catalog/";
$finalImg = property_catalog_image_path($property, $root_prefix, '../');
$proximityAmenities = regional_proximity_amenities($property['state'] ?? '');
$proximityMapQuery = regional_proximity_map_query($property);

// 1. FLOOR PLAN PATH 
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
            <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-bed fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">3 Bedrooms</h6><p class="text-muted small mt-2 mb-0">Optimal family spacing</p></div></div>
            <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-bath fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">2 Bathrooms</h6><p class="text-muted small mt-2 mb-0">Standard sanitary fittings</p></div></div>
            <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-couch fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">1 Living Hall</h6><p class="text-muted small mt-2 mb-0">Open concept design</p></div></div>
            <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-utensils fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">1 Kitchen</h6><p class="text-muted small mt-2 mb-0">Ventilated cooking area</p></div></div>
        </div>';
} else {
    if ($dbType === 'commercial') {
        $layoutHtml = '
            <div class="row text-center g-4">
                <div class="col-md-4 col-12"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-store fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">2 Open Workspaces</h6><p class="text-muted small mt-2 mb-0">Flexible partition ready</p></div></div>
                <div class="col-md-4 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-briefcase fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">1 Manager Office</h6><p class="text-muted small mt-2 mb-0">Private executive suite</p></div></div>
                <div class="col-md-4 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-toilet fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">2 Restrooms</h6><p class="text-muted small mt-2 mb-0">Client & staff designated</p></div></div>
            </div>';
    } else {
        if ($dbType === 'bungalow') {
            $layoutHtml = '
                <div class="row text-center g-4">
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-bed fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">6 Bedrooms</h6><p class="text-muted small mt-2 mb-0">Premium suite sizing</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-bath fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">5 Bathrooms</h6><p class="text-muted small mt-2 mb-0">Luxury en-suite setups</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-swimming-pool fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">1 Private Pool</h6><p class="text-muted small mt-2 mb-0">Resort-style recreation</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-car-side fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">3 Car Porches</h6><p class="text-muted small mt-2 mb-0">Expansive driveway</p></div></div>
                </div>';
        } elseif ($dbType === 'apartment') {
            $rooms = ($sqft >= 1200) ? "4" : "3";
            $baths = ($sqft >= 1200) ? "3" : "2";
            $layoutHtml = '
                <div class="row text-center g-4">
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-bed fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">'.$rooms.' Bedrooms</h6><p class="text-muted small mt-2 mb-0">High-rise scenic views</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-bath fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">'.$baths.' Bathrooms</h6><p class="text-muted small mt-2 mb-0">Modern ventilation</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-dumbbell fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">1 Gym Facility</h6><p class="text-muted small mt-2 mb-0">Resident exclusive access</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-water fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">1 Public Pool</h6><p class="text-muted small mt-2 mb-0">Maintained infinity pool</p></div></div>
                </div>';
        } else { 
            $rooms = ($sqft >= 1800) ? "5" : "4";
            $baths = ($sqft >= 1800) ? "4" : "3";
            $layoutHtml = '
                <div class="row text-center g-4">
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-bed fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">'.$rooms.' Bedrooms</h6><p class="text-muted small mt-2 mb-0">Multi-generational layout</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-bath fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">'.$baths.' Bathrooms</h6><p class="text-muted small mt-2 mb-0">Functional water heating</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-warehouse fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">2 Car Porches</h6><p class="text-muted small mt-2 mb-0">Covered parking space</p></div></div>
                    <div class="col-md-3 col-6"><div class="p-4 bg-white border rounded-4 shadow-sm h-100 icon-box"><i class="fas fa-box-open fa-2x text-gold mb-3"></i><h6 class="fw-bold mb-0 text-uppercase tracking-wider">1 Store Room</h6><p class="text-muted small mt-2 mb-0">Built-in storage solution</p></div></div>
                </div>';
        }
    }
}

// ==================================================================
// SECTION 2: 16 REGIONS PROXIMITY & NEIGHBORHOOD MAPPING
// ==================================================================
$proximityHtml = '<div class="row g-4 fs-5 text-secondary">';
$stateCheck = strtoupper(trim($property['state']));

if ($stateCheck === 'JOHOR') {
    $proximityHtml .= '
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-university text-danger fa-fw me-3"></i> <span>Universiti Teknologi Malaysia (UTM) <strong class="text-dark">3.2 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-shopping-cart text-primary fa-fw me-3"></i> <span>AEON Mall Tebrau City <strong class="text-dark">5.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-hospital text-success fa-fw me-3"></i> <span>Sultanah Aminah Hospital <strong class="text-dark">8.1 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-plane text-warning fa-fw me-3"></i> <span>Senai International Airport <strong class="text-dark">18.0 KM</strong></span></div></div>';
} elseif ($stateCheck === 'KEDAH') {
    $proximityHtml .= '
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-shopping-bag text-primary fa-fw me-3"></i> <span>Aman Central Mall <strong class="text-dark">3.2 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-hospital-alt text-success fa-fw me-3"></i> <span>Hospital Sultanah Bahiyah <strong class="text-dark">4.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-plane-departure text-warning fa-fw me-3"></i> <span>Sultan Abdul Halim Airport <strong class="text-dark">12.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-graduation-cap text-danger fa-fw me-3"></i> <span>Universiti Utara Malaysia (UUM) <strong class="text-dark">15.5 KM</strong></span></div></div>';
} elseif ($stateCheck === 'KELANTAN') {
    $proximityHtml .= '
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-shopping-cart text-primary fa-fw me-3"></i> <span>Aeon Mall Kota Bharu <strong class="text-dark">4.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-clinic-medical text-success fa-fw me-3"></i> <span>Hospital Raja Perempuan Zainab II <strong class="text-dark">3.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-store text-danger fa-fw me-3"></i> <span>Siti Khadijah Market <strong class="text-dark">2.1 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-plane text-warning fa-fw me-3"></i> <span>Sultan Ismail Petra Airport <strong class="text-dark">9.0 KM</strong></span></div></div>';
} elseif ($stateCheck === 'MELAKA' || $stateCheck === 'MALACCA') {
    $proximityHtml .= '
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-map-signs text-danger fa-fw me-3"></i> <span>Jonker Street Heritage Area <strong class="text-dark">2.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-hospital text-success fa-fw me-3"></i> <span>Melaka General Hospital <strong class="text-dark">4.1 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-shopping-bag text-primary fa-fw me-3"></i> <span>Dataran Pahlawan Megamall <strong class="text-dark">3.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-university text-warning fa-fw me-3"></i> <span>Multimedia University (MMU) <strong class="text-dark">6.5 KM</strong></span></div></div>';
} elseif ($stateCheck === 'NEGERI SEMBILAN') {
    $proximityHtml .= '
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-shopping-cart text-primary fa-fw me-3"></i> <span>Palm Mall Seremban <strong class="text-dark">3.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-hospital-alt text-success fa-fw me-3"></i> <span>Hospital Tuanku Ja\'afar <strong class="text-dark">4.2 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-graduation-cap text-danger fa-fw me-3"></i> <span>UiTM Seremban Campus <strong class="text-dark">7.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-store-alt text-warning fa-fw me-3"></i> <span>Seremban Gateway <strong class="text-dark">2.8 KM</strong></span></div></div>';
} elseif ($stateCheck === 'PAHANG') {
    $proximityHtml .= '
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-shopping-bag text-primary fa-fw me-3"></i> <span>East Coast Mall <strong class="text-dark">4.1 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-hospital text-success fa-fw me-3"></i> <span>Hospital Tengku Ampuan Afzan <strong class="text-dark">3.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-umbrella-beach text-warning fa-fw me-3"></i> <span>Teluk Cempedak Beach <strong class="text-dark">5.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-university text-danger fa-fw me-3"></i> <span>Universiti Malaysia Pahang <strong class="text-dark">18.0 KM</strong></span></div></div>';
} elseif ($stateCheck === 'PERAK') {
    $proximityHtml .= '
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-shopping-cart text-primary fa-fw me-3"></i> <span>Ipoh Parade Shopping Centre <strong class="text-dark">2.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-hospital-alt text-success fa-fw me-3"></i> <span>Hospital Raja Permaisuri Bainun <strong class="text-dark">3.2 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-plane text-warning fa-fw me-3"></i> <span>Sultan Azlan Shah Airport <strong class="text-dark">6.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-graduation-cap text-danger fa-fw me-3"></i> <span>Universiti Teknologi PETRONAS <strong class="text-dark">14.5 KM</strong></span></div></div>';
} elseif ($stateCheck === 'PERLIS') {
    $proximityHtml .= '
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-store text-primary fa-fw me-3"></i> <span>C-Mart Arau Hypermarket <strong class="text-dark">4.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-clinic-medical text-success fa-fw me-3"></i> <span>Hospital Tuanku Fauziah <strong class="text-dark">5.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-university text-danger fa-fw me-3"></i> <span>Universiti Malaysia Perlis (UniMAP) <strong class="text-dark">8.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-train text-warning fa-fw me-3"></i> <span>Arau KTM Railway Station <strong class="text-dark">3.0 KM</strong></span></div></div>';
} elseif ($stateCheck === 'PENANG' || $stateCheck === 'PULAU PINANG') {
    $proximityHtml .= '
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-archway text-danger fa-fw me-3"></i> <span>Penang Bridge <strong class="text-dark">4.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-industry text-primary fa-fw me-3"></i> <span>Bayan Lepas Free Industrial Zone <strong class="text-dark">6.2 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-shopping-cart text-success fa-fw me-3"></i> <span>Queensbay Mall <strong class="text-dark">3.8 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-hospital-alt text-warning fa-fw me-3"></i> <span>Penang General Hospital <strong class="text-dark">7.1 KM</strong></span></div></div>';
} elseif ($stateCheck === 'SABAH') {
    $proximityHtml .= '
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-shopping-bag text-primary fa-fw me-3"></i> <span>Imago Shopping Mall <strong class="text-dark">3.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-hospital text-success fa-fw me-3"></i> <span>Queen Elizabeth Hospital <strong class="text-dark">4.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-plane text-warning fa-fw me-3"></i> <span>Kota Kinabalu International Airport <strong class="text-dark">7.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-graduation-cap text-danger fa-fw me-3"></i> <span>Universiti Malaysia Sabah (UMS) <strong class="text-dark">12.0 KM</strong></span></div></div>';
} elseif ($stateCheck === 'SARAWAK') {
    $proximityHtml .= '
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-shopping-cart text-primary fa-fw me-3"></i> <span>The Spring Shopping Mall <strong class="text-dark">3.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-hospital-alt text-success fa-fw me-3"></i> <span>Sarawak General Hospital <strong class="text-dark">4.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-plane-departure text-warning fa-fw me-3"></i> <span>Kuching International Airport <strong class="text-dark">8.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-university text-danger fa-fw me-3"></i> <span>Universiti Malaysia Sarawak <strong class="text-dark">15.0 KM</strong></span></div></div>';
} elseif ($stateCheck === 'SELANGOR') {
    $proximityHtml .= '
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-shopping-bag text-danger fa-fw me-3"></i> <span>Sunway Pyramid Mall <strong class="text-dark">4.2 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-graduation-cap text-primary fa-fw me-3"></i> <span>Monash University Malaysia <strong class="text-dark">3.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-clinic-medical text-success fa-fw me-3"></i> <span>Subang Jaya Medical Centre <strong class="text-dark">5.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-subway text-warning fa-fw me-3"></i> <span>Nearest LRT/MRT Station <strong class="text-dark">1.8 KM</strong></span></div></div>';
} elseif ($stateCheck === 'TERENGGANU') {
    $proximityHtml .= '
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-store text-primary fa-fw me-3"></i> <span>KTCC Mall <strong class="text-dark">2.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-hospital text-success fa-fw me-3"></i> <span>Hospital Sultanah Nur Zahirah <strong class="text-dark">3.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-university text-danger fa-fw me-3"></i> <span>Universiti Sultan Zainal Abidin <strong class="text-dark">9.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-plane text-warning fa-fw me-3"></i> <span>Sultan Mahmud Airport <strong class="text-dark">11.0 KM</strong></span></div></div>';
} elseif ($stateCheck === 'KUALA LUMPUR' || $stateCheck === 'WP KUALA LUMPUR') {
    $proximityHtml .= '
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-train text-danger fa-fw me-3"></i> <span>KLCC LRT Station <strong class="text-dark">1.2 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-shopping-bag text-primary fa-fw me-3"></i> <span>Pavilion Bukit Bintang <strong class="text-dark">2.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-school text-success fa-fw me-3"></i> <span>International School of KL (ISKL) <strong class="text-dark">4.3 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-tree text-warning fa-fw me-3"></i> <span>Perdana Botanical Garden <strong class="text-dark">5.0 KM</strong></span></div></div>';
} elseif ($stateCheck === 'LABUAN' || $stateCheck === 'WP LABUAN') {
    $proximityHtml .= '
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-building text-primary fa-fw me-3"></i> <span>Financial Park Labuan <strong class="text-dark">1.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-hospital text-success fa-fw me-3"></i> <span>Labuan Nucleus Hospital <strong class="text-dark">3.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-school text-danger fa-fw me-3"></i> <span>Labuan International School <strong class="text-dark">4.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-plane-departure text-warning fa-fw me-3"></i> <span>Labuan Airport <strong class="text-dark">5.0 KM</strong></span></div></div>';
} elseif ($stateCheck === 'PUTRAJAYA' || $stateCheck === 'WP PUTRAJAYA') {
    $proximityHtml .= '
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-shopping-bag text-primary fa-fw me-3"></i> <span>IOI City Mall <strong class="text-dark">6.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-hospital-alt text-success fa-fw me-3"></i> <span>Hospital Putrajaya <strong class="text-dark">4.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-store-alt text-danger fa-fw me-3"></i> <span>Alamanda Shopping Centre <strong class="text-dark">5.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-subway text-warning fa-fw me-3"></i> <span>Putrajaya Sentral Station <strong class="text-dark">3.5 KM</strong></span></div></div>';
} else {
    $proximityHtml .= '
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-school text-danger fa-fw me-3"></i> <span>Regional Secondary School <strong class="text-dark">1.5 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-store text-primary fa-fw me-3"></i> <span>Local Commercial Complex <strong class="text-dark">2.3 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-clinic-medical text-success fa-fw me-3"></i> <span>Community Health Center <strong class="text-dark">3.0 KM</strong></span></div></div>
        <div class="col-md-6"><div class="d-flex align-items-center"><i class="fas fa-bus text-warning fa-fw me-3"></i> <span>Central Transit Hub <strong class="text-dark">4.2 KM</strong></span></div></div>';
}
$proximityHtml .= '</div>';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-7 mb-4">
            <div class="card shadow-lg border-0 overflow-hidden h-100 rounded-4 property-hero-card bg-white">
                <div class="position-relative zoom-container" style="cursor: zoom-in;" data-bs-toggle="modal" data-bs-target="#imageZoom">
                    <img src="<?php echo $finalImg; ?>" class="w-100" style="height: 450px; object-fit: cover;">
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-black bg-opacity-25 zoom-overlay">
                        <span class="badge bg-dark bg-opacity-75 fs-5 py-2 px-3 rounded-pill shadow"><i class="fas fa-search-plus me-2"></i>Click to Enlarge</span>
                    </div>
                </div>

                <div class="card-body p-5">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <h2 class="luxury-title fw-light text-dark mb-0"><?php echo htmlspecialchars($property['project_name']); ?></h2>
                            <form method="POST" class="m-0 wishlist-detail-form">
                                <button type="submit" name="toggle_wishlist" class="btn <?php echo $is_wishlisted ? 'bg-dark text-white' : 'btn-outline-dark'; ?> rounded-circle d-flex align-items-center justify-content-center shadow-sm wishlist-detail-btn" style="width: 45px; height: 45px; border-color: #212529 !important;">
                                    <i class="<?php echo $is_wishlisted ? 'fas text-gold' : 'far'; ?> fa-heart fs-5"></i>
                                </button>
                            </form>
                        </div>
                        <?php if ($is_sold_out): ?>
                            <span class="badge bg-danger px-4 py-2 text-uppercase tracking-wider shadow-sm text-white rounded-0">SOLD OUT</span>
                        <?php else: ?>
                            <span class="badge <?php echo $is_afford ? 'bg-success' : 'bg-gold text-dark'; ?> px-4 py-2 text-uppercase tracking-wider shadow-sm rounded-0" style="border: 1px solid rgba(255,255,255,0.2);">
                                <?php echo $is_afford ? 'GOV AFFORDABLE' : htmlspecialchars($property['property_type']); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <p class="text-secondary fw-bold mb-4 tracking-wider text-uppercase" style="font-size: 0.9rem !important;"><i class="fas fa-map-marker-alt text-gold me-2"></i> <?php echo htmlspecialchars($property['state']); ?></p>

                    <?php if ($is_afford): ?>
                        <div class="alert alert-danger border-0 shadow-sm p-4 mb-4" style="background-color: #fff5f5; border-left: 5px solid #dc3545 !important;">
                            <h5 class="fw-bold text-danger mb-2"><i class="fas fa-exclamation-triangle me-2"></i> Eligibility Restriction</h5>
                            <p class="mb-0 text-dark">This is a government-subsidized unit. Applicants must have a combined household income <strong>Below RM <?php echo number_format($property['income_limit_rm'] ?? 0); ?></strong>. Documents must be verified offline.</p>
                        </div>
                    <?php endif; ?>

                    <hr class="my-4">
                    <div class="row text-center mb-4">
                        <div class="col-4 border-end px-2">
                            <small class="d-block text-secondary fw-bold text-uppercase tracking-wider mb-2" style="font-size: 0.75rem;">Area SQFT</small>
                            <span class="fs-4 fw-bold text-dark"><?php echo number_format($property['built_up_sqft']); ?></span>
                        </div>
                        <div class="col-4 border-end px-2">
                            <small class="d-block text-secondary fw-bold text-uppercase tracking-wider mb-2" style="font-size: 0.75rem;">Availability</small>
                            <span class="fs-4 fw-bold <?php echo $is_sold_out ? 'text-danger' : 'text-dark'; ?>"><?php echo $is_sold_out ? '0' : $property['total_units']; ?></span>
                        </div>
                        <div class="col-4 px-2">
                            <small class="d-block text-secondary fw-bold text-uppercase tracking-wider mb-2" style="font-size: 0.75rem;">Reference</small>
                            <span class="fs-5 fw-bold text-secondary"><?php echo htmlspecialchars($property['property_code']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5 mb-4">
            <div class="card shadow-lg border-0 h-100 bg-dark text-white rounded-4 estimator-card">
                <div class="card-header text-white p-4 border-0 rounded-top-4 estimator-header">
                    <h5 class="fw-light text-uppercase tracking-wider mb-0 text-gold"><i class="fas fa-calculator me-2"></i>Financial Calculator</h5>
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
                        <?php if ($is_sold_out): ?>
                            <button type="button" class="btn btn-outline-danger btn-lg fw-bold py-3 shadow text-uppercase tracking-wider" disabled>
                                <i class="fas fa-lock me-2"></i>SOLD OUT
                            </button>
                        <?php else: ?>
                            <?php if ($is_afford): ?>
                                <a href="apply_affordable.php?id=<?php echo $property_id; ?>" class="btn btn-success btn-lg fw-light py-3 shadow text-uppercase tracking-wider">Apply Now</a>
                            <?php else: ?>
                                <a href="book_appointment.php?id=<?php echo $property_id; ?>" class="btn btn-light btn-lg fw-bold py-3 shadow text-uppercase tracking-wider text-dark">Book Private Viewing</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            
            <div class="card border-0 bg-white shadow-sm rounded-4 p-4 p-md-5 mb-5 reveal-card content-card">
                <h3 class="luxury-title text-dark mb-4 border-bottom border-secondary border-opacity-25 pb-3"><i class="fas fa-drafting-compass text-gold me-3"></i> Architectural Masterplan</h3>
                <div class="text-center bg-light p-4 rounded-4 shadow-inner floor-plan-frame">
                    <img src="<?php echo htmlspecialchars($finalFloorPlan); ?>" class="img-fluid rounded" alt="Floor Plan" style="max-height: 500px; object-fit: contain;">
                </div>
            </div>

            <div class="card border-0 bg-white shadow-sm rounded-4 p-4 p-md-5 mb-5 reveal-card content-card">
                <h3 class="luxury-title text-dark mb-4 border-bottom border-secondary border-opacity-25 pb-3"><i class="fas fa-th-large text-gold me-3"></i> Interior Specifications</h3>
                <div class="p-2"><?php echo $layoutHtml; ?></div>
            </div>

            <div class="card border-0 bg-white shadow-sm rounded-4 p-4 p-md-5 mb-5 reveal-card content-card">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 border-bottom border-secondary border-opacity-25 pb-3 mb-4">
                    <h3 class="luxury-title text-dark m-0"><i class="fas fa-map-marked-alt text-gold me-3"></i> Interactive Regional Proximity</h3>
                    <span class="badge bg-dark text-gold px-3 py-2 rounded-pill"><i class="fas fa-location-dot me-1"></i> Regional Amenities</span>
                </div>

                <div class="row mt-3 g-4">
                    <div class="col-lg-8">
                        <div id="propertyMap" class="rounded-4 shadow-inner border border-secondary border-opacity-25" style="height: 480px; width: 100%;">
                            <iframe
                                title="Map for <?php echo htmlspecialchars($property['project_name']); ?>"
                                src="https://maps.google.com/maps?q=<?php echo urlencode($proximityMapQuery); ?>&t=&z=13&ie=UTF8&iwloc=&output=embed"
                                class="w-100 h-100 rounded-4 border-0"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="bg-white border rounded-4 shadow-sm h-100 d-flex flex-column overflow-hidden">
                            <div class="p-3 d-flex justify-content-between align-items-center gap-3 flex-wrap" style="background: #020617;">
                                <h6 class="m-0 fw-bold" style="color: #fff !important; font-size: 1rem; text-shadow: 0 1px 3px rgba(0,0,0,0.75);">
                                    <i class="fas fa-location-arrow text-gold me-2"></i>Surrounding Amenities
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
                                            <span class="badge bg-dark text-gold fw-bold shadow-sm px-2 py-1"><?php echo htmlspecialchars($amenity['distance']); ?></span>
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
            <div class="modal-body text-center p-0"><img src="<?php echo $finalImg; ?>" class="img-fluid rounded shadow-lg"></div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Montserrat:wght@300;400;500;600&display=swap');
    
    .luxury-title { font-family: 'Playfair Display', serif; }
    p { line-height: 1.65; }
    .tracking-wider { letter-spacing: 0.1em; }
    .text-gold { color: #FFC000 !important; }
    .bg-gold { background-color: #FFC000 !important; }
    .property-hero-card,
    .content-card {
        border: 1px solid rgba(20, 24, 31, 0.06) !important;
        box-shadow: 0 18px 45px rgba(20, 24, 31, 0.08) !important;
    }
    .shadow-inner { box-shadow: inset 0 2px 10px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.05); }
    .floor-plan-frame,
    .proximity-frame {
        background: #f7f5ef !important;
    }
    .icon-box {
        border-color: rgba(20, 24, 31, 0.08) !important;
        border-radius: 1rem !important;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .icon-box:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(20,24,31,0.1) !important; border-color: rgba(255,192,0,0.45) !important; }
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
    
    @keyframes driftUp {
        0% { opacity: 0; transform: translateY(60px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .reveal-card { animation: driftUp 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards; }
    .zoom-overlay { opacity: 0; transition: 0.3s; }
    .zoom-container:hover .zoom-overlay { opacity: 1; }
    
    .custom-slider { -webkit-appearance: none; width: 100%; height: 6px; border-radius: 3px; background: #f8f5ed; outline: none; }
    .custom-slider::-webkit-slider-thumb { -webkit-appearance: none; width: 20px; height: 20px; border-radius: 50%; background: #FFC000; border: 2px solid #000; cursor: pointer; box-shadow: 0 0 10px rgba(255,192,0,0.5); }
    .custom-slider::-moz-range-thumb { width: 20px; height: 20px; border-radius: 50%; background: #FFC000; border: 2px solid #000; cursor: pointer; box-shadow: 0 0 10px rgba(255,192,0,0.5); }
    .hover-place { transition: background-color 0.2s ease; }
    .hover-place:hover { background-color: #f8f5ed; }
    .wishlist-detail-btn.is-loading {
        opacity: 0.65;
        pointer-events: none;
    }
    #placesList::-webkit-scrollbar { width: 6px; }
    #placesList::-webkit-scrollbar-track { background: #f1f1f1; }
    #placesList::-webkit-scrollbar-thumb { background: #FFC000; border-radius: 10px; }
    #placesList::-webkit-scrollbar-thumb:hover { background: #cc9a00; }
    @media (max-width: 767.98px) {
        .tracking-wider { letter-spacing: 0.06em; }
        .card-body.p-5 { padding: 1.5rem !important; }
        .proximity-frame { padding: 1.25rem !important; }
    }
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

const wishlistDetailForm = document.querySelector('.wishlist-detail-form');
if (wishlistDetailForm) {
    wishlistDetailForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const button = wishlistDetailForm.querySelector('.wishlist-detail-btn');
        const icon = button ? button.querySelector('i') : null;
        const data = new FormData(wishlistDetailForm);
        data.append('toggle_wishlist', '1');

        if (button) {
            button.classList.add('is-loading');
            button.disabled = true;
        }

        fetch(<?php echo json_encode('property_detail.php?id=' . $property_id); ?>, {
            method: 'POST',
            body: data,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Wishlist update failed');
            }
            return response.json();
        })
        .then(function (result) {
            if (!result.success || !button || !icon) {
                return;
            }

            if (result.saved) {
                button.classList.remove('btn-outline-dark');
                button.classList.add('bg-dark', 'text-white');
                icon.className = 'fas text-gold fa-heart fs-5';
            } else {
                button.classList.remove('bg-dark', 'text-white');
                button.classList.add('btn-outline-dark');
                icon.className = 'far fa-heart fs-5';
            }
        })
        .catch(function () {
            HTMLFormElement.prototype.submit.call(wishlistDetailForm);
        })
        .finally(function () {
            if (button) {
                button.classList.remove('is-loading');
                button.disabled = false;
            }
        });
    });
}

</script>

<?php include_once '../includes/footer.php'; ?>
