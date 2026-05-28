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
            $stmt = $conn->prepare("UPDATE banks SET interest_rate = ?, effective_quarter = ?, effective_year = ? WHERE bank_id = ?");
            foreach ($_POST['rates'] as $bank_id => $data) {
                // Ensure values are properly casted
                $rate_val = floatval($data['rate']);
                $quarter_val = trim($data['quarter']);
                $year_val = intval($data['year']);
                $bank_id_val = intval($bank_id);
                $stmt->bind_param("dsii", $rate_val, $quarter_val, $year_val, $bank_id_val);
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
$sql = "SELECT bank_id, bank_name, interest_rate, effective_quarter, effective_year FROM banks ORDER BY CASE WHEN bank_name = 'BASE INTEREST RATE' THEN 0 ELSE 1 END, interest_rate ASC";
$result = $conn->query($sql);
$banks = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Fallbacks if null
        $row['effective_quarter'] = $row['effective_quarter'] ?? 'Q1';
        $row['effective_year'] = $row['effective_year'] ?? date('Y');
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
                                <th scope="col" class="text-uppercase text-muted border-bottom border-secondary" style="width: 40%;">Bank Name</th>
                                <th scope="col" class="text-uppercase text-muted border-bottom border-secondary" style="width: 30%;">Interest Rate (p.a)</th>
                                <th scope="col" class="text-uppercase text-muted border-bottom border-secondary" style="width: 30%;">Effective Period</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($banks as $bank): 
                                $is_base = ($bank['bank_name'] === 'BASE INTEREST RATE');
                                $row_bg = $is_base ? 'background: rgba(33, 37, 41, 0.95);' : 'background: transparent;';
                                $name_class = $is_base ? 'text-warning' : 'text-dark';
                                $name_style = $is_base ? '' : 'color: #000000 !important;';
                            ?>
                                <tr style="<?php echo $row_bg; ?>">
                                    <td class="fw-bold border-bottom border-secondary text-white" style="<?php echo $row_bg; ?>">
                                        <span class="<?php echo $name_class; ?>" style="<?php echo $name_style; ?>"><?php echo htmlspecialchars($bank['bank_name']); ?></span>
                                    </td>
                                    <td class="border-bottom border-secondary" style="<?php echo $row_bg; ?>">
                                        <div class="input-group" style="max-width: 180px;">
                                            <input type="number" step="0.01" min="0" max="100" class="form-control <?php echo $is_base ? 'bg-dark text-white border-warning' : ''; ?>" name="rates[<?php echo $bank['bank_id']; ?>][rate]" value="<?php echo htmlspecialchars($bank['interest_rate']); ?>" required>
                                            <span class="input-group-text <?php echo $is_base ? 'bg-warning text-dark border-warning' : ''; ?>"><i class="fas fa-percent"></i></span>
                                        </div>
                                    </td>
                                    <td class="border-bottom border-secondary" style="<?php echo $row_bg; ?>">
                                        <div class="d-flex gap-2" style="max-width: 250px;">
                                            <select class="form-select form-select-sm <?php echo $is_base ? 'bg-dark text-white border-secondary' : ''; ?>" name="rates[<?php echo $bank['bank_id']; ?>][quarter]">
                                                <?php foreach(['Q1','Q2','Q3','Q4'] as $q): ?>
                                                    <option value="<?php echo $q; ?>" <?php echo ($bank['effective_quarter'] === $q) ? 'selected' : ''; ?>><?php echo $q; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select class="form-select form-select-sm <?php echo $is_base ? 'bg-dark text-white border-secondary' : ''; ?>" name="rates[<?php echo $bank['bank_id']; ?>][year]">
                                                <?php 
                                                    $current_year = date('Y');
                                                    for($y = $current_year - 2; $y <= $current_year + 2; $y++): 
                                                ?>
                                                    <option value="<?php echo $y; ?>" <?php echo ($bank['effective_year'] == $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                                <?php endfor; ?>
                                            </select>
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