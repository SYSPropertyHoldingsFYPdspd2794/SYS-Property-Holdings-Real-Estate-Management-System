<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'STAFF') {
    header("Location: ../login.php");
    exit();
}
include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];

// 獲取員工與分配的州屬
$staff_stmt = $conn->prepare("SELECT full_name, assigned_state FROM staff WHERE staff_id = ?");
$staff_stmt->bind_param("i", $account_id);
$staff_stmt->execute();
$staff = $staff_stmt->get_result()->fetch_assoc();
$assigned_state = $staff['assigned_state'] ?? 'Unassigned';
$first_name = explode(' ', trim($staff['full_name']))[0];

// 待辦事項 KPI
$new_leads_count = $conn->query("SELECT COUNT(*) FROM appointments WHERE assigned_staff_id = $account_id AND status = 'ASSIGNED'")->fetch_row()[0];

$pending_verifications = 0;
if ($assigned_state !== 'Unassigned') {
    $ver_stmt = $conn->prepare("SELECT COUNT(*) FROM affordable_housing_applications a JOIN properties p ON a.property_id = p.property_id WHERE p.state = ? AND a.status = 'PENDING_REVIEW'");
    $ver_stmt->bind_param("s", $assigned_state);
    $ver_stmt->execute();
    $pending_verifications = $ver_stmt->get_result()->fetch_row()[0];
}

$completed_appts_month = $conn->query("SELECT COUNT(*) FROM appointments WHERE assigned_staff_id = $account_id AND status = 'COMPLETED' AND MONTH(appointment_date) = MONTH(CURRENT_DATE()) AND YEAR(appointment_date) = YEAR(CURRENT_DATE())")->fetch_row()[0];

// 區域庫存速覽
$state_projects = 0;
$state_units = 0;
if ($assigned_state !== 'Unassigned') {
    $inventory_stmt = $conn->prepare("SELECT COUNT(*) as total_projects, SUM(total_units) as total_units FROM properties WHERE state = ? AND status = 'ACTIVE'");
    $inventory_stmt->bind_param("s", $assigned_state);
    $inventory_stmt->execute();
    $inventory_res = $inventory_stmt->get_result()->fetch_assoc();
    $state_projects = $inventory_res['total_projects'] ?? 0;
    $state_units = $inventory_res['total_units'] ?? 0;
}

// 今日行程表 (Today's Schedule)
$today_stmt = $conn->prepare("
    SELECT a.appointment_id, a.appointment_time, a.service_type, c.full_name, c.phone_number, p.project_name 
    FROM appointments a
    JOIN customers c ON a.customer_id = c.customer_id
    JOIN properties p ON a.property_id = p.property_id
    WHERE a.assigned_staff_id = ? AND a.appointment_date = CURDATE() AND a.status = 'ASSIGNED'
    ORDER BY a.appointment_time ASC
");
$today_stmt->bind_param("i", $account_id);
$today_stmt->execute();
$today_schedule = $today_stmt->get_result();

$page_title = "Staff Workspace";
include '../includes/header.php';
?>

<div class="container my-5">
    <!-- Welcome Banner -->
    <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #101827, #1e293b); color: white;">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <img src="../SYS%20Property%20Catalog/SYS_Property_Holdings_Icon.jpeg" alt="Logo" style="height: 56px; border-radius: 8px;" class="shadow-sm">
                <div>
                    <h3 class="fw-bold mb-1">Hello, <?php echo htmlspecialchars($first_name); ?>!</h3>
                    <p class="mb-0 text-light opacity-75"><i class="fas fa-map-marker-alt text-danger me-2"></i>Assigned Region: <strong class="text-white"><?php echo htmlspecialchars($assigned_state); ?></strong></p>
                </div>
            </div>
            <div class="d-none d-md-block text-end">
                <p class="mb-0 fs-5 fw-bold text-info"><?php echo date('l, d M Y'); ?></p>
            </div>
        </div>
    </div>

    <!-- Action Required KPI Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary bg-opacity-10 border-start border-primary border-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-primary text-uppercase mb-3"><i class="fas fa-user-clock me-2"></i>New Leads Assigned</h6>
                    <h2 class="fw-bold text-white m-0"><?php echo $new_leads_count; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-warning bg-opacity-10 border-start border-warning border-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-warning text-uppercase mb-3" style="color: #b78a00 !important;"><i class="fas fa-file-signature me-2"></i>Pending Reviews</h6>
                    <h2 class="fw-bold text-white m-0"><?php echo $pending_verifications; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-success bg-opacity-10 border-start border-success border-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-success text-uppercase mb-3"><i class="fas fa-chart-line me-2"></i>Monthly Completed</h6>
                    <h2 class="fw-bold text-white m-0"><?php echo $completed_appts_month; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-info bg-opacity-10 border-start border-info border-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-info text-uppercase mb-3" style="color: #0b8e88 !important;"><i class="fas fa-building me-2"></i>Total Project</h6>
                    <h4 class="fw-bold text-white mb-0"><?php echo number_format($state_units); ?> <small class="text-muted fs-6">projects</small></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Today's Schedule -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0"><i class="fas fa-calendar-day text-primary me-2"></i>Today's Schedule</h5>
                    <a href="appointments.php" class="btn btn-sm btn-outline-dark rounded-pill fw-bold">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Time</th>
                                    <th>Customer</th>
                                    <th>Service Type</th>
                                    <th>Property</th>
                                    <th class="pe-4 text-end">Contact</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($today_schedule->num_rows > 0): ?>
                                    <?php while ($appt = $today_schedule->fetch_assoc()): ?>
                                        <?php
                                            // 解析大馬電話號碼並生成一鍵聯絡 WhatsApp 網址
                                            $phone = preg_replace('/[^0-9]/', '', $appt['phone_number']);
                                            if (strpos($phone, '60') !== 0 && strpos($phone, '0') === 0) {
                                                $phone = '60' . substr($phone, 1);
                                            }
                                            $time_formatted = date('h:i A', strtotime($appt['appointment_time']));
                                            $msg = urlencode("Hi {$appt['full_name']}, this is {$first_name} from SYS Property Holdings. Just sending a quick reminder for our appointment today at {$time_formatted} for {$appt['project_name']}. See you later!");
                                            $wa_link = "https://wa.me/{$phone}?text={$msg}";
                                        ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark"><?php echo $time_formatted; ?></td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($appt['full_name']); ?></td>
                                            <td><span class="badge bg-secondary rounded-pill px-3"><?php echo str_replace('_', ' ', htmlspecialchars($appt['service_type'])); ?></span></td>
                                            <td class="text-muted"><?php echo htmlspecialchars($appt['project_name']); ?></td>
                                            <td class="pe-4 text-end">
                                                <a href="<?php echo $wa_link; ?>" target="_blank" class="btn btn-sm btn-success rounded-pill fw-bold shadow-sm">
                                                    <i class="fab fa-whatsapp me-1"></i> WhatsApp
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-mug-hot fa-3x mb-3 opacity-25"></i>
                                            <p class="m-0 fw-bold">No appointments scheduled for today.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold m-0"><i class="fas fa-bolt text-warning me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body p-4">
                    <div class="list-group list-group-flush gap-2">
                        <a href="appointments.php" class="list-group-item list-group-item-action bg-light border-0 rounded-3 p-3 fw-bold text-dark d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-calendar-check text-primary me-3"></i>Manage Appointments</span>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                        <a href="verifications.php" class="list-group-item list-group-item-action bg-light border-0 rounded-3 p-3 fw-bold text-dark d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-file-signature text-success me-3"></i>Application Review</span>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                        <a href="state_inventory.php" class="list-group-item list-group-item-action bg-light border-0 rounded-3 p-3 fw-bold text-dark d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-building text-info me-3"></i>View Regional Inventory</span>
                            <i class="fas fa-chevron-right text-muted small"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>