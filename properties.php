<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: properties.php (ROOT DIRECTORY)
 * DESCRIPTION: Staff/Admin property catalog. Fixed Federal Territories value mapping.
 */

include_once 'includes/header.php';
require_once 'includes/auth_check.php';
require_once 'includes/property_images.php';
protect_staff_admin_page($conn);

$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$filter_state = isset($_GET['filter_state']) ? trim($_GET['filter_state']) : '';
$filter_type = isset($_GET['filter_type']) ? trim($_GET['filter_type']) : '';

$sql = "SELECT * FROM properties WHERE (status = 'ACTIVE' OR status = 'AVAILABLE')";
$params = [];
$types = "";

if (!empty($search_name)) { $sql .= " AND project_name LIKE ?"; $params[] = "%$search_name%"; $types .= "s"; }
if (!empty($filter_state)) { $sql .= " AND state = ?"; $params[] = $filter_state; $types .= "s"; }

if ($filter_type === 'AFFORDABLE') {
    $sql .= " AND is_affordable = 1";
} elseif (!empty($filter_type)) {
    $sql .= " AND property_type = ? AND is_affordable = 0";
    $params[] = $filter_type;
    $types .= "s";
}

$sql .= " ORDER BY is_affordable DESC, property_id DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container my-5 py-4">
    <div class="row mb-4 text-center">
        <div class="col-md-12">
            <div class="section-kicker mb-2">Property inventory</div>
            <h2 class="fw-bold display-5 text-white">Inventory Catalog</h2>
            <p class="lead text-light opacity-75">Internal staff and administration management view</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-5 bg-light">
        <div class="card-body p-4">
            <form method="GET" action="properties.php">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted small"><i class="fas fa-search me-1"></i> Project Name</label>
                        <input type="text" name="search_name" class="form-control" value="<?php echo htmlspecialchars($search_name); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small"><i class="fas fa-map-marker-alt me-1"></i> State Region</label>
                        <select name="filter_state" class="form-select">
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
                                <option value="Kuala Lumpur" <?php if($filter_state=='Kuala Lumpur') echo 'selected'; ?>>WP Kuala Lumpur</option>
                                <option value="Labuan" <?php if($filter_state=='Labuan') echo 'selected'; ?>>WP Labuan</option>
                                <option value="Putrajaya" <?php if($filter_state=='Putrajaya') echo 'selected'; ?>>WP Putrajaya</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small"><i class="fas fa-home me-1"></i> Category</label>
                        <select name="filter_type" class="form-select">
                            <option value="">All Categories</option>
                            <option value="AFFORDABLE" <?php if($filter_type=='AFFORDABLE') echo 'selected'; ?>>Affordable</option>
                            <option value="TERRACE" <?php if($filter_type=='TERRACE') echo 'selected'; ?>>Terrace</option>
                            <option value="BUNGALOW" <?php if($filter_type=='BUNGALOW') echo 'selected'; ?>>Bungalow</option>
                            <option value="COMMERCIAL" <?php if($filter_type=='COMMERCIAL') echo 'selected'; ?>>Commercial</option>
                            <option value="APARTMENT" <?php if($filter_type=='APARTMENT') echo 'selected'; ?>>Apartment</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-dark w-100 shadow-sm"><i class="fas fa-sliders-h me-2"></i>Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $is_affordable = (intval($row['is_affordable']) === 1);
                $badge_text = $is_affordable ? "GOV AFFORDABLE" : htmlspecialchars($row['property_type']);
                $badge_class = $is_affordable ? "bg-success" : "bg-primary";
                
                $finalImg = property_catalog_image_path($row);
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 overflow-hidden hover-card">
                        <div class="position-relative" style="height: 220px;">
                            <img src="<?php echo htmlspecialchars($finalImg); ?>" class="w-100 h-100" style="object-fit: cover;">
                            <span class="badge <?php echo $badge_class; ?> position-absolute top-0 end-0 m-3 shadow z-3"><?php echo $badge_text; ?></span>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <h5 class="fw-bold text-dark text-truncate" style="line-height: 1.4;"><?php echo htmlspecialchars($row['project_name']); ?></h5>
                            <p class="text-secondary fw-bold small mb-2" style="font-size: 0.8rem;"><i class="fas fa-map-marker-alt text-danger me-1"></i> <?php echo htmlspecialchars($row['state']); ?></p>
                            
                            <?php if ($is_affordable): ?>
                                <p class="text-danger fw-bold mb-3" style="font-size: 0.85rem;"><i class="fas fa-id-card me-1"></i> Income Limit: RM <?php echo number_format($row['income_limit_rm'] ?? 0); ?></p>
                            <?php endif; ?>

                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <h4 class="text-success fw-bold mb-0">RM <?php echo number_format($row['price'], 2); ?></h4>
                                <a href="property_detail.php?id=<?php echo $row['property_id']; ?>" class="btn btn-outline-dark px-4 shadow-sm">View Details</a>
                            </div>
                        </div>
                        <div class="card-footer bg-light border-0 py-3 text-center border-top">
                            <span class="text-secondary fw-bold" style="font-size: 0.8rem;"><i class="fas fa-door-open me-2 text-dark"></i>Available Units: <strong class="fs-6 text-dark"><?php echo $row['total_units']; ?></strong></span>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="col-12 text-center py-5"><h4>No matching properties found.</h4></div>';
        }
        ?>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
