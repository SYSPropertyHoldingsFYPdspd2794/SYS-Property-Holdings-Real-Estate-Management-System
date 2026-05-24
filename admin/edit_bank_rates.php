<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: admin/edit_bank_rates.php
 * DESCRIPTION: Allows ADMIN to edit the bank interest rates.
 */

require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn);

$success_msg = '';
$error_msg = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_rates'])) {
    if (isset($_POST['rates']) && is_array($_POST['rates'])) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE banks SET interest_rate = ? WHERE bank_id = ?");
            foreach ($_POST['rates'] as $bank_id => $rate) {
                // Ensure rate is a valid decimal
                $rate_val = floatval($rate);
                $bank_id_val = intval($bank_id);
                $stmt->bind_param("di", $rate_val, $bank_id_val);
                $stmt->execute();
            }
            $conn->commit();
            $success_msg = 'Bank rates updated successfully.';
        } catch (Exception $e) {
            $conn->rollback();
            $error_msg = 'Error updating bank rates: ' . $e->getMessage();
        }
    }
}

// Fetch current rates
$sql = "SELECT bank_id, bank_name, interest_rate FROM banks ORDER BY interest_rate ASC";
$result = $conn->query($sql);
$banks = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $banks[] = $row;
    }
}

$page_title = "Edit Bank Rates";
include '../includes/header.php';
?>

<div class="container my-5 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <h2 class="fw-bold text-white m-0"><i class="fas fa-edit me-2 text-warning"></i>Edit Bank Loan Rates</h2>
        <a href="../bank_rates.php" class="btn btn-outline-light"><i class="fas fa-arrow-left me-2"></i>Back to Rates</a>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card filter-glass border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="edit_bank_rates.php" method="POST">
                <div class="table-responsive">
                    <table class="table text-white table-hover align-middle mb-0" style="background: transparent;">
                        <thead>
                            <tr>
                                <th scope="col" class="text-uppercase text-muted border-bottom border-secondary" style="width: 50%;">Bank Name</th>
                                <th scope="col" class="text-uppercase text-muted border-bottom border-secondary" style="width: 50%;">Interest Rate (p.a)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($banks as $bank): ?>
                                <tr>
                                    <td class="fw-bold border-bottom border-secondary" style="background: transparent;">
                                        <?php echo htmlspecialchars($bank['bank_name']); ?>
                                    </td>
                                    <td class="border-bottom border-secondary" style="background: transparent;">
                                        <div class="input-group" style="max-width: 200px;">
                                            <input type="number" step="0.01" min="0" max="100" class="form-control" name="rates[<?php echo $bank['bank_id']; ?>]" value="<?php echo htmlspecialchars($bank['interest_rate']); ?>" required>
                                            <span class="input-group-text"><i class="fas fa-percent"></i></span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-4 pt-3 border-top border-secondary">
                    <button type="submit" name="update_rates" class="btn btn-warning px-4 fw-bold shadow"><i class="fas fa-save me-2"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>