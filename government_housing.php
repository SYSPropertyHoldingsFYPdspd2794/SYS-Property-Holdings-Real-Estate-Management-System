<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: government_housing.php (ROOT)
 * DESCRIPTION: Complete Affordable Housing Hub combining KPKT standards, Legal Restrictions, and O2O Flow.
 */
include_once 'includes/header.php';
?>

<div class="container my-5 py-4">
    <div class="text-center mb-5">
        <div class="section-kicker mb-2">KPKT Authorized Partner</div>
        <img src="SYS Property Catalog/SYS_Property_Holdings_Icon.jpeg" alt="SYS Property Holdings" style="max-height: 80px; margin-bottom: 20px; border-radius: 8px;">
        <h1 class="display-4 fw-bold text-white mb-3">Affordable Housing Initiative</h1>
        <p class="lead text-light mx-auto" style="max-width: 780px;">SYS Property partners with state governments (RMMJ & RSKU) to deliver quality homes. We ensure a transparent, secure, and regulated pathway to homeownership for eligible citizens.</p>
    </div>

    <div class="row align-items-center mb-5 bg-white p-4 p-lg-5 shadow-sm rounded-4 border">
        <div class="col-lg-5 mb-4 mb-lg-0 text-center">
            <img src="SYS Property Catalog/Floor_Plan.webp" class="img-fluid rounded shadow" alt="Standard Floor Plan" style="border: 5px solid #f8f9fa;">
            <p class="text-muted small mt-3 fw-bold"><i class="fas fa-search-plus me-1"></i> Standard 3-Bedroom Layout Specification</p>
        </div>
        <div class="col-lg-7 px-lg-5">
            <h3 class="fw-bold mb-4" style="color: black;"><i class="fas fa-clipboard-list text-primary me-2"></i> Application Requirements</h3>
            <p class="text-muted mb-4">To ensure fair distribution, all applications are subject to strict verification based on Ministry guidelines. We only collect basic data for the initial O2O pre-check.</p>
            
            <ul class="list-group list-group-flush fs-5">
                <li class="list-group-item bg-transparent border-bottom py-3"><i class="fas fa-check-circle text-success me-3"></i> <strong>Citizenship:</strong> Must be a Malaysian Citizen.</li>
                <li class="list-group-item bg-transparent border-bottom py-3"><i class="fas fa-check-circle text-success me-3"></i> <strong>Age Limit:</strong> 18 years old and above.</li>
                <li class="list-group-item bg-transparent border-bottom py-3"><i class="fas fa-check-circle text-success me-3"></i> <strong>Homeownership:</strong> First-time homebuyer only.</li>
                <li class="list-group-item bg-transparent border-bottom py-3"><i class="fas fa-check-circle text-success me-3"></i> <strong>Income Bracket:</strong> Must not exceed the specific property limit.</li>
                <li class="list-group-item bg-transparent py-3"><i class="fas fa-check-circle text-success me-3"></i> <strong>Dependents:</strong> Declaration of family size required.</li>
            </ul>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 h-100">
                <h3 class="fw-bold text-danger mb-4"><i class="fas fa-gavel me-2"></i> Crucial Legal Restrictions (Must Read)</h3>
                <p class="text-muted">Buying a government-subsidized home comes with strict legal obligations under the Housing Development Act. Failure to comply may lead to blacklisting or legal seizure of the property.</p>
                
                <div class="row mt-4">
                    <div class="col-md-6 mb-4">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-lock text-danger fa-2x me-3 mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-1">10-Year Moratorium</h6>
                                <p class="small text-muted">The property <strong>cannot be sold</strong> within the first 10 years without state approval.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-ban text-danger fa-2x me-3 mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-1">No Renting Allowed</h6>
                                <p class="small text-muted">Owners are strictly prohibited from renting out the unit. It is for <strong>owner-occupancy only</strong>.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-file-invoice-dollar text-danger fa-2x me-3 mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Truthful Declaration</h6>
                                <p class="small text-muted">Falsifying income details is a <strong>criminal offense</strong> and leads to immediate disqualification.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-house-user text-danger fa-2x me-3 mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-1">First-Time Buyer</h6>
                                <p class="small text-muted">You and your spouse must not currently own any residential property in the state.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card bg-dark text-white border-0 shadow-sm rounded-4 p-4 p-lg-5 h-100">
                <h5 class="fw-bold text-warning mb-4"><i class="fas fa-folder-open me-2"></i> Documents You Need</h5>
                <p class="small opacity-75 mb-4">Prepare these digital copies for your O2O offline verification:</p>
                <ul class="list-unstyled">
                    <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-square text-success me-3 fs-5"></i> Latest 3-Month Salary Slips</li>
                    <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-square text-success me-3 fs-5"></i> Latest EPF Statement (KWSP)</li>
                    <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-square text-success me-3 fs-5"></i> Employment Confirmation Letter</li>
                    <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-square text-success me-3 fs-5"></i> Marriage Certificate (If applicable)</li>
                </ul>
                <div class="mt-auto p-3 bg-secondary bg-opacity-25 rounded text-center">
                    <p class="small mb-0 fst-italic">"Online submission is for eligibility pre-check only. No payments are made through this portal."</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-light rounded-4 p-4 p-lg-5 text-center shadow-sm">
        <h3 class="fw-bold mb-5" style="color: black;">The Safe O2O Application Process</h3>
        <div class="row g-4 position-relative">
            <div class="col-md-3">
                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border-bottom border-primary border-4">
                    <i class="fas fa-laptop-house fa-3x text-primary mb-3"></i>
                    <h6 class="fw-bold" style="color: black;">1. Online Intake</h6>
                    <p class="small text-muted">Submit basic income details through our secure portal.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border-bottom border-primary border-4">
                    <i class="fas fa-user-check fa-3x text-primary mb-3"></i>
                    <h6 class="fw-bold" style="color: black;">2. Staff Review</h6>
                    <p class="small text-muted">Our regional staff verifies your bracket status offline.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border-bottom border-success border-4">
                    <i class="fab fa-whatsapp fa-3x text-success mb-3"></i>
                    <h6 class="fw-bold" style="color: black;">3. Mobile Alert</h6>
                    <p class="small text-muted">Qualified applicants receive an official WhatsApp notification.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border-bottom border-warning border-4">
                    <i class="fas fa-key fa-3x text-warning mb-3"></i>
                    <h6 class="fw-bold" style="color: black;">4. Offline Handover</h6>
                    <p class="small text-muted">Visit the office for Contract Signing and key collection.</p>
                </div>
            </div>
        </div>
        <div class="mt-5">
            <?php 
            $targetUrl = 'login.php';
            if (isset($_SESSION['role'])) {
                $targetUrl = ($_SESSION['role'] === 'CUSTOMER') ? 'customer/properties.php?filter_type=AFFORDABLE' : 'properties.php?filter_type=AFFORDABLE';
            }
            ?>
            <a href="<?php echo $targetUrl; ?>" class="btn btn-primary btn-lg px-5 shadow">View Affordable Inventory</a>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
