<?php
session_start();
if (!isset($_SESSION['role']) |

| $_SESSION['role']!== 'CUSTOMER') {
    header("Location:../login.php");
    exit();
}
include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];

$conn->query("CREATE TABLE IF NOT EXISTS wishlists (
    wishlist_id INT AUTO_INCREMENT PRIMARY KEY, 
    customer_id INT, 
    property_id INT, 
    FOREIGN KEY(customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE, 
    FOREIGN KEY(property_id) REFERENCES properties(property_id) ON DELETE CASCADE
)");

if ($_SERVER === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $prop_id = intval($_POST['property_id']);
        $check = $conn->prepare("SELECT wishlist_id FROM wishlists WHERE customer_id =? AND property_id =?");
        $check->bind_param("ii", $account_id, $prop_id);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            $ins = $conn->prepare("INSERT INTO wishlists (customer_id, property_id) VALUES (?,?)");
            $ins->bind_param("ii", $account_id, $prop_id);
            $ins->execute();
        }
    } elseif ($_POST['action'] === 'remove') {
        $wish_id = intval($_POST['wishlist_id']);
        $del = $conn->prepare("DELETE FROM wishlists WHERE wishlist_id =? AND customer_id =?");
        $del->bind_param("ii", $wish_id, $account_id);
        $del->execute();
    }
    header("Location: wishlist.php");
    exit();
}

include '../includes/header.php';
$stmt = $conn->prepare("SELECT w.wishlist_id, p.* FROM wishlists w JOIN properties p ON w.property_id = p.property_id WHERE w.customer_id =?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<div class="container my-5">
    <h2 class="fw-bold mb-5"><i class="fas fa-heart text-danger me-2"></i>My Wishlist</h2>
    <div class="row">
        <?php if ($result->num_rows > 0):?>
            <?php while ($row = $result->fetch_assoc()):?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <span class="badge bg-primary mb-3"><?php echo htmlspecialchars($row['property_type']);?></span>
                            <h5 class="fw-bold fs-4"><?php echo htmlspecialchars($row['project_name']);?></h5>
                            <p class="text-muted"><i class="fas fa-map-marker-alt me-2 text-danger"></i><?php echo htmlspecialchars($row['state']);?></p>
                            <h4 class="text-success fw-bold mt-3">RM <?php echo number_format($row['price'], 2);?></h4>
                        </div>
                        <div class="card-footer bg-white border-0 p-4 d-flex justify-content-between align-items-center">
                            <form method="POST" class="m-0">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="wishlist_id" value="<?php echo $row['wishlist_id'];?>">
                                <button type="submit" class="btn btn-outline-danger fw-bold"><i class="fas fa-trash-alt me-1"></i> Remove</button>
                            </form>
                            <a href="property_detail.php?id=<?php echo $row['property_id'];?>" class="btn btn-dark fw-bold">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile;?>
        <?php else:?>
            <div class="col-12 text-center py-5">
                <i class="far fa-folder-open display-1 text-muted mb-3"></i>
                <h4 class="text-muted">Your wishlist is currently empty.</h4>
                <a href="properties.php" class="btn btn-primary mt-3 px-4 fw-bold">Browse Properties</a>
            </div>
        <?php endif;?>
    </div>
</div>
<?php include '../includes/footer.php';?>

