<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}
include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];

// 獲取客戶資料
$cust_stmt = $conn->prepare("SELECT full_name, monthly_income FROM customers WHERE customer_id = ?");
$cust_stmt->bind_param("i", $account_id);
$cust_stmt->execute();
$customer = $cust_stmt->get_result()->fetch_assoc();
$monthly_income = (float)$customer['monthly_income'];
$first_name = explode(' ', trim($customer['full_name']))[0];

// 快速狀態統計
$wishlist_count = $conn->query("SELECT COUNT(*) FROM wishlists WHERE customer_id = $account_id")->fetch_row()[0];
$appt_count = $conn->query("SELECT COUNT(*) FROM appointments WHERE customer_id = $account_id AND status IN ('REQUESTED', 'ASSIGNED')")->fetch_row()[0];
$app_count = $conn->query("SELECT COUNT(*) FROM affordable_housing_applications WHERE customer_id = $account_id AND status IN ('PENDING_REVIEW', 'APPROVED_FOR_DRAW')")->fetch_row()[0];

// 獲取系統基準利率
$rate_res = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'BASE_INTEREST_RATE'");
$base_rate = $rate_res && $rate_res->num_rows > 0 ? $rate_res->fetch_assoc()['setting_value'] : '3.85';

// 為您推薦 (根據月收入推薦符合資格的可負擔房屋，或是普通房產)
$rec_stmt = $conn->prepare("
    SELECT property_id, project_name, state, property_type, price, image_filename, is_affordable 
    FROM properties 
    WHERE status = 'ACTIVE' 
    AND ((is_affordable = 1 AND income_limit_rm >= ?) OR (is_affordable = 0))
    ORDER BY property_id DESC LIMIT 4
");
$rec_stmt->bind_param("d", $monthly_income);
$rec_stmt->execute();
$recommendations = $rec_stmt->get_result();

$page_title = "Customer Dashboard";
include '../includes/header.php';
?>

<div class="container my-5">
    <!-- Welcome Banner -->
    <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, var(--luxury-bg), var(--luxury-ink)); color: white;">
        <div class="card-body p-4 p-md-5 d-flex justify-content-between align-items-center">
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
            <a href="track_status.php" class="text-decoration-none">
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
            <div class="d-flex justify-content-between align-items-end mb-3">
                <h4 class="fw-bold m-0"><i class="fas fa-star text-warning me-2"></i>Recommended For You</h4>
                <a href="../properties.php" class="btn btn-sm btn-outline-light text-white rounded-pill fw-bold">View More</a>
            </div>
            
            <div class="row g-3">
                <?php if ($recommendations->num_rows > 0): ?>
                    <?php while ($prop = $recommendations->fetch_assoc()): ?>
                        <?php 
                        // 處理圖片路徑
                        $img_path = '../SYS Property Catalog/' . htmlspecialchars($prop['image_filename']);
                        if (strpos($prop['image_filename'], 'Custom/') === 0) {
                            $img_path = '../storage/property_images/' . htmlspecialchars($prop['image_filename']);
                        }
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
                                    <a href="../property_detail.php?id=<?php echo $prop['property_id']; ?>" class="stretched-link"></a>
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
                    <h6 class="fw-bold text-dark"><i class="fas fa-percent text-primary me-2"></i>Current Interest Rate</h6>
                    <div class="display-5 fw-bold text-primary mb-2"><?php echo htmlspecialchars($base_rate); ?>%</div>
                    <p class="text-muted small mb-3">Based on standard national base rates. Use our calculator to estimate your monthly commitments.</p>
                    <a href="../financial_planner.php" class="btn btn-primary w-100 fw-bold rounded-pill shadow-sm">Loan Calculator</a>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-compass text-info me-2"></i>Explore</h6>
                    <div class="d-grid gap-2">
                        <a href="../showrooms.php" class="btn btn-outline-dark text-start rounded-pill fw-bold"><i class="fas fa-map-marked-alt me-2 text-muted"></i>Find Showrooms</a>
                        <a href="../bank_rates.php" class="btn btn-outline-dark text-start rounded-pill fw-bold"><i class="fas fa-university me-2 text-muted"></i>Compare Bank Rates</a>
                        <a href="profile.php" class="btn btn-outline-dark text-start rounded-pill fw-bold"><i class="fas fa-user-edit me-2 text-muted"></i>Update My Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>