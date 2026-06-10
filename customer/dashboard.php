<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}
include '../includes/db_connect.php';
include_once '../includes/property_images.php';

$account_id = $_SESSION['account_id'];
$conn->query("ALTER TABLE appointments ADD COLUMN IF NOT EXISTS customer_deleted_at DATETIME DEFAULT NULL");

// 獲取客戶資料
$cust_stmt = $conn->prepare("SELECT full_name, monthly_income FROM customers WHERE customer_id = ?");
$cust_stmt->bind_param("i", $account_id);
$cust_stmt->execute();
$customer = $cust_stmt->get_result()->fetch_assoc();
$monthly_income = (float)$customer['monthly_income'];
$first_name = explode(' ', trim($customer['full_name']))[0];

// 快速狀態統計
$wishlist_count = $conn->query("SELECT COUNT(*) FROM wishlists WHERE customer_id = $account_id")->fetch_row()[0];
$appt_count = $conn->query("SELECT COUNT(*) FROM appointments WHERE customer_id = $account_id AND status IN ('REQUESTED', 'ASSIGNED') AND TIMESTAMP(appointment_date, appointment_time) > NOW() AND customer_deleted_at IS NULL")->fetch_row()[0];
$app_count = $conn->query("SELECT COUNT(*) FROM affordable_housing_applications WHERE customer_id = $account_id AND status IN ('PENDING_REVIEW', 'APPROVED_FOR_DRAW')")->fetch_row()[0];

// 獲取系統基準利率 (BASE INTEREST RATE from banks table)
$rate_res = $conn->query("SELECT interest_rate FROM banks WHERE bank_name = 'BASE INTEREST RATE'");
$base_rate = $rate_res && $rate_res->num_rows > 0 ? number_format($rate_res->fetch_assoc()['interest_rate'], 2) : '2.75';

$rec_state = isset($_GET['rec_state']) ? trim($_GET['rec_state']) : '';

// 獲取活躍的州別，供 Filter 使用
$state_res = $conn->query("SELECT DISTINCT state FROM properties WHERE status = 'ACTIVE' ORDER BY state ASC");
$active_states = [];
while ($row = $state_res->fetch_assoc()) {
    $active_states[] = $row['state'];
}

// 為您推薦 (根據月收入推薦符合資格的可負擔房屋，或是普通房產，並支援州別篩選)
$rec_sql = "
    SELECT property_id, project_name, state, property_type, price, image_filename, is_affordable 
    FROM properties 
    WHERE status = 'ACTIVE' 
    AND ((is_affordable = 1 AND income_limit_rm >= ?) OR (is_affordable = 0))
";
if (!empty($rec_state)) {
    $rec_sql .= " AND state = ?";
}
$rec_sql .= " ORDER BY (is_affordable = 1 AND income_limit_rm >= ?) DESC, property_id DESC LIMIT 4";

$rec_stmt = $conn->prepare($rec_sql);
if (!empty($rec_state)) {
    $rec_stmt->bind_param("dsd", $monthly_income, $rec_state, $monthly_income);
} else {
    $rec_stmt->bind_param("dd", $monthly_income, $monthly_income);
}
$rec_stmt->execute();
$recommendations = $rec_stmt->get_result();

$page_title = "Customer Dashboard";
include '../includes/header.php';
?>

<div class="container my-5">
    <!-- Welcome Banner -->
    <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, var(--luxury-bg), var(--luxury-ink)); color: white;">
        <div class="card-body p-4 p-md-5 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-4">
                <img src="../SYS%20Property%20Catalog/SYS_Property_Holdings_Icon.jpeg" alt="Logo" style="height: 80px; border-radius: 12px;" class="shadow d-none d-sm-block">
                <div>
                    <?php if ($appt_count > 0): ?>
                    <span class="badge bg-warning text-dark mb-2 fw-bold px-3 py-2 rounded-pill"><i class="fas fa-bell me-1"></i> You have upcoming appointments!</span>
                <?php endif; ?>
                <h2 class="fw-bold mb-2">Welcome back, <?php echo htmlspecialchars($first_name); ?>!</h2>
                <p class="mb-0 text-light opacity-75">Ready to continue your journey to your dream home?</p>
            </div>
            <div class="d-none d-md-block text-end">
                <i class="fas fa-home fa-4x opacity-25"></i>
            </div>
        </div>
    </div>

    <!-- Quick Status Widgets -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <a href="wishlist.php" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 hover-card">
                    <div class="card-body p-4 text-center">
                        <div class="step-icon mb-3 bg-danger bg-opacity-10 text-danger" style="width: 60px; height: 60px; font-size: 1.5rem;">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3 class="fw-bold text-white mb-1"><?php echo $wishlist_count; ?></h3>
                        <p class="text-muted fw-bold mb-0 text-uppercase small">Saved Properties</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="track_status.php" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 hover-card">
                    <div class="card-body p-4 text-center">
                        <div class="step-icon mb-3 bg-primary bg-opacity-10 text-primary" style="width: 60px; height: 60px; font-size: 1.5rem;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h3 class="fw-bold text-white mb-1"><?php echo $appt_count; ?></h3>
                        <p class="text-muted fw-bold mb-0 text-uppercase small">Upcoming Appointments</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="track_status.php?tab=housing" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 hover-card">
                    <div class="card-body p-4 text-center">
                        <div class="step-icon mb-3 bg-success bg-opacity-10 text-success" style="width: 60px; height: 60px; font-size: 1.5rem;">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        <h3 class="fw-bold text-white mb-1"><?php echo $app_count; ?></h3>
                        <p class="text-muted fw-bold mb-0 text-uppercase small">Active Applications</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="row g-4">
        <!-- Recommendations -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <h4 class="fw-bold m-0"><i class="fas fa-star text-warning me-2"></i>Recommended For You</h4>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light rounded-pill px-3 d-flex align-items-center gap-2 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Filter by State">
                            <i class="fas fa-filter text-warning"></i>
                            <span class="fw-bold"><?php echo empty($rec_state) ? 'Filter Region' : htmlspecialchars($rec_state); ?></span>
                            <i class="fas fa-chevron-down opacity-75" style="font-size: 0.7em;"></i>
                        </button>
                        <ul class="dropdown-menu shadow-sm border-0 rounded-3" style="max-height: 300px; overflow-y: auto;">
                            <li><a class="dropdown-item fw-bold <?php echo empty($rec_state) ? 'active' : ''; ?>" href="dashboard.php">All Regions</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php foreach ($active_states as $st): ?>
                                <li><a class="dropdown-item <?php echo $rec_state === $st ? 'active bg-primary text-white' : ''; ?>" href="dashboard.php?rec_state=<?php echo urlencode($st); ?>"><?php echo htmlspecialchars($st); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <a href="properties.php" class="btn btn-sm btn-outline-light text-white rounded-pill fw-bold">View More</a>
            </div>
            
            <div class="row g-3">
                <?php if ($recommendations->num_rows > 0): ?>   
                    <?php while ($prop = $recommendations->fetch_assoc()): ?>
                        <?php 
                        $img_path = property_catalog_image_path($prop, '../', '../');
                        ?>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-4 h-100 hover-card overflow-hidden">
                                <img src="<?php echo $img_path; ?>" class="card-img-top" onerror="this.src='https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=500&h=300&fit=crop'" alt="<?php echo htmlspecialchars($prop['project_name']); ?>" style="height: 180px; object-fit: cover;">
                                <div class="card-body p-3">
                                    <span class="badge <?php echo $prop['is_affordable'] ? 'bg-success' : 'bg-primary'; ?> mb-2">
                                        <?php echo $prop['is_affordable'] ? 'Affordable' : htmlspecialchars($prop['property_type']); ?>
                                    </span>
                                    <h6 class="fw-bold text-dark text-truncate mb-1"><?php echo htmlspecialchars($prop['project_name']); ?></h6>
                                    <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt text-danger me-1"></i> <?php echo htmlspecialchars($prop['state']); ?></p>
                                    <h5 class="text-success fw-bold m-0">RM <?php echo number_format($prop['price'], 2); ?></h5>
                                    <a href="property_detail.php?id=<?php echo $prop['property_id']; ?>" class="stretched-link"></a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="p-4 bg-light rounded-4 text-center text-muted">
                            <p class="m-0">No recommendations available at the moment.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Sidebar (Financial Tools & Quick Links) -->
        <div class="col-lg-4">
            <h4 class="fw-bold mb-3"><i class="fas fa-tools text-secondary me-2"></i>Quick Tools</h4>
            
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light border-start border-primary border-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark"><i class="fas fa-percent text-primary me-2"></i>Current Base Interest Rate</h6>
                    <div class="display-5 fw-bold text-primary mb-2"><?php echo htmlspecialchars($base_rate); ?>%</div>
                    <p class="text-muted small mb-3">This represents the current Malaysian Federal Base Rate. Use our calculator to estimate your monthly commitments based on this rate.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-compass text-info me-2"></i>Explore</h6>
                    <div class="d-grid gap-2">
                        <a href="profile.php" class="btn btn-outline-dark text-start rounded-pill fw-bold"><i class="fas fa-user-edit me-2 text-muted"></i>Update My Profile</a>
                        <a href="../showrooms.php" class="btn btn-outline-dark text-start rounded-pill fw-bold"><i class="fas fa-map-marked-alt me-2 text-muted"></i>Find Showrooms</a>
                        <a href="../bank_rates.php" class="btn btn-outline-dark text-start rounded-pill fw-bold"><i class="fas fa-university me-2 text-muted"></i>Compare Bank Rates</a>
                        <a href="../financial_planner.php" class="btn btn-outline-dark text-start rounded-pill fw-bold"><i class="fas fa-calculator me-2 text-muted"></i>Financial Planner</a>
                        <a href="../buying_journey.php" class="btn btn-outline-dark text-start rounded-pill fw-bold"><i class="fas fa-route me-2 text-muted"></i>Buying Journey</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
