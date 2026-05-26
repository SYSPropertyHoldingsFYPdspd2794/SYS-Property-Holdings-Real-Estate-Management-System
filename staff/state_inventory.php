<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';
require_once '../includes/property_images.php';

protect_staff_page('STAFF', $conn);

$account_id = $_SESSION['account_id'];
$stmt = $conn->prepare("SELECT assigned_state FROM staff WHERE staff_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$staff_state = $stmt->get_result()->fetch_assoc()['assigned_state'] ?? '';
$stmt->close();

$page_title = "State Inventory";
include '../includes/header.php';

$properties_result = null;
$summary = [
    'total_properties' => 0,
    'total_units' => 0,
    'total_interest' => 0,
    'avg_price' => 0
];

if ($staff_state !== '') {
    $summary_stmt = $conn->prepare("
        SELECT 
            COUNT(p.property_id) AS total_properties,
            COALESCE(SUM(p.total_units), 0) AS total_units,
            COALESCE(SUM(wi.total_interest), 0) AS total_interest,
            COALESCE(AVG(p.price), 0) AS avg_price
        FROM properties p
        LEFT JOIN (
            SELECT property_id, COUNT(*) AS total_interest
            FROM wishlists
            GROUP BY property_id
        ) wi ON p.property_id = wi.property_id
        WHERE p.state = ? AND p.status IN ('ACTIVE', 'AVAILABLE', 'SOLD_OUT')
    ");
    $summary_stmt->bind_param("s", $staff_state);
    $summary_stmt->execute();
    $summary = $summary_stmt->get_result()->fetch_assoc() ?: $summary;
    $summary_stmt->close();

    $query = "
        SELECT 
            p.*,
            COALESCE(wi.total_interest, 0) AS total_interest,
            wi.last_interest_at
        FROM properties p
        LEFT JOIN (
            SELECT property_id, COUNT(*) AS total_interest, MAX(created_at) AS last_interest_at
            FROM wishlists
            GROUP BY property_id
        ) wi ON p.property_id = wi.property_id
        WHERE p.state = ? AND p.status IN ('ACTIVE', 'AVAILABLE', 'SOLD_OUT')
        ORDER BY total_interest DESC, p.is_affordable DESC, p.property_id DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $staff_state);
    $stmt->execute();
    $properties_result = $stmt->get_result();
}
?>

<div class="container-fluid px-4 px-lg-5 my-5 state-inventory-page">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <p class="text-uppercase tracking-wider text-gold mb-2 small">Staff Regional View</p>
            <h2 class="fw-bold m-0 text-white">State Inventory</h2>
            <p class="text-light opacity-75 mt-2 mb-0">
                Showing property stock and wishlist demand for
                <strong><?php echo htmlspecialchars($staff_state !== '' ? $staff_state : 'Unassigned Region'); ?></strong>.
            </p>
        </div>
        <a href="../properties.php?filter_state=<?php echo urlencode($staff_state); ?>" class="btn btn-primary fw-bold px-4 <?php echo $staff_state === '' ? 'disabled' : ''; ?>">
            <i class="fas fa-up-right-from-square me-2"></i>Catalog View
        </a>
    </div>

    <?php if ($staff_state === ''): ?>
        <div class="alert alert-warning border-0 shadow-sm p-4">
            <h5 class="fw-bold mb-1">No assigned state found.</h5>
            <p class="mb-0">Please ask an admin to assign a state before using State Inventory.</p>
        </div>
    <?php else: ?>
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <div class="inventory-metric">
                    <span>Total Properties</span>
                    <strong><?php echo number_format((int)$summary['total_properties']); ?></strong>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="inventory-metric">
                    <span>Total Units</span>
                    <strong><?php echo number_format((int)$summary['total_units']); ?></strong>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="inventory-metric">
                    <span>Wishlist Interest</span>
                    <strong><?php echo number_format((int)$summary['total_interest']); ?></strong>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="inventory-metric">
                    <span>Average Price</span>
                    <strong>RM <?php echo number_format((float)$summary['avg_price'], 0); ?></strong>
                </div>
            </div>
        </div>

        <div class="inventory-grid-wrap bg-white text-dark shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle inventory-grid mb-0">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Location</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Specs</th>
                            <th>Units</th>
                            <th>Interest</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($properties_result && $properties_result->num_rows > 0): ?>
                            <?php while ($prop = $properties_result->fetch_assoc()): ?>
                                <?php
                                $is_affordable = (int)$prop['is_affordable'] === 1;
                                $is_sold_out = trim((string)$prop['status']) === 'SOLD_OUT';
                                $final_img = property_catalog_image_path($prop, '../', '../');
                                ?>
                                <tr>
                                    <td class="property-cell">
                                        <div class="property-cell-inner">
                                            <img src="<?php echo htmlspecialchars($final_img); ?>" alt="<?php echo htmlspecialchars($prop['project_name']); ?>">
                                            <div>
                                                <strong><?php echo htmlspecialchars($prop['project_name']); ?></strong>
                                                <small><?php echo htmlspecialchars($prop['property_code']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold"><?php echo htmlspecialchars($prop['state']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $is_affordable ? 'bg-success' : 'bg-gold text-dark'; ?> px-3 py-2">
                                            <?php echo $is_affordable ? 'AFFORDABLE' : htmlspecialchars($prop['property_type']); ?>
                                        </span>
                                        <?php if ($is_affordable): ?>
                                            <small class="d-block text-muted mt-2">Income limit RM <?php echo number_format((float)($prop['income_limit_rm'] ?? 0), 0); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-success">RM <?php echo number_format((float)$prop['price'], 2); ?></td>
                                    <td>
                                        <span class="d-block"><?php echo number_format((int)$prop['built_up_sqft']); ?> sqft</span>
                                        <small class="text-muted">Same listing data as catalog/detail</small>
                                    </td>
                                    <td>
                                        <span class="<?php echo $is_sold_out ? 'text-danger' : 'text-dark'; ?> fw-bold">
                                            <?php echo $is_sold_out ? '0' : number_format((int)$prop['total_units']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="interest-pill">
                                            <i class="fas fa-heart me-1"></i><?php echo number_format((int)$prop['total_interest']); ?>
                                        </span>
                                        <small class="d-block text-muted mt-2">
                                            <?php echo $prop['last_interest_at'] ? date('d M Y, g:i A', strtotime($prop['last_interest_at'])) : 'No activity'; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $is_sold_out ? 'bg-danger' : 'bg-primary'; ?> px-3 py-2">
                                            <?php echo htmlspecialchars($prop['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="../property_detail.php?id=<?php echo (int)$prop['property_id']; ?>" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <h5 class="fw-bold mb-1">No properties in this state.</h5>
                                    <p class="text-muted mb-0">There are currently no active, available, or sold out listings for <?php echo htmlspecialchars($staff_state); ?>.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.state-inventory-page .tracking-wider { letter-spacing: 0.1em; }
.state-inventory-page .text-gold { color: #c5a059 !important; }
.bg-gold { background-color: #c5a059 !important; }
.inventory-metric {
    background: #ffffff;
    color: #15191f;
    border: 1px solid rgba(20,24,31,0.08);
    box-shadow: 0 16px 36px rgba(0,0,0,0.12);
    padding: 1.1rem 1.25rem;
    border-radius: 8px;
    min-height: 108px;
}
.inventory-metric span {
    display: block;
    color: #6c757d;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 0.55rem;
}
.inventory-metric strong {
    display: block;
    font-size: clamp(1.35rem, 2vw, 2rem);
    line-height: 1.1;
}
.inventory-grid-wrap {
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid rgba(20,24,31,0.08);
}
.inventory-grid thead th {
    background: #11151b;
    color: #f8f5ed;
    border: 0;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    white-space: nowrap;
}
.inventory-grid tbody td {
    border-color: rgba(20,24,31,0.08);
    vertical-align: middle;
}
.property-cell {
    min-width: 280px;
}
.property-cell-inner {
    min-width: 280px;
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
.interest-pill {
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
    .property-cell-inner { min-width: 230px; }
}
</style>

<?php include '../includes/footer.php'; ?>
