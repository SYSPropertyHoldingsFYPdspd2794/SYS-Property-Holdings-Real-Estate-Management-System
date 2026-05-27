<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/wishlist.php
 * DESCRIPTION: Customer Wishlist Management interface.
 */

require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';
require_once '../includes/property_images.php';
protect_customer_page('CUSTOMER', $conn);

$account_id = $_SESSION['account_id'];

// Create table safely if it doesn't exist (Backup measure)
$conn->query("CREATE TABLE IF NOT EXISTS wishlists (
    wishlist_id INT AUTO_INCREMENT PRIMARY KEY, 
    customer_id INT NOT NULL, 
    property_id INT NOT NULL, 
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE, 
    FOREIGN KEY(property_id) REFERENCES properties(property_id) ON DELETE CASCADE
)");   

// HANDLE REMOVE ACTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    $wish_id = intval($_POST['wishlist_id']);
    $del = $conn->prepare("DELETE FROM wishlists WHERE wishlist_id = ? AND customer_id = ?");
    $del->bind_param("ii", $wish_id, $account_id);
    $del->execute();

    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'wishlist_id' => $wish_id
        ]);
        exit();
    }

    header("Location: wishlist.php"); 
    exit();
}

// FETCH WISHLIST JOINED WITH PROPERTIES
$stmt = $conn->prepare("SELECT w.wishlist_id, p.* FROM wishlists w JOIN properties p ON w.property_id = p.property_id WHERE w.customer_id = ? ORDER BY w.created_at DESC");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();

include_once '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex align-items-center mb-5 border-bottom pb-3">
        <i class="fas fa-heart text-danger fa-2x me-3"></i>
        <h2 class="fw-bold mb-0 text-white">My Saved Properties</h2>
    </div>

    <div class="row" id="wishlistGrid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                
                // Use the centralized helper function to handle paths and fallbacks
                $finalImg = property_catalog_image_path($row, $root_prefix ?? '', '../');
            ?>
                <div class="col-lg-4 col-md-6 mb-4 wishlist-card-wrap">
                    <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                        <div class="position-relative bg-light" style="height: 200px;">
                            <img src="<?php echo htmlspecialchars($finalImg); ?>" class="w-100 h-100" style="object-fit: cover; object-position: center;">
                            <span class="badge bg-danger position-absolute top-0 end-0 m-3 shadow z-3"><i class="fas fa-bookmark me-1"></i>Saved</span>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <span class="badge bg-primary mb-2 align-self-start"><?php echo htmlspecialchars($row['property_type']); ?></span>
                        <h5 class="fw-bold fs-5 text-warning" title="<?php echo htmlspecialchars($row['project_name']); ?>"><?php echo htmlspecialchars($row['project_name']); ?></h5>
                            <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt me-1 text-danger"></i><?php echo htmlspecialchars($row['state']); ?></p>
                            <h4 class="text-success fw-bold mt-auto mb-0">RM <?php echo number_format($row['price'], 2); ?></h4>
                        </div>
                        <div class="card-footer bg-white border-top border-light p-3 d-flex justify-content-between align-items-center">
                            <form method="POST" class="m-0 wishlist-remove-form">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="wishlist_id" value="<?php echo $row['wishlist_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger fw-bold rounded-pill px-3"><i class="fas fa-trash-alt me-1"></i> Remove</button>
                            </form>
                            <a href="property_detail.php?id=<?php echo $row['property_id']; ?>" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-4">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 mt-4" id="emptyWishlistState">
                <div class="p-5 bg-light rounded-4 shadow-sm border border-light d-inline-block w-100">
                    <i class="far fa-folder-open display-1 text-muted mb-4 opacity-50"></i>
                    <h3 class="text-dark fw-bold">Your Wishlist is Empty</h3>
                    <p class="text-muted lead mb-4">Start browsing our catalog and click the heart icon to save your dream properties here.</p>
                    <a href="properties.php" class="btn btn-primary btn-lg rounded-pill shadow-sm px-5 fw-bold">Browse Properties</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .wishlist-card-wrap {
        transition: opacity 0.25s ease, transform 0.25s ease;
    }
    .wishlist-card-wrap.is-removing {
        opacity: 0;
        transform: scale(0.97);
    }
    .wishlist-remove-form .btn.is-loading {
        opacity: 0.65;
        pointer-events: none;
    }
</style>

<script>
function renderEmptyWishlistState() {
    const grid = document.getElementById('wishlistGrid');
    if (!grid || grid.querySelector('.wishlist-card-wrap')) {
        return;
    }

    grid.innerHTML = `
        <div class="col-12 text-center py-5 mt-4" id="emptyWishlistState">
            <div class="p-5 bg-light rounded-4 shadow-sm border border-light d-inline-block w-100">
                <i class="far fa-folder-open display-1 text-muted mb-4 opacity-50"></i>
                <h3 class="text-dark fw-bold">Your Wishlist is Empty</h3>
                <p class="text-muted lead mb-4">Start browsing our catalog and click the heart icon to save your dream properties here.</p>
                <a href="properties.php" class="btn btn-primary btn-lg rounded-pill shadow-sm px-5 fw-bold">Browse Properties</a>
            </div>
        </div>
    `;
}

document.querySelectorAll('.wishlist-remove-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        const cardWrap = form.closest('.wishlist-card-wrap');
        const data = new FormData(form);

        if (button) {
            button.classList.add('is-loading');
            button.disabled = true;
        }

        fetch('wishlist.php', {
            method: 'POST',
            body: data,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Wishlist remove failed');
            }
            return response.json();
        })
        .then(function (result) {
            if (!result.success || !cardWrap) {
                return;
            }

            cardWrap.classList.add('is-removing');
            setTimeout(function () {
                cardWrap.remove();
                renderEmptyWishlistState();
            }, 250);
        })
        .catch(function () {
            HTMLFormElement.prototype.submit.call(form);
        })
        .finally(function () {
            if (button) {
                button.classList.remove('is-loading');
                button.disabled = false;
            }
        });
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>
