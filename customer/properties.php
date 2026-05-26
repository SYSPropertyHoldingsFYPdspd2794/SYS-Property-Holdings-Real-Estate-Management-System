<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/properties.php
 * DESCRIPTION: Customer property catalog. Upgraded with dynamic SOLD OUT badges and filtering parameters.
 */

include_once '../includes/header.php';
require_once '../includes/auth_check.php';
require_once '../includes/property_images.php';
protect_customer_page('CUSTOMER', $conn);

$account_id = $_SESSION['account_id'];

// WISHLIST LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_wishlist'])) {
    $prop_id = intval($_POST['property_id']);
    $chk = $conn->prepare("SELECT wishlist_id FROM wishlists WHERE customer_id = ? AND property_id = ?");
    $chk->bind_param("ii", $account_id, $prop_id);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        $del = $conn->prepare("DELETE FROM wishlists WHERE customer_id = ? AND property_id = ?");
        $del->bind_param("ii", $account_id, $prop_id);
        $del->execute();
    } else {
        $ins = $conn->prepare("INSERT INTO wishlists (customer_id, property_id) VALUES (?, ?)");
        $ins->bind_param("ii", $account_id, $prop_id);
        $ins->execute();
    }
    header("Location: properties.php?" . $_SERVER['QUERY_STRING']);
    exit();
}

$wishlist_array = [];
$w_res = $conn->query("SELECT property_id FROM wishlists WHERE customer_id = $account_id");
while($w = $w_res->fetch_assoc()) { $wishlist_array[] = $w['property_id']; }

$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$filter_state = isset($_GET['filter_state']) ? trim($_GET['filter_state']) : '';
$filter_type = isset($_GET['filter_type']) ? trim($_GET['filter_type']) : '';

// FIXED MATRIX: Expanded query layer scope to explicitly include SOLD_OUT assets in customer view
$sql = "SELECT * FROM properties WHERE (status = 'ACTIVE' OR status = 'AVAILABLE' OR status = 'SOLD_OUT')";
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

<div class="container my-5 property-catalog-page">
    <div class="row mb-4 text-center">
        <div class="col-md-12">
            <h2 class="display-5 text-uppercase tracking-widest luxury-title mt-4 catalog-title">Exclusive Collections</h2>
            <p class="catalog-subtitle tracking-wider mb-4">Discover unparalleled living across 13 states and 3 federal territories.</p>
            <hr class="w-10 mx-auto bg-gold" style="height: 2px; opacity: 1;">
        </div>
    </div>

    <div class="card shadow-lg border-0 mb-5 bg-white text-dark rounded-4 filter-glass">
        <div class="card-body p-4">
            <form method="GET" action="properties.php">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted small"><i class="fas fa-search me-1"></i> Project Name</label>
                        <input type="text" name="search_name" class="form-control catalog-input" value="<?php echo htmlspecialchars($search_name); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small"><i class="fas fa-map-marker-alt me-1"></i> State</label>
                        <select name="filter_state" class="form-select catalog-input">
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
                        <select name="filter_type" class="form-select catalog-input">
                            <option value="">All Categories</option>
                            <option value="AFFORDABLE" <?php if($filter_type=='AFFORDABLE') echo 'selected'; ?>>Affordable</option>
                            <option value="TERRACE" <?php if($filter_type=='TERRACE') echo 'selected'; ?>>Terrace</option>
                            <option value="BUNGALOW" <?php if($filter_type=='BUNGALOW') echo 'selected'; ?>>Bungalow</option>
                            <option value="COMMERCIAL" <?php if($filter_type=='COMMERCIAL') echo 'selected'; ?>>Commercial</option>
                            <option value="APARTMENT" <?php if($filter_type=='APARTMENT') echo 'selected'; ?>>Apartment</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-dark text-white w-100 fw-bold shadow-sm text-uppercase tracking-wider catalog-search-btn">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $is_affordable = ($row['is_affordable'] == 1);
                $is_sold_out = (trim($row['status']) === 'SOLD_OUT');
                
                // FIXED MATRIX: Determine active layout badge properties based on asset occupancy values
                if ($is_sold_out) {
                    $badge_text = "SOLD OUT";
                    $badge_class = "bg-danger text-white premium-badge";
                } else {
                    $badge_text = $is_affordable ? "GOV AFFORDABLE" : htmlspecialchars($row['property_type']);
                    $badge_class = $is_affordable ? "bg-success text-white premium-badge" : "bg-gold text-dark premium-badge";
                }
                
                $finalImg = property_catalog_image_path($row, $root_prefix, '../');
                ?>
                <div class="col-lg-4 col-md-6 mb-5">
                    <div class="card h-100 border-0 rounded-4 overflow-hidden hover-card bg-white text-dark">
                        <div class="position-relative overflow-hidden" style="height: 280px;">
                            <img src="<?php echo htmlspecialchars($finalImg); ?>" class="w-100 h-100 image-zoom" style="object-fit: cover;">
                            <div class="image-overlay"></div>
                            <span class="badge <?php echo $badge_class; ?> position-absolute top-0 end-0 m-4 shadow-sm z-3 text-uppercase"><?php echo $badge_text; ?></span>
                            
                            <form method="POST" class="position-absolute bottom-0 end-0 m-3 z-3">
                                <input type="hidden" name="property_id" value="<?php echo $row['property_id']; ?>">
                                <button type="submit" name="toggle_wishlist" class="btn btn-dark bg-opacity-75 text-white rounded-circle shadow-lg p-0 d-flex align-items-center justify-content-center luxury-wishlist-btn" title="Add to Wishlist">
                                    <i class="<?php echo in_array($row['property_id'], $wishlist_array) ? 'fas text-gold' : 'far'; ?> fa-heart fs-6"></i>
                                </button>
                            </form>
                        </div>
                        <div class="card-body p-4 p-xl-5 d-flex flex-column position-relative">
                            <p class="text-uppercase tracking-wider text-muted mb-2" style="font-size: 0.75rem;"><i class="fas fa-map-marker-alt text-gold me-2"></i> <?php echo htmlspecialchars($row['state']); ?></p>
                            <h4 class="fw-light text-dark text-truncate mb-3 luxury-title"><?php echo htmlspecialchars($row['project_name']); ?></h4>
                            
                            <?php if ($is_affordable): ?>
                                <p class="text-muted fw-bold small mb-3"><i class="fas fa-id-card text-gold me-2"></i> Income Limit: <span class="text-dark">RM <?php echo number_format($row['income_limit_rm'] ?? 0); ?></span></p>
                            <?php endif; ?>

                            <div class="mt-auto pt-4 border-top border-light d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="d-block text-muted small text-uppercase tracking-wider" style="font-size: 0.65rem;">Starting Price</span>
                                    <h5 class="text-dark fw-bold mb-0">RM <?php echo number_format($row['price'], 2); ?></h5>
                                </div>
                                <a href="property_detail.php?id=<?php echo $row['property_id']; ?>" class="btn btn-outline-dark rounded-pill px-4 py-2 shadow-sm tracking-wider text-uppercase view-btn" style="font-size: 0.75rem;">View</a>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pb-4 pt-0 px-4 px-xl-5">
                            <div class="d-flex align-items-center rounded-pill p-2 px-3" style="background-color: rgba(255, 192, 0, 0.15);">
                                <i class="fas fa-key text-gold me-2"></i>
                                <small class="text-uppercase tracking-wider fw-bold" style="font-size: 0.65rem; color: #cc9a00;">Availability:</small>
                                <small class="ms-auto fw-bold <?php echo $is_sold_out ? 'text-danger' : 'text-dark'; ?>"><?php echo $is_sold_out ? 'SOLD OUT' : $row['total_units'] . ' Units'; ?></small>
                            </div>
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

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Montserrat:wght@300;400;500;600&display=swap');

.luxury-title { font-family: 'Playfair Display', serif; }
.tracking-wider { letter-spacing: 0.1em; }
.tracking-widest { letter-spacing: 0.2em; }
.text-gold { color: #FFC000 !important; }
.bg-gold { background-color: #FFC000 !important; }

.property-catalog-page {
    color: #f4f1e8;
}

.catalog-title {
    color: #ffffff;
}

.catalog-subtitle {
    color: #ddd7cc;
}

.filter-glass {
    background: rgba(255, 255, 255, 0.96) !important;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(20,24,31,0.08) !important;
    box-shadow: 0 18px 45px rgba(20,24,31,0.08) !important;
}

.catalog-input {
    min-height: 46px;
    border-color: rgba(20,24,31,0.16);
    color: #15191f;
    background-color: #fff;
}

.catalog-input:focus {
    border-color: #FFC000;
    box-shadow: 0 0 0 0.18rem rgba(255,192,0,0.2);
}

.catalog-search-btn {
    min-height: 46px;
    background: #11151b;
    border-color: #11151b;
}

.hover-card { 
    transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease; 
    border: 1px solid rgba(20,24,31,0.08) !important; 
    box-shadow: 0 12px 32px rgba(20,24,31,0.07) !important;
    background: #ffffff !important;
    color: #212529;
}
.hover-card:hover { 
    transform: translateY(-6px); 
    border-color: rgba(255,192,0,0.38) !important;
    box-shadow: 0 22px 44px rgba(20,24,31,0.14) !important; 
}
.hover-card:hover .image-zoom {
    transform: scale(1.05);
}

.image-zoom {
    transition: transform 0.8s ease;
}

.image-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 50%;
    background: linear-gradient(to top, rgba(0,0,0,0.56) 0%, rgba(0,0,0,0.08) 72%, rgba(0,0,0,0) 100%);
    z-index: 1;
}

.premium-badge { 
    letter-spacing: 1px; 
    font-weight: 700; 
    font-size: 0.65rem; 
    padding: 0.6em 1.2em; 
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,0.2);
}

.luxury-wishlist-btn {
    background-color: rgba(5,7,10,0.88) !important;
    color: #ffffff !important;
}

.luxury-wishlist-btn {
    width: 45px; 
    height: 45px;
    transition: all 0.3s ease;
    border: 1px solid rgba(255,255,255,0.35);
}
.luxury-wishlist-btn:hover {
    background-color: #000 !important;
    transform: scale(1.1);
}

.view-btn:hover {
    background: #11151b;
    color: #ffffff;
}

@media (max-width: 767.98px) {
    .tracking-wider { letter-spacing: 0.06em; }
    .tracking-widest { letter-spacing: 0.1em; }
    .display-5 { font-size: 2rem; }
    .hover-card .card-body {
        padding: 1.5rem !important;
    }
}
</style>

<?php include_once '../includes/footer.php'; ?>
