<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

// Only allow STAFF
protect_admin_page('STAFF', $conn);

// Get staff's assigned state
$staff_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT state FROM staff WHERE staff_id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$staff_state = $stmt->get_result()->fetch_assoc()['state'] ?? 'Unknown';
$stmt->close();

$page_title = "State Inventory & Interest - " . htmlspecialchars($staff_state);
include '../includes/header.php';

// Fetch properties in staff's state with wishlist interest counts
$query = "
    SELECT p.*, COUNT(w.wishlist_id) as total_interest
    FROM properties p
    LEFT JOIN wishlists w ON p.property_id = w.property_id
    WHERE p.state = ? AND p.status = 'ACTIVE'
    GROUP BY p.property_id
    ORDER BY total_interest DESC, p.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $staff_state);
$stmt->execute();
$properties_result = $stmt->get_result();
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h2 class="fw-bold m-0"><i class="bi bi-geo-alt-fill me-2 text-primary"></i>Local Market Interest</h2>
            <p class="text-muted mt-1 mb-0">Showing properties and customer interest in your assigned state: <strong><?= htmlspecialchars($staff_state) ?></strong></p>
        </div>
    </div>

    <?php if ($properties_result->num_rows > 0): ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php while ($prop = $properties_result->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 hover-card">
                        <?php 
                        $img_path = '../uploads/properties/' . htmlspecialchars($prop['cover_image']);
                        if (empty($prop['cover_image']) || !file_exists($img_path)) {
                            $img_path = '../SYS Property Catalog/placeholder.jpg';
                        }
                        ?>
                        <div style="height: 200px; overflow: hidden; position: relative;">
                            <img src="<?= $img_path ?>" class="card-img-top image-zoom" alt="<?= htmlspecialchars($prop['project_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <!-- Hot badge if interest > 5 -->
                            <?php if ($prop['total_interest'] >= 5): ?>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-3 px-3 py-2 shadow-sm rounded-pill"><i class="bi bi-fire me-1"></i> HOT</span>
                            <?php endif; ?>
                            <!-- Interest counter -->
                            <div class="position-absolute bottom-0 start-0 m-3 px-3 py-1 bg-dark text-white rounded-pill shadow-sm" style="opacity: 0.9;">
                                <i class="bi bi-heart-fill text-danger me-1"></i> <?= $prop['total_interest'] ?> Interested
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-secondary mb-2"><?= htmlspecialchars($prop['property_type']) ?></span>
                                <h5 class="fw-bold text-success m-0">RM <?= number_format($prop['price'], 2) ?></h5>
                            </div>
                            <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($prop['project_name']) ?></h5>
                            <p class="text-muted small mb-3"><i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($prop['city'] . ', ' . $prop['state']) ?></p>
                            
                            <hr class="text-muted">
                            
                            <div class="d-flex justify-content-between text-muted small">
                                <span><i class="bi bi-rulers me-1"></i> <?= htmlspecialchars($prop['built_up_sqft']) ?> sqft</span>
                                <span><i class="bi bi-door-open me-1"></i> <?= htmlspecialchars($prop['bedrooms']) ?> Beds</span>
                                <span><i class="bi bi-droplet me-1"></i> <?= htmlspecialchars($prop['bathrooms']) ?> Baths</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-light text-center py-5 shadow-sm border">
            <i class="bi bi-house-x display-1 text-muted mb-3 d-block"></i>
            <h4>No Active Properties in Your Region</h4>
            <p class="text-muted">There are currently no active listings available in <?= htmlspecialchars($staff_state) ?>.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>