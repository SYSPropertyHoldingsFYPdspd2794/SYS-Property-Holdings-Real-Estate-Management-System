<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: properties.php (ROOT DIRECTORY)
 * DESCRIPTION: Internal catalog for Staff and Admin with full state filters and robust image logic.
 */

// 1. Path Fix: This file is in root, so includes are direct
include_once 'includes/header.php';
require_once 'includes/auth_check.php';

// 2. Security: Strictly for Staff and Admin
protect_staff_admin_page($conn);

// 3. Filter Logic Initialization
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$filter_state = isset($_GET['filter_state']) ? trim($_GET['filter_state']) : '';
$filter_type = isset($_GET['filter_type']) ? trim($_GET['filter_type']) : '';

// 4. SQL Construction
$sql = "SELECT * FROM properties WHERE (status = 'ACTIVE' OR status = 'AVAILABLE')";

if (!empty($search_name)) {
    $sql .= " AND project_name LIKE '%" . $conn->real_escape_string($search_name) . "%'";
}
if (!empty($filter_state)) {
    $sql .= " AND state = '" . $conn->real_escape_string($filter_state) . "'";
}

// Logic: If filter is AFFORDABLE, check is_affordable column
if ($filter_type === 'AFFORDABLE') {
    $sql .= " AND is_affordable = 1";
} elseif (!empty($filter_type)) {
    $sql .= " AND property_type = '" . $conn->real_escape_string($filter_type) . "' AND is_affordable = 0";
}

$sql .= " ORDER BY is_affordable DESC, property_id DESC";
$result = $conn->query($sql);
?>

<div class="container my-5">
    <div class="row mb-4 text-center">
        <div class="col-md-12">
            <h2 class="fw-bold display-5 text-dark">Inventory Catalog</h2>
            <p class="lead text-secondary text-uppercase small" style="letter-spacing: 2px;">Internal Staff & Administration View</p>
            <hr class="w-25 mx-auto bg-dark" style="height: 3px; opacity: 1;">
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-5 bg-light rounded-4">
        <div class="card-body p-4">
            <form method="GET" action="properties.php">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted small"><i class="fas fa-search me-1"></i> Search Property</label>
                        <input type="text" name="search_name" class="form-control form-control-lg" placeholder="e.g. Pagoh Jaya..." value="<?php echo htmlspecialchars($search_name); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small"><i class="fas fa-map-marker-alt me-1"></i> Filter State</label>
                        <select name="filter_state" class="form-select form-select-lg">
                            <option value="">All Regions</option>
                            <optgroup label="States">
                                <option value="Johor" <?php if($filter_state=='Johor') echo 'selected'; ?>>Johor</option>
                                <option value="Kedah" <?php if($filter_state=='Kedah') echo 'selected'; ?>>Kedah</option>
                                <option value="Kelantan" <?php if($filter_state=='Kelantan') echo 'selected'; ?>>Kelantan</option>
                                <option value="Melaka" <?php if($filter_state=='Melaka') echo 'selected'; ?>>Melaka</option>
                                <option value="Negeri Sembilan" <?php if($filter_state=='Negeri Sembilan') echo 'selected'; ?>>Negeri Sembilan</option>
                                <option value="Pahang" <?php if($filter_state=='Pahang') echo 'selected'; ?>>Pahang</option>
                                <option value="Perak" <?php if($filter_state=='Perak') echo 'selected'; ?>>Perak</option>
                                <option value="Perlis" <?php if($filter_state=='Perlis') echo 'selected'; ?>>Perlis</option>
                                <option value="Penang" <?php if($filter_state=='Penang') echo 'selected'; ?>>Penang</option>
                                <option value="Sabah" <?php if($filter_state=='Sabah') echo 'selected'; ?>>Sabah</option>
                                <option value="Sarawak" <?php if($filter_state=='Sarawak') echo 'selected'; ?>>Sarawak</option>
                                <option value="Selangor" <?php if($filter_state=='Selangor') echo 'selected'; ?>>Selangor</option>
                                <option value="Terengganu" <?php if($filter_state=='Terengganu') echo 'selected'; ?>>Terengganu</option>
                            </optgroup>
                            <optgroup label="Federal Territories">
                                <option value="WP Kuala Lumpur" <?php if($filter_state=='WP Kuala Lumpur') echo 'selected'; ?>>WP Kuala Lumpur</option>
                                <option value="WP Labuan" <?php if($filter_state=='WP Labuan') echo 'selected'; ?>>WP Labuan</option>
                                <option value="WP Putrajaya" <?php if($filter_state=='WP Putrajaya') echo 'selected'; ?>>WP Putrajaya</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small"><i class="fas fa-home me-1"></i> Category</label>
                        <select name="filter_type" class="form-select form-select-lg">
                            <option value="">All Categories</option>
                            <option value="AFFORDABLE" <?php if($filter_type=='AFFORDABLE') echo 'selected'; ?>>Affordable</option>
                            <option value="TERRACE" <?php if($filter_type=='TERRACE') echo 'selected'; ?>>Terrace</option>
                            <option value="BUNGALOW" <?php if($filter_type=='BUNGALOW') echo 'selected'; ?>>Bungalow</option>
                            <option value="COMMERCIAL" <?php if($filter_type=='COMMERCIAL') echo 'selected'; ?>>Commercial</option>
                            <option value="APARTMENT" <?php if($filter_type=='APARTMENT') echo 'selected'; ?>>Apartment</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-dark btn-lg w-100 fw-bold shadow-sm">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                
                $is_affordable = (intval($row['is_affordable']) === 1);
                $badge_class = $is_affordable ? "bg-success" : "bg-primary";
                $badge_text = $is_affordable ? "GOV AFFORDABLE" : htmlspecialchars($row['property_type']);

                // --- ROBUST IMAGE ENGINE (ROOT VERSION) ---
                $dbType = strtolower(trim($row['property_type']));
                $rawState = trim($row['state']);
                
                // Normalizing State names to match file directory
                if (strtoupper($rawState) === 'PENANG') $rawState = 'Pulau Pinang';
                if (strtoupper($rawState) === 'MALACCA') $rawState = 'Melaka';
                $stateName = ucwords(strtolower($rawState)); 
                
                $folder = ""; $filePrefix = "";
                switch($dbType) {
                    case 'commercial': $folder = "Commercial/"; $filePrefix = "Commercial"; break;
                    case 'terrace': $folder = "Terrace/"; $filePrefix = "Terrace"; break;
                    case 'bungalow': $folder = "Bungalow/"; $filePrefix = "Bungalow"; break;
                    case 'apartment': $folder = "Apartment/"; $filePrefix = "Apartment"; break;
                    default: $folder = "Apartment/"; $filePrefix = "Apartment";
                }

                // Since this file is in ROOT, we don't need $root_prefix for assets
                $baseDir = "SYS Property Catalog/";
                $finalImg = $baseDir . "placeholder.jpg"; 
                $fileName = $filePrefix . " - " . $stateName;

                foreach (['jpg', 'jpeg', 'png', 'webp', 'JPG', 'JPEG', 'PNG', 'WEBP'] as $ext) {
                    $testPath = $baseDir . $folder . $fileName . "." . $ext;
                    if (file_exists($testPath)) {
                        $finalImg = $testPath;
                        break;
                    }
                }

                $formattedPrice = number_format($row['price'], 2);
                ?>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden hover-effect">
                        <div class="position-relative bg-light" style="height: 230px;">
                            <img src="<?php echo htmlspecialchars($finalImg); ?>" 
                                 class="w-100 h-100" 
                                 alt="Property" 
                                 style="object-fit: cover; object-position: center;">
                            <span class="badge <?php echo $badge_class; ?> position-absolute top-0 end-0 m-3 shadow">
                                <?php echo $badge_text; ?>
                            </span>
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <h5 class="card-title fw-bold text-dark mb-1 text-truncate" title="<?php echo htmlspecialchars($row['project_name']); ?>">
                                <?php echo htmlspecialchars($row['project_name']); ?>
                            </h5>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i> <?php echo htmlspecialchars($row['state']); ?>
                            </p>
                            
                            <?php if ($is_affordable): ?>
                                <div class="bg-danger bg-opacity-10 border-start border-danger border-4 p-2 mb-3">
                                    <small class="text-danger fw-bold d-block"><i class="fas fa-id-card me-1"></i> ELIGIBILITY RESTRICED</small>
                                    <small class="text-dark">Income Limit: RM <?php echo number_format($row['applicant_income_limit']); ?></small>
                                </div>
                            <?php endif; ?>

                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block fw-bold" style="font-size: 0.7rem;">LISTING PRICE</small>
                                    <h4 class="text-success fw-bold mb-0">RM <?php echo $formattedPrice; ?></h4>
                                </div>
                                <a href="property_detail.php?id=<?php echo $row['property_id']; ?>" 
                                   class="btn btn-outline-dark rounded-pill px-4 shadow-sm fw-bold">
                                   View Detail
                                </a>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-0 py-3 text-center border-top">
                            <small class="text-muted">
                                <i class="fas fa-door-open me-1"></i> Available Units: 
                                <strong class="text-dark"><?php echo $row['total_units']; ?></strong>
                            </small>
                        </div>
                    </div>
                </div>

                <?php
            }
        } else {
            echo '<div class="col-12 text-center py-5">
                    <i class="fas fa-search-minus fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted fw-bold">No Records Found</h4>
                    <p class="text-muted">No properties match your current internal criteria.</p>
                    <a href="properties.php" class="btn btn-dark mt-2">Reset Inventory View</a>
                  </div>';
        }
        ?>
    </div>
</div>

<style>
    .hover-effect { transition: transform 0.3s ease; }
    .hover-effect:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,0.12) !important; }
</style>

<?php include_once 'includes/footer.php'; ?>