<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/properties.php
 * DESCRIPTION: Customer exclusive property catalog with search and filters.
 */

include_once '../includes/header.php';
require_once '../includes/auth_check.php';
include_once '../includes/header.php';
/** @var string $root_prefix */
/** @var mysqli $conn */
require_once '../includes/auth_check.php';
protect_customer_page('CUSTOMER', $conn);

// --- SEARCH & FILTER LOGIC ---
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$filter_state = isset($_GET['filter_state']) ? trim($_GET['filter_state']) : '';
$filter_type = isset($_GET['filter_type']) ? trim($_GET['filter_type']) : '';

$sql = "SELECT * FROM properties WHERE (status = 'ACTIVE' OR status = 'AVAILABLE')";
$params = [];
$types = "";

if (!empty($search_name)) {
    $sql .= " AND project_name LIKE ?";
    $params[] = "%" . $search_name . "%";
    $types .= "s";
}
if (!empty($filter_state)) {
    $sql .= " AND state = ?";
    $params[] = $filter_state;
    $types .= "s";
}
if (!empty($filter_type)) {
    $sql .= " AND property_type = ?";
    $params[] = $filter_type;
    $types .= "s";
}

$sql .= " ORDER BY property_id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container my-5">
    <div class="row mb-4 text-center">
        <div class="col-md-12">
            <h2 class="fw-bold display-5">Properties Catalog</h2>
            <p class="lead text-secondary">Find your ideal home within our verified listings.</p>
            <hr class="w-25 mx-auto bg-primary" style="height: 3px; opacity: 1;">
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-5 bg-light rounded-4">
        <div class="card-body p-4">
            <form method="GET" action="properties.php">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted small"><i class="fas fa-search me-1"></i> Search by Name</label>
                        <input type="text" name="search_name" class="form-control form-control-lg" placeholder="e.g. Elite Estate..." value="<?php echo htmlspecialchars($search_name); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small"><i class="fas fa-map-marker-alt me-1"></i> Filter by State</label>
                        <select name="filter_state" class="form-select form-select-lg">
                            <option value="">All States</option>
                            <option value="Johor" <?php if($filter_state=='Johor') echo 'selected'; ?>>Johor</option>
                            <option value="Penang" <?php if($filter_state=='Penang') echo 'selected'; ?>>Penang</option>
                            <option value="Sarawak" <?php if($filter_state=='Sarawak') echo 'selected'; ?>>Sarawak</option>
                            <option value="Selangor" <?php if($filter_state=='Selangor') echo 'selected'; ?>>Selangor</option>
                            <option value="Kuala Lumpur" <?php if($filter_state=='Kuala Lumpur') echo 'selected'; ?>>Kuala Lumpur</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small"><i class="fas fa-home me-1"></i> Property Type</label>
                        <select name="filter_type" class="form-select form-select-lg">
                            <option value="">All Types</option>
                            <option value="AFFORDABLE" <?php if($filter_type=='AFFORDABLE') echo 'selected'; ?>>Affordable</option>
                            <option value="TERRACE" <?php if($filter_type=='TERRACE') echo 'selected'; ?>>Terrace</option>
                            <option value="BUNGALOW" <?php if($filter_type=='BUNGALOW') echo 'selected'; ?>>Bungalow</option>
                            <option value="COMMERCIAL" <?php if($filter_type=='COMMERCIAL') echo 'selected'; ?>>Commercial</option>
                            <option value="APARTMENT" <?php if($filter_type=='APARTMENT') echo 'selected'; ?>>Apartment</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                
                // Image Mapping Logic
                $dbType = strtolower(trim($row['property_type']));
                $rawState = trim($row['state']);
                
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

                $formattedPrice = number_format($row['price'], 2);
                $availableUnits = isset($row['available_units']) ? $row['available_units'] : $row['total_units'];
                ?>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden hover-card">
                        <div class="position-relative bg-light" style="height: 250px;">
                            <img src="<?php echo htmlspecialchars($finalImg); ?>" 
                                 class="w-100 h-100" 
                                 alt="<?php echo htmlspecialchars($row['project_name']); ?>" 
                                 style="object-fit: cover; object-position: center;">
                            <span class="badge bg-primary position-absolute top-0 end-0 m-3 shadow">
                                <?php echo htmlspecialchars($row['property_type']); ?>
                            </span>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <h5 class="card-title fw-bold text-dark mb-1 text-truncate" title="<?php echo htmlspecialchars($row['project_name']); ?>">
                                <?php echo htmlspecialchars($row['project_name']); ?>
                            </h5>
                            <p class="text-muted small mb-4">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i> <?php echo htmlspecialchars($row['state']); ?>
                            </p>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block fw-bold" style="font-size: 0.7rem;">PRICE FROM</small>
                                    <h4 class="text-success fw-bold mb-0">RM <?php echo $formattedPrice; ?></h4>
                                </div>
                                <a href="property_detail.php?id=<?php echo $row['property_id']; ?>" class="btn btn-dark rounded-pill px-4 shadow-sm">Details</a>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 py-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-door-open me-1"></i> Available Units: <strong class="text-dark"><?php echo $availableUnits; ?></strong>
                            </small>
                        </div>
                    </div>
                </div>

                <?php
            }
        } else {
            echo '<div class="col-12 text-center py-5">
                    <i class="fas fa-search-minus fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted fw-bold">No properties found</h4>
                    <p class="text-muted">Try adjusting your search filters.</p>
                    <a href="properties.php" class="btn btn-outline-primary mt-2">Clear Filters</a>
                  </div>';
        }
        ?>
    </div>
</div>

<style>
.hover-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.hover-card:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,0.15)!important; }
</style>

<?php include_once '../includes/footer.php'; ?>