<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: properties.php (ROOT DIRECTORY)
 * DESCRIPTION: Public catalog view strictly for STAFF and ADMIN users.
 */

// Notice path changes: includes are now direct
include_once 'includes/header.php';
require_once 'includes/auth_check.php';
include_once '../includes/header.php';
/** @var string $root_prefix */
/** @var mysqli $conn */
require_once '../includes/auth_check.php';
protect_staff_admin_page($conn);

$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$filter_state = isset($_GET['filter_state']) ? trim($_GET['filter_state']) : '';
$filter_type = isset($_GET['filter_type']) ? trim($_GET['filter_type']) : '';

$sql = "SELECT * FROM properties WHERE (status = 'ACTIVE' OR status = 'AVAILABLE')";
$params = [];
$types = "";

if (!empty($search_name)) {
    $sql .= " AND project_name LIKE ?"; $params[] = "%" . $search_name . "%"; $types .= "s";
}
if (!empty($filter_state)) {
    $sql .= " AND state = ?"; $params[] = $filter_state; $types .= "s";
}
if (!empty($filter_type)) {
    $sql .= " AND property_type = ?"; $params[] = $filter_type; $types .= "s";
}
$sql .= " ORDER BY property_id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container my-5">
    <div class="row mb-4 text-center">
        <div class="col-md-12">
            <h2 class="fw-bold display-5">System Properties Catalog</h2>
            <p class="lead text-secondary">Internal inventory view for Staff and Administrators.</p>
            <hr class="w-25 mx-auto bg-dark" style="height: 3px; opacity: 1;">
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-5 bg-light rounded-4">
        <div class="card-body p-4">
            <form method="GET" action="properties.php">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted small">Search by Name</label>
                        <input type="text" name="search_name" class="form-control form-control-lg" value="<?php echo htmlspecialchars($search_name); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small">Filter by State</label>
                        <select name="filter_state" class="form-select form-select-lg">
                            <option value="">All States</option>
                            <option value="Johor" <?php if($filter_state=='Johor') echo 'selected'; ?>>Johor</option>
                            <option value="Penang" <?php if($filter_state=='Penang') echo 'selected'; ?>>Penang</option>
                            <option value="Selangor" <?php if($filter_state=='Selangor') echo 'selected'; ?>>Selangor</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small">Property Type</label>
                        <select name="filter_type" class="form-select form-select-lg">
                            <option value="">All Types</option>
                            <option value="AFFORDABLE" <?php if($filter_type=='AFFORDABLE') echo 'selected'; ?>>Affordable</option>
                            <option value="COMMERCIAL" <?php if($filter_type=='COMMERCIAL') echo 'selected'; ?>>Commercial</option>
                            <option value="TERRACE" <?php if($filter_type=='TERRACE') echo 'selected'; ?>>Terrace</option>
                            <option value="APARTMENT" <?php if($filter_type=='APARTMENT') echo 'selected'; ?>>Apartment</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-dark btn-lg w-100 fw-bold shadow-sm">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $dbType = strtolower(trim($row['property_type']));
                $rawState = trim($row['state']);
                if (strtoupper($rawState) === 'PENANG') $rawState = 'Pulau Pinang';
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
                    if (file_exists($testPath)) { $finalImg = $testPath; break; }
                }

                $formattedPrice = number_format($row['price'], 2);
                ?>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                        <div class="position-relative bg-light" style="height: 250px;">
                            <img src="<?php echo htmlspecialchars($finalImg); ?>" class="w-100 h-100" style="object-fit: cover;">
                            <span class="badge bg-dark position-absolute top-0 end-0 m-3 shadow"><?php echo htmlspecialchars($row['property_type']); ?></span>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <h5 class="card-title fw-bold text-dark mb-1 text-truncate"><?php echo htmlspecialchars($row['project_name']); ?></h5>
                            <p class="text-muted small mb-4"><i class="fas fa-map-marker-alt text-danger me-1"></i> <?php echo htmlspecialchars($row['state']); ?></p>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <div><h4 class="text-success fw-bold mb-0">RM <?php echo $formattedPrice; ?></h4></div>
                                <a href="property_detail.php?id=<?php echo $row['property_id']; ?>" class="btn btn-outline-dark rounded-pill px-4">View</a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
            }
        } else {
            echo '<div class="col-12 text-center py-5"><p class="text-muted">No properties found in database.</p></div>';
        }
        ?>
    </div>
</div>
<?php include_once 'includes/footer.php'; ?>