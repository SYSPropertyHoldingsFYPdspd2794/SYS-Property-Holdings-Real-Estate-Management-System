<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';
require_once '../includes/property_images.php';

protect_admin_page('ADMIN', $conn);

$page_title = "Interest Timeline";
include '../includes/header.php';

$summary = [
    'total_actions' => 0,
    'unique_customers' => 0,
    'unique_properties' => 0,
    'top_interest' => 0
];

$summary_result = $conn->query("
    SELECT
        COUNT(*) AS total_actions,
        COUNT(DISTINCT customer_id) AS unique_customers,
        COUNT(DISTINCT property_id) AS unique_properties
    FROM wishlists
");
if ($summary_result) {
    $summary = array_merge($summary, $summary_result->fetch_assoc() ?: []);
}

$top_result = $conn->query("
    SELECT COUNT(*) AS top_interest
    FROM wishlists
    GROUP BY property_id
    ORDER BY top_interest DESC
    LIMIT 1
");
if ($top_result && $top_result->num_rows > 0) {
    $summary['top_interest'] = (int)$top_result->fetch_assoc()['top_interest'];
}

$query = "
    SELECT 
        w.wishlist_id,
        w.created_at,
        c.full_name AS customer_name,
        c.occupation,
        p.property_id,
        p.property_code,
        p.project_name,
        p.state,
        p.property_type,
        p.price,
        p.total_units,
        p.built_up_sqft,
        p.income_limit_rm,
        p.status,
        p.image_filename,
        p.is_affordable,
        popularity.total_interest
    FROM wishlists w
    JOIN customers c ON w.customer_id = c.customer_id
    JOIN properties p ON w.property_id = p.property_id
    JOIN (
        SELECT property_id, COUNT(*) AS total_interest
        FROM wishlists
        GROUP BY property_id
    ) popularity ON popularity.property_id = p.property_id
    ORDER BY w.created_at DESC, w.wishlist_id DESC
    LIMIT 100
";
$result = $conn->query($query);
?>

<div class="container-fluid px-4 px-lg-5 my-5 interest-timeline-page">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <p class="text-uppercase tracking-wider text-gold mb-2 small">Admin Market Signal</p>
            <h2 class="fw-bold m-0 text-white">Interest Timeline</h2>
            <p class="text-light opacity-75 mt-2 mb-0">
                Every wishlist action is listed below so you can spot which properties are getting hot.
            </p>
        </div>
        <a href="business_reports.php?report=market" class="btn btn-primary fw-bold px-4">
            <i class="fas fa-chart-pie me-2"></i>Market Report
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="timeline-metric">
                <span>Total Wishlist Actions</span>
                <strong><?php echo number_format((int)$summary['total_actions']); ?></strong>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="timeline-metric">
                <span>Interested Customers</span>
                <strong><?php echo number_format((int)$summary['unique_customers']); ?></strong>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="timeline-metric">
                <span>Properties Touched</span>
                <strong><?php echo number_format((int)$summary['unique_properties']); ?></strong>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="timeline-metric">
                <span>Hottest Property Adds</span>
                <strong><?php echo number_format((int)$summary['top_interest']); ?></strong>
            </div>
        </div>
    </div>

    <div class="interest-grid-wrap bg-white text-dark shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle interest-grid mb-0">
                <thead>
                    <tr>
                        <th>Moment</th>
                        <th>Customer</th>
                        <th>Property</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Specs</th>
                        <th>Units</th>
                        <th>Status</th>
                        <th>Heat</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($log = $result->fetch_assoc()): ?>
                            <?php
                            $is_affordable = (int)$log['is_affordable'] === 1;
                            $is_sold_out = trim((string)$log['status']) === 'SOLD_OUT';
                            $final_img = property_catalog_image_path($log, '../', '../');
                            ?>
                            <tr>
                                <td class="moment-cell">
                                    <strong><?php echo date('d M Y', strtotime($log['created_at'])); ?></strong>
                                    <small><?php echo date('g:i:s A', strtotime($log['created_at'])); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($log['customer_name']); ?></strong>
                                    <small class="d-block text-muted"><?php echo htmlspecialchars($log['occupation'] ?: 'Occupation not set'); ?></small>
                                </td>
                                <td class="property-cell">
                                    <div class="property-cell-inner">
                                        <img src="<?php echo htmlspecialchars($final_img); ?>" alt="<?php echo htmlspecialchars($log['project_name']); ?>">
                                        <div>
                                            <strong><?php echo htmlspecialchars($log['project_name']); ?></strong>
                                            <small><?php echo htmlspecialchars($log['property_code']); ?></small>
                                            <span class="badge <?php echo $is_affordable ? 'bg-success' : 'bg-gold text-dark'; ?> mt-2">
                                                <?php echo $is_affordable ? 'AFFORDABLE' : htmlspecialchars($log['property_type']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold"><?php echo htmlspecialchars($log['state']); ?></span>
                                </td>
                                <td class="fw-bold text-success">RM <?php echo number_format((float)$log['price'], 2); ?></td>
                                <td>
                                    <span class="d-block"><?php echo number_format((int)$log['built_up_sqft']); ?> sqft</span>
                                    <?php if ($is_affordable): ?>
                                        <small class="text-muted">Income limit RM <?php echo number_format((float)($log['income_limit_rm'] ?? 0), 0); ?></small>
                                    <?php else: ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($log['property_type']); ?> listing</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="<?php echo $is_sold_out ? 'text-danger' : 'text-dark'; ?> fw-bold">
                                        <?php echo $is_sold_out ? '0' : number_format((int)$log['total_units']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $is_sold_out ? 'bg-danger' : 'bg-primary'; ?> px-3 py-2">
                                        <?php echo htmlspecialchars($log['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="heat-pill">
                                        <i class="fas fa-heart me-1"></i><?php echo number_format((int)$log['total_interest']); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="../property_detail.php?id=<?php echo (int)$log['property_id']; ?>" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <h5 class="fw-bold mb-1">No wishlist activity yet.</h5>
                                <p class="text-muted mb-0">Customer wishlist actions will appear here immediately after they add properties.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.interest-timeline-page .tracking-wider { letter-spacing: 0.1em; }
.interest-timeline-page .text-gold { color: #FFC000 !important; }
.bg-gold { background-color: #FFC000 !important; }
.timeline-metric {
    background: #ffffff;
    color: #15191f;
    border: 1px solid rgba(20,24,31,0.08);
    box-shadow: 0 16px 36px rgba(0,0,0,0.12);
    padding: 1.1rem 1.25rem;
    border-radius: 8px;
    min-height: 108px;
}
.timeline-metric span {
    display: block;
    color: #6c757d;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 0.55rem;
}
.timeline-metric strong {
    display: block;
    font-size: clamp(1.35rem, 2vw, 2rem);
    line-height: 1.1;
}
.interest-grid-wrap {
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid rgba(20,24,31,0.08);
}
.interest-grid thead th {
    background: #11151b;
    color: #f8f5ed;
    border: 0;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    white-space: nowrap;
}
.interest-grid tbody td {
    border-color: rgba(20,24,31,0.08);
    vertical-align: middle;
}
.moment-cell strong,
.moment-cell small {
    display: block;
    white-space: nowrap;
}
.moment-cell small {
    color: #6c757d;
}
.property-cell {
    min-width: 300px;
}
.property-cell-inner {
    min-width: 300px;
    display: flex;
    align-items: center;
    gap: 0.85rem;
}
.property-cell-inner img {
    width: 78px;
    height: 58px;
    object-fit: cover;
    border-radius: 6px;
    flex: 0 0 auto;
}
.property-cell-inner strong,
.property-cell-inner small {
    display: block;
}
.property-cell-inner small {
    color: #6c757d;
    margin-top: 0.2rem;
}
.heat-pill {
    display: inline-flex;
    align-items: center;
    background: #fff4f4;
    color: #b4232f;
    border: 1px solid #ffd3d8;
    border-radius: 999px;
    padding: 0.32rem 0.72rem;
    font-weight: 700;
}
@media (max-width: 767.98px) {
    .property-cell,
    .property-cell-inner { min-width: 240px; }
}
</style>

<?php include '../includes/footer.php'; ?>
