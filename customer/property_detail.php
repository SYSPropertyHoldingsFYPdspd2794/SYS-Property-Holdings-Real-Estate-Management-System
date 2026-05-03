<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role']!== 'CUSTOMER') {
    header("Location:../login.php");
    exit();
}
include '../includes/db_connect.php';
include '../includes/header.php';

$property_id = isset($_GET['id'])? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM properties WHERE property_id =?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    echo "<div class='container my-5'><h3 class='text-danger'>Property not found.</h3></div>";
    include '../includes/footer.php';
    exit();
}

$rate_stmt = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'BASE_INTEREST_RATE'");
$rate = $rate_stmt->fetch_assoc()['setting_value'];
?>
<div class="container my-5">
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-body p-5">
                    <span class="badge bg-primary mb-3 fs-6 px-3 py-2"><?php echo htmlspecialchars($property['property_type']);?></span>
                    <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($property['project_name']);?></h1>
                    <p class="fs-4 text-muted"><i class="fas fa-map-marker-alt me-2 text-danger"></i><?php echo htmlspecialchars($property['state']);?></p>
                    <hr class="my-4">
                    <div class="row text-center mb-5">
                        <div class="col p-3 border-end">
                            <p class="mb-1 text-muted text-uppercase fw-bold">Available Units</p>
                            <h2 class="fw-bold"><?php echo htmlspecialchars($property['available_units']);?></h2>
                        </div>
                        <div class="col p-3">
                            <p class="mb-1 text-muted text-uppercase fw-bold">Selling Price</p>
                            <h2 class="fw-bold text-success">RM <?php echo number_format($property['price'], 2);?></h2>
                        </div>
                    </div>
                    <div class="d-grid mt-auto">
                        <?php if ($property['property_type'] === 'AFFORDABLE'):?>
                            <a href="apply_affordable.php?id=<?php echo $property['property_id'];?>" class="btn btn-success btn-lg fw-bold py-3">Apply for Gov Housing</a>
                        <?php else:?>
                            <a href="book_appointment.php?id=<?php echo $property['property_id'];?>" class="btn btn-dark btn-lg fw-bold py-3">Book Showroom Viewing</a>
                        <?php endif;?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card shadow border-0 h-100 bg-light">
                <div class="card-body p-5">
                    <h3 class="fw-bold mb-4"><i class="fas fa-calculator me-2 text-primary"></i>Dynamic Loan Calculator</h3>
                    <input type="hidden" id="propertyPrice" value="<?php echo $property['price'];?>">
                    <input type="hidden" id="interestRate" value="<?php echo $rate;?>">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Base Interest Rate (%)</label>
                        <input type="text" class="form-control form-control-lg bg-white" value="<?php echo htmlspecialchars($rate);?>" readonly>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Downpayment (%)</label>
                        <input type="number" id="downpayment" class="form-control form-control-lg" value="10" min="0" max="100">
                    </div>
                    <div class="mb-5">
                        <label class="form-label fw-bold">Loan Tenure (Years)</label>
                        <input type="number" id="tenure" class="form-control form-control-lg" value="35" min="5" max="35">
                    </div>
                    <div class="p-4 bg-white border border-primary rounded text-center shadow-sm">
                        <p class="mb-2 text-muted fw-bold text-uppercase">Estimated Monthly Installment</p>
                        <h1 class="text-primary fw-bold m-0" id="monthlyResult">RM 0.00</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function calculateLoan() {
    const price = parseFloat(document.getElementById('propertyPrice').value);
    const ratePercentage = parseFloat(document.getElementById('interestRate').value);
    const monthlyRate = (ratePercentage / 100) / 12;
    const downpaymentPerc = parseFloat(document.getElementById('downpayment').value) / 100;
    const tenureMonths = parseFloat(document.getElementById('tenure').value) * 12;
    
    const loanAmount = price - (price * downpaymentPerc);
    
    if (loanAmount <= 0 |

| tenureMonths <= 0 |
| isNaN(loanAmount) |
| isNaN(tenureMonths)) {
        document.getElementById('monthlyResult').innerText = "RM 0.00";
        return;
    }
    
    let monthlyInstallment = 0;
    if (monthlyRate > 0) {
        monthlyInstallment = (loanAmount * monthlyRate * Math.pow(1 + monthlyRate, tenureMonths)) / (Math.pow(1 + monthlyRate, tenureMonths) - 1);
    } else {
        monthlyInstallment = loanAmount / tenureMonths;
    }
    
    document.getElementById('monthlyResult').innerText = "RM " + monthlyInstallment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
document.getElementById('downpayment').addEventListener('input', calculateLoan);
document.getElementById('tenure').addEventListener('input', calculateLoan);
window.addEventListener('load', calculateLoan);
</script>
<?php include '../includes/footer.php';?>

