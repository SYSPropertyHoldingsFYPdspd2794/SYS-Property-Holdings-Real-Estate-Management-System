<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: bank_rates.php (ROOT DIRECTORY)
 * DESCRIPTION: Displays current interest rates using local assets mapping from 8 Major Banks.
 */

include_once 'includes/header.php';

$sql = "SELECT bank_name, interest_rate FROM banks ORDER BY interest_rate ASC";
$result = $conn->query($sql);
?>

<div class="container my-5 py-4 position-relative">
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>
        <div class="position-absolute top-0 end-0 mt-3 me-3 z-3">
            <a href="admin/edit_bank_rates.php" class="btn btn-warning shadow"><i class="fas fa-edit me-2"></i>Edit Rates</a>
        </div>
    <?php endif; ?>

    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-white mb-3"><i class="fas fa-chart-line text-gold me-3"></i>Current Bank Loan Rates</h1>
        <p class="lead text-light w-75 mx-auto">Compare the latest Base Lending Rates (BLR) from Malaysia's top banks. Use these rates in our property calculator to formulate a precise offline financial plan.</p>
        <hr class="w-25 mx-auto mt-4 mb-5 border-primary border-2 opacity-75">
    </div>

    <div class="row g-4">
        <?php
        if ($result && $result->num_rows > 0) {
            $rank = 1;
            while ($row = $result->fetch_assoc()) {
                $bankName = htmlspecialchars($row['bank_name']);
                $rate = number_format($row['interest_rate'], 2);
                
                $bankNameRaw = strtolower(trim($row['bank_name']));
                $logoUrl = "";
                $websiteUrl = "#";

                // LOCAL BANK LOGO MAPPING (Based on user provided directory structure)
                $bankPath = $root_prefix . "SYS Property Catalog/Bank/";
                
                if (strpos($bankNameRaw, 'maybank') !== false) {
                    $logoUrl = $bankPath . "MAYBANK.png";
                    $websiteUrl = "https://www.maybank2u.com.my";
                } elseif (strpos($bankNameRaw, 'cimb') !== false) {
                    $logoUrl = $bankPath . "CIMB.jpg";
                    $websiteUrl = "https://www.cimb.com.my";
                } elseif (strpos($bankNameRaw, 'public') !== false) {
                    $logoUrl = $bankPath . "PUBLIC_BANK.jpg"; // specific format handled
                    $websiteUrl = "https://www.pbebank.com";
                } elseif (strpos($bankNameRaw, 'rhb') !== false) {
                    $logoUrl = $bankPath . "RHB.png";
                    $websiteUrl = "https://www.rhbgroup.com";
                } elseif (strpos($bankNameRaw, 'hong leong') !== false) {
                    $logoUrl = $bankPath . "HONG_LEONG.png";
                    $websiteUrl = "https://www.hlb.com.my";
                } elseif (strpos($bankNameRaw, 'ambank') !== false) {
                    $logoUrl = $bankPath . "AMBANK.png";
                    $websiteUrl = "https://www.ambank.com.my";
                } elseif (strpos($bankNameRaw, 'uob') !== false) {
                    $logoUrl = $bankPath . "UOB.png";
                    $websiteUrl = "https://www.uob.com.my";
                } elseif (strpos($bankNameRaw, 'affin') !== false) {
                    $logoUrl = $bankPath . "AFFIN_BANK.jpg";
                    $websiteUrl = "https://www.affinalways.com";
                } else {
                    $logoUrl = $root_prefix . "SYS Property Catalog/placeholder.jpg";
                }

                $badgeClass = ($rank <= 3) ? 'bg-danger' : 'bg-secondary';
                $badgeText = ($rank === 1) ? 'Lowest Rate' : "Top " . $rank;
                ?>
                
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow hover-bank-card overflow-hidden">
                        <div class="bg-white p-4 text-center position-relative border-bottom d-flex align-items-center justify-content-center" style="height: 120px;">
                            <?php if($rank <= 3): ?>
                                <span class="position-absolute top-0 end-0 badge <?php echo $badgeClass; ?> m-2 shadow-sm z-3"><?php echo $badgeText; ?></span>
                            <?php endif; ?>
                            <a href="<?php echo $websiteUrl; ?>" target="_blank" class="stretched-link text-decoration-none w-100">
                                <img src="<?php echo $logoUrl; ?>" style="max-height: 60px; object-fit: contain; width: 100%;" alt="<?php echo $bankName; ?>">
                            </a>
                        </div>
                        <div class="card-body p-4 text-center bg-light">
                            <h6 class="fw-bold text-dark mb-3"><?php echo $bankName; ?></h6>
                            <p class="text-uppercase text-muted fw-bold small mb-1" style="letter-spacing: 1px;">Interest Rate (p.a)</p>
                            <h2 class="display-5 fw-bold text-primary mb-0"><?php echo $rate; ?>%</h2>
                        </div>
                    </div>
                </div>
                <?php
                $rank++;
            }
        } else {
            echo '<div class="col-12 text-center"><div class="alert alert-warning">No bank rates available.</div></div>';
        }
        ?>
    </div>
</div>

<style>
.hover-bank-card { transition: transform 0.3s ease, box-shadow 0.3s ease; border-radius: 15px; }
.hover-bank-card:hover { transform: translateY(-8px); box-shadow: 0 1.5rem 3rem rgba(0,0,0,0.15)!important; }
</style>

<?php include_once 'includes/footer.php'; ?>
