<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

// Only allow ADMIN
protect_admin_page('ADMIN', $conn);

$page_title = "Global Interest Timeline";
include '../includes/header.php';

// Fetch timeline data: recent wishlist additions globally
$query = "
    SELECT 
        w.created_at, 
        c.full_name as customer_name, 
        c.email,
        p.project_name, 
        p.state,
        p.price,
        p.property_type
    FROM wishlists w
    JOIN customers c ON w.customer_id = c.customer_id
    JOIN properties p ON w.property_id = p.property_id
    ORDER BY w.created_at DESC
    LIMIT 50
";
$result = $conn->query($query);
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h2 class="fw-bold m-0"><i class="bi bi-clock-history me-2 text-primary"></i>Global Interest Timeline</h2>
            <p class="text-muted mt-1 mb-0">Live feed of properties being added to customer wishlists across all regions.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    
                    <?php if ($result->num_rows > 0): ?>
                        <div class="timeline position-relative" style="border-left: 2px solid #dee2e6; padding-left: 20px; margin-left: 10px;">
                            <?php 
                            $last_date = '';
                            while ($log = $result->fetch_assoc()): 
                                $current_date = date('F j, Y', strtotime($log['created_at']));
                                $time = date('g:i A', strtotime($log['created_at']));
                                
                                // Group by date
                                if ($current_date !== $last_date):
                                    $last_date = $current_date;
                            ?>
                                <h5 class="fw-bold mt-4 mb-3 text-muted position-relative" style="left: -32px;">
                                    <span class="bg-white pe-3"><i class="bi bi-calendar-event me-2"></i><?= $current_date ?></span>
                                </h5>
                            <?php endif; ?>
                            
                            <div class="timeline-item mb-4 position-relative">
                                <i class="bi bi-heart-fill text-danger position-absolute bg-white" style="left: -30px; top: 0px; font-size: 1.2rem;"></i>
                                <div class="card border-0 bg-light">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-dark"><?= htmlspecialchars($time) ?></span>
                                            <span class="text-muted small"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($log['state']) ?></span>
                                        </div>
                                        <p class="m-0 fs-6">
                                            <strong><?= htmlspecialchars($log['customer_name']) ?></strong> 
                                            (<a href="mailto:<?= htmlspecialchars($log['email']) ?>" class="text-decoration-none text-info"><?= htmlspecialchars($log['email']) ?></a>) 
                                            showed interest in 
                                            <span class="text-primary fw-bold"><?= htmlspecialchars($log['project_name']) ?></span> 
                                            <span class="badge bg-secondary ms-1"><?= htmlspecialchars($log['property_type']) ?></span>
                                        </p>
                                        <p class="text-success fw-bold small m-0 mt-1">RM <?= number_format($log['price'], 2) ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted my-5">
                            <i class="bi bi-inbox display-4 mb-3 d-block"></i>
                            <h5>No recent activity found.</h5>
                            <p>Customer wishlist additions will appear here in real-time.</p>
                        </div>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>