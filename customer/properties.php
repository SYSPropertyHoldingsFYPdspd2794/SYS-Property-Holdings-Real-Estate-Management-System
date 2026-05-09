<?php
include '../includes/db_connect.php';
include '../includes/auth_check.php';

protect_customer_page('CUSTOMER', $conn);

$type = $_GET['type'] ?? '';
$allowed_types = ['AFFORDABLE', 'TERRACE', 'BUNGALOW', 'COMMERCIAL', 'APARTMENT'];

if ($type !== '' && in_array($type, $allowed_types, true)) {
    $stmt = $conn->prepare("SELECT * FROM properties WHERE status = 'ACTIVE' AND property_type = ? ORDER BY property_id DESC");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $properties = $stmt->get_result();
} else {
    $properties = $conn->query("SELECT * FROM properties WHERE status = 'ACTIVE' ORDER BY property_id DESC");
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-building text-primary me-2"></i>Property Catalog</h2>
            <p class="text-muted mb-0">Browse active SYS Property projects and continue to details, booking, or affordable housing applications.</p>
        </div>
        <form method="GET" class="d-flex gap-2">
            <select name="type" class="form-select" onchange="this.form.submit()">
                <option value="">All Types</option>
                <?php foreach ($allowed_types as $property_type): ?>
                    <option value="<?php echo htmlspecialchars($property_type); ?>" <?php echo $type === $property_type ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(ucwords(strtolower(str_replace('_', ' ', $property_type)))); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($type !== ''): ?>
                <a href="properties.php" class="btn btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="row">
        <?php if ($properties->num_rows > 0): ?>
            <?php while ($row = $properties->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($row['property_type']); ?></span>
                                <span class="badge bg-success"><?php echo (int)$row['available_units']; ?> unit(s)</span>
                            </div>
                            <h4 class="fw-bold mb-2"><?php echo htmlspecialchars($row['project_name']); ?></h4>
                            <p class="text-muted mb-2"><i class="fas fa-map-marker-alt text-danger me-2"></i><?php echo htmlspecialchars($row['state']); ?></p>
                            <p class="text-muted mb-3"><i class="fas fa-ruler-combined text-primary me-2"></i><?php echo number_format((int)$row['built_up_sqft']); ?> sqft</p>
                            <h4 class="text-success fw-bold mb-4">RM <?php echo number_format((float)$row['price'], 2); ?></h4>
                            <div class="mt-auto d-flex gap-2">
                                <a href="property_detail.php?id=<?php echo (int)$row['property_id']; ?>" class="btn btn-dark fw-bold flex-fill">View Details</a>
                                <form method="POST" action="wishlist.php" class="m-0">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="property_id" value="<?php echo (int)$row['property_id']; ?>">
                                    <button type="submit" class="btn btn-outline-danger" title="Add to wishlist">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="far fa-folder-open display-1 text-muted mb-3"></i>
                <h4 class="text-muted">No active properties found.</h4>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
