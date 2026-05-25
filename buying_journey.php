<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: buying_journey.php (ROOT DIRECTORY)
 * DESCRIPTION: Interactive Educational Module. A comprehensive step-by-step roadmap for purchasing real estate in Malaysia, featuring government agency integrations and professional timelines.
 */

session_start();
require_once 'includes/db_connect.php';

// Accessible to both guests and logged-in customers
$is_logged_in = isset($_SESSION['account_id']) && $_SESSION['role'] === 'CUSTOMER';
$account_id = $is_logged_in ? $_SESSION['account_id'] : null;

include_once 'includes/header.php';
?>

<div class="bg-dark text-light" style="min-height: 100vh;">

    <div class="position-relative overflow-hidden text-white py-5" style="background: linear-gradient(rgba(10, 15, 20, 0.8), rgba(10, 15, 20, 0.9)), url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?q=80&w=2073&auto=format&fit=crop') center center / cover no-repeat; padding-top: 100px !important; padding-bottom: 100px !important;">
        <div class="container py-5 text-center position-relative z-3">
            <span class="badge bg-danger text-white px-3 py-2 rounded-pill mb-3 fw-bold tracking-widest text-uppercase">The Master Blueprint</span>
            <h1 class="display-3 fw-bold text-white mb-4 tracking-tight">The Homebuyer's Odyssey</h1>
            <p class="lead w-75 mx-auto text-light opacity-75 fs-5">Navigate the complexities of the Malaysian real estate landscape with absolute confidence. From initial financial health checks to collecting the keys to your new empire, follow our definitive 7-step roadmap guided by official regulatory standards.</p>
            <div class="mt-5">
                <a href="#timeline-start" class="btn btn-warning btn-lg fw-bold px-5 py-3 rounded-pill shadow-lg text-dark explore-btn">Begin The Journey <i class="fas fa-arrow-down ms-2"></i></a>
            </div>
        </div>
        <div class="position-absolute bottom-0 start-0 w-100 bg-dark" style="height: 30px; clip-path: polygon(0 100%, 100% 100%, 100% 0);"></div>
    </div>

    <div class="bg-dark" id="timeline-start">
        <div class="container py-5">
            
            <div class="text-center mb-5 pb-4 reveal-item">
                <h2 class="display-5 fw-bold text-white">7 Steps to Property Ownership</h2>
                <p class="text-light opacity-75 fs-5">Standard Operating Procedures (SOP) compliant with the Housing Development Act (HDA) Malaysia.</p>
                <hr class="w-25 mx-auto bg-primary" style="height: 4px; opacity: 1; border-radius: 2px;">
            </div>

            <div class="timeline-wrapper position-relative">
                <div class="timeline-spine d-none d-lg-block" style="background: #333;"></div>

                <div class="row align-items-center mb-5 pb-5 timeline-item reveal-item">
                    <div class="col-lg-5 order-2 order-lg-1 text-lg-end text-center pe-lg-5">
                        <div class="badge bg-secondary text-white px-3 py-2 rounded-pill mb-3 fs-6 shadow-sm">STEP 01</div>
                        <h3 class="fw-bold text-primary mb-3">Financial Health & DSR Check</h3>
                        <p class="text-light opacity-75 lh-lg">Before hunting for properties, you must establish a concrete budget. Banks evaluate your borrowing capacity using the <strong>Debt Service Ratio (DSR)</strong> and your central credit scores (CCRIS/CTOS). Aim to keep your DSR below 60%.</p>
                        
                        <div class="p-3 bg-dark border border-primary border-opacity-50 rounded-3 text-start d-inline-block shadow-sm mb-3 text-lg-end w-100">
                            <h6 class="fw-bold text-white mb-2"><i class="fas fa-building text-warning me-2"></i> Government & Authorities Involved:</h6>
                            <ul class="list-unstyled m-0 small text-light opacity-75 font-monospace">
                                <li class="mb-1"><strong class="text-warning">BNM (Bank Negara Malaysia):</strong> Generates CCRIS report.</li>
                                <li class="mb-1"><strong class="text-warning">KWSP (EPF):</strong> Allows Account 2 withdrawal for house purchasing.</li>
                            </ul>
                        </div>
                        
                        <div class="alert alert-warning border-0 shadow-sm text-start small fw-bold bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-lightbulb text-warning me-2"></i> Pro Tip: Utilize our <a href="financial_planner.php" class="text-warning text-decoration-underline">SYS Financial Planner</a> to simulate your DSR before approaching banks.
                        </div>
                    </div>
                    <div class="col-lg-2 order-1 order-lg-2 d-none d-lg-flex justify-content-center z-3">
                        <div class="timeline-dot bg-primary border border-4 border-dark shadow"></div>
                    </div>
                    <div class="col-lg-5 order-3 order-lg-3 ps-lg-5 mb-4 mb-lg-0 text-center">
                        <div class="img-wrapper rounded-4 shadow-lg overflow-hidden position-relative border border-5 border-secondary">
                            <img src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?q=80&w=2070&auto=format&fit=crop" class="img-fluid w-100" alt="Financial Planning" style="height: 350px; object-fit: cover;">
                            <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-dark bg-opacity-75 text-white text-start">
                                <i class="fas fa-search-dollar fa-2x mb-2 text-warning"></i>
                                <h6 class="fw-bold m-0">Determine Purchasing Power</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center mb-5 pb-5 timeline-item reveal-item">
                    <div class="col-lg-5 order-3 order-lg-1 pe-lg-5 mb-4 mb-lg-0 text-center">
                        <div class="img-wrapper rounded-4 shadow-lg overflow-hidden position-relative border border-5 border-secondary">
                            <img src="https://images.unsplash.com/photo-1560520653-9e0e4c89eb11?q=80&w=1973&auto=format&fit=crop" class="img-fluid w-100" alt="Property Viewing" style="height: 350px; object-fit: cover;">
                            <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-dark bg-opacity-75 text-white text-start">
                                <i class="fas fa-handshake fa-2x mb-2 text-info"></i>
                                <h6 class="fw-bold m-0">Securing Your Unit</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 order-2 order-lg-2 d-none d-lg-flex justify-content-center z-3">
                        <div class="timeline-dot bg-info border border-4 border-dark shadow"></div>
                    </div>
                    <div class="col-lg-5 order-1 order-lg-3 text-lg-start text-center ps-lg-5">
                        <div class="badge bg-secondary text-white px-3 py-2 rounded-pill mb-3 fs-6 shadow-sm">STEP 02</div>
                        <h3 class="fw-bold text-info mb-3">Booking & Letter of Offer (OTP)</h3>
                        <p class="text-light opacity-75 lh-lg">Once you've surveyed and selected your ideal property through our offline showroom, you will sign an <strong>Offer to Purchase (OTP)</strong> form. You must pay an earnest booking fee, usually <strong>2% to 3%</strong> of the property price, which forms part of your total 10% downpayment.</p>
                        
                        <div class="p-3 bg-dark border border-info border-opacity-50 rounded-3 text-start d-inline-block shadow-sm mb-3 w-100">
                            <h6 class="fw-bold text-white mb-2"><i class="fas fa-building text-warning me-2"></i> Regulatory Framework:</h6>
                            <ul class="list-unstyled m-0 small text-light opacity-75 font-monospace">
                                <li class="mb-1"><strong class="text-info">KPKT (Ministry of Housing):</strong> Governs developer licensing. Ensure you only pay booking fees to licensed developers or registered agency accounts, never personal accounts.</li>
                            </ul>
                        </div>

                        <div class="alert alert-danger border-0 shadow-sm text-start small fw-bold bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-exclamation-circle text-danger me-2"></i> Trap Avoidance: Ensure your OTP form contains a "Subject to Loan Approval" clause to get a refund if the bank rejects your loan.
                        </div>
                    </div>
                </div>

                <div class="row align-items-center mb-5 pb-5 timeline-item reveal-item">
                    <div class="col-lg-5 order-2 order-lg-1 text-lg-end text-center pe-lg-5">
                        <div class="badge bg-secondary text-white px-3 py-2 rounded-pill mb-3 fs-6 shadow-sm">STEP 03</div>
                        <h3 class="fw-bold text-success mb-3">Applying for the Mortgage</h3>
                        <p class="text-light opacity-75 lh-lg">With your OTP secured, you generally have 14 to 21 days to secure a housing loan. You will submit your 3-6 months payslips, EPF statements, and EA forms to multiple banks. Upon approval, you will sign the <strong>Bank Letter of Offer (LO)</strong>, locking in your Interest Rate (BR/OPR) and Margin of Finance (up to 90%).</p>
                        
                        <div class="p-3 bg-dark border border-success border-opacity-50 rounded-3 text-start d-inline-block shadow-sm mb-3 text-lg-end w-100">
                            <h6 class="fw-bold text-white mb-2"><i class="fas fa-building text-warning me-2"></i> Associated Entities:</h6>
                            <ul class="list-unstyled m-0 small text-light opacity-75 font-monospace">
                                <li class="mb-1"><strong class="text-success">Commercial Banks:</strong> Maybank, CIMB, Public Bank, etc.</li>
                                <li class="mb-1"><strong class="text-success">Valuers (JPPH):</strong> For subsale, banks appoint valuers to assess the true brick-and-mortar market value.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-2 order-1 order-lg-2 d-none d-lg-flex justify-content-center z-3">
                        <div class="timeline-dot bg-success border border-4 border-dark shadow"></div>
                    </div>
                    <div class="col-lg-5 order-3 order-lg-3 ps-lg-5 mb-4 mb-lg-0 text-center">
                        <div class="img-wrapper rounded-4 shadow-lg overflow-hidden position-relative border border-5 border-secondary">
                            <img src="https://images.unsplash.com/photo-1601597111158-2fceff292cdc?q=80&w=2070&auto=format&fit=crop" class="img-fluid w-100" alt="Bank Loan" style="height: 350px; object-fit: cover;">
                            <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-dark bg-opacity-75 text-white text-start">
                                <i class="fas fa-file-signature fa-2x mb-2 text-success"></i>
                                <h6 class="fw-bold m-0">Locking The Bank Finance</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center mb-5 pb-5 timeline-item reveal-item">
                    <div class="col-lg-5 order-3 order-lg-1 pe-lg-5 mb-4 mb-lg-0 text-center">
                        <div class="img-wrapper rounded-4 shadow-lg overflow-hidden position-relative border border-5 border-secondary">
                            <img src="https://images.unsplash.com/photo-1450101499163-c8848c66ca85?q=80&w=2070&auto=format&fit=crop" class="img-fluid w-100" alt="Signing SPA" style="height: 350px; object-fit: cover;">
                            <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-dark bg-opacity-75 text-white text-start">
                                <i class="fas fa-pen-nib fa-2x mb-2 text-danger"></i>
                                <h6 class="fw-bold m-0">The Legal Binding Contract</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 order-2 order-lg-2 d-none d-lg-flex justify-content-center z-3">
                        <div class="timeline-dot bg-danger border border-4 border-dark shadow"></div>
                    </div>
                    <div class="col-lg-5 order-1 order-lg-3 text-lg-start text-center ps-lg-5">
                        <div class="badge bg-secondary text-white px-3 py-2 rounded-pill mb-3 fs-6 shadow-sm">STEP 04</div>
                        <h3 class="fw-bold text-danger mb-3">Executing the S&P Agreement (SPA)</h3>
                        <p class="text-light opacity-75 lh-lg">Now you officially hire a conveyancing lawyer to draft the <strong>Sales & Purchase Agreement (SPA)</strong>. Upon signing, you must settle the remaining balance of your 10% downpayment (e.g., if booking was 2%, you pay the remaining 8%). You will also pay the Lawyer's Professional Legal Fees at this stage.</p>
                        
                        <div class="p-3 bg-dark border border-danger border-opacity-50 rounded-3 text-start d-inline-block shadow-sm w-100">
                            <h6 class="fw-bold text-white mb-2"><i class="fas fa-gavel text-warning me-2"></i> Legal Framework:</h6>
                            <ul class="list-unstyled m-0 small text-light opacity-75 font-monospace">
                                <li class="mb-1"><strong class="text-danger">HDA Schedule H/G:</strong> For under-construction projects, the SPA format is strictly protected under the Housing Development (Control and Licensing) Act 1966.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center mb-5 pb-5 timeline-item reveal-item">
                    <div class="col-lg-5 order-2 order-lg-1 text-lg-end text-center pe-lg-5">
                        <div class="badge bg-secondary text-white px-3 py-2 rounded-pill mb-3 fs-6 shadow-sm">STEP 05</div>
                        <h3 class="fw-bold text-warning mb-3">MOT & Stamp Duty Stamping</h3>
                        <p class="text-light opacity-75 lh-lg">Your lawyer submits the <strong>Memorandum of Transfer (MOT)</strong> and Loan Agreement to the government for stamping. This is where you pay the heaviest hidden cost: The Stamp Duty. For under-construction properties, MOT stamping happens later when the strata/individual title is issued.</p>
                        
                        <div class="p-3 bg-dark border border-warning border-opacity-50 rounded-3 text-start d-inline-block shadow-sm mb-3 text-lg-end w-100">
                            <h6 class="fw-bold text-white mb-2"><i class="fas fa-landmark text-warning me-2"></i> Key Government Departments:</h6>
                            <ul class="list-unstyled m-0 small text-light opacity-75 font-monospace">
                                <li class="mb-1"><strong class="text-warning">LHDN (Inland Revenue Board):</strong> Assesses and collects the Ad Valorem Stamp Duty.</li>
                                <li class="mb-1"><strong class="text-warning">PTG (Pejabat Tanah & Galian):</strong> The State Land Office registers your name into the property title.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-2 order-1 order-lg-2 d-none d-lg-flex justify-content-center z-3">
                        <div class="timeline-dot bg-warning border border-4 border-dark shadow"></div>
                    </div>
                    <div class="col-lg-5 order-3 order-lg-3 ps-lg-5 mb-4 mb-lg-0 text-center">
                        <div class="img-wrapper rounded-4 shadow-lg overflow-hidden position-relative border border-5 border-secondary">
                            <img src="https://images.unsplash.com/photo-1589829085413-56de8ae18c73?q=80&w=2112&auto=format&fit=crop" class="img-fluid w-100" alt="LHDN Stamping" style="height: 350px; object-fit: cover;">
                            <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-dark bg-opacity-75 text-white text-start">
                                <i class="fas fa-stamp fa-2x mb-2 text-warning"></i>
                                <h6 class="fw-bold m-0">Legalizing Ownership Transfer</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center mb-5 pb-5 timeline-item reveal-item">
                    <div class="col-lg-5 order-3 order-lg-1 pe-lg-5 mb-4 mb-lg-0 text-center">
                        <div class="img-wrapper rounded-4 shadow-lg overflow-hidden position-relative border border-5 border-secondary">
                            <img src="https://images.unsplash.com/photo-1541888081622-19e078970e5b?q=80&w=2070&auto=format&fit=crop" class="img-fluid w-100" alt="Construction Site" style="height: 350px; object-fit: cover;">
                            <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-dark bg-opacity-75 text-white text-start">
                                <i class="fas fa-hammer fa-2x mb-2 text-info"></i>
                                <h6 class="fw-bold m-0">Progressive Billing Commences</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 order-2 order-lg-2 d-none d-lg-flex justify-content-center z-3">
                        <div class="timeline-dot bg-info border border-4 border-dark shadow"></div>
                    </div>
                    <div class="col-lg-5 order-1 order-lg-3 text-lg-start text-center ps-lg-5">
                        <div class="badge bg-secondary text-white px-3 py-2 rounded-pill mb-3 fs-6 shadow-sm">STEP 06</div>
                        <h3 class="fw-bold text-info mb-3">Progressive Loan Disbursement</h3>
                        <p class="text-light opacity-75 lh-lg">You do NOT pay the full monthly installment immediately for new projects. The bank releases money to the developer in stages as construction completes (Progressive Billing). During this period of 2 to 4 years, you only pay <strong>Progressive Interest</strong> to the bank based on the amount disbursed.</p>
                        
                        <div class="p-3 bg-dark border border-info border-opacity-50 rounded-3 text-start d-inline-block shadow-sm w-100">
                            <h6 class="fw-bold text-white mb-2"><i class="fas fa-hard-hat text-warning me-2"></i> Construction Protection:</h6>
                            <ul class="list-unstyled m-0 small text-light opacity-75 font-monospace">
                                <li class="mb-1"><strong>Architect Certification:</strong> Funds are only released when independent architects submit the Certificate of Completion to the bank for each stage.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center mb-5 pb-5 timeline-item reveal-item">
                    <div class="col-lg-5 order-2 order-lg-1 text-lg-end text-center pe-lg-5">
                        <div class="badge bg-secondary text-white px-3 py-2 rounded-pill mb-3 fs-6 shadow-sm">FINAL STEP</div>
                        <h3 class="fw-bold text-white mb-3">Keys Collection & Vacant Possession (VP)</h3>
                        <p class="text-light opacity-75 lh-lg">Once the property obtains the CCC (Certificate of Completion and Compliance), the developer will issue the Notice of Vacant Possession. You finally collect your keys! Upon receiving keys, a 24-month <strong>Defect Liability Period (DLP)</strong> acts as your warranty to claim free repairs for any structural defects.</p>
                        
                        <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-50 rounded-3 text-start d-inline-block shadow-sm text-lg-end w-100">
                            <h6 class="fw-bold text-success mb-2"><i class="fas fa-certificate text-success me-2"></i> Final Authority Endorsement:</h6>
                            <ul class="list-unstyled m-0 small text-light opacity-75 font-monospace">
                                <li class="mb-1"><strong>Local City Council (Majlis Bandaraya):</strong> Issues the CCC to guarantee the building is legally safe for human occupation.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-2 order-1 order-lg-2 d-none d-lg-flex justify-content-center z-3">
                        <div class="timeline-dot bg-dark border border-4 border-warning shadow position-relative">
                            <i class="fas fa-key position-absolute top-50 start-50 translate-middle text-warning small"></i>
                        </div>
                    </div>
                    <div class="col-lg-5 order-3 order-lg-3 ps-lg-5 mb-4 mb-lg-0 text-center">
                        <div class="img-wrapper rounded-4 shadow-lg overflow-hidden position-relative border border-5 border-warning">
                            <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?q=80&w=2070&auto=format&fit=crop" class="img-fluid w-100" alt="Getting Keys" style="height: 350px; object-fit: cover;">
                            <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-dark bg-opacity-75 text-white text-start">
                                <i class="fas fa-door-open fa-2x mb-2 text-warning"></i>
                                <h6 class="fw-bold m-0 text-warning">Welcome Home.</h6>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="container my-5 py-5 reveal-item">
        <div class="row g-5">
            <div class="col-lg-6">
                <h3 class="fw-bold text-white mb-4 border-bottom border-primary border-3 pb-3"><i class="fas fa-book text-primary me-2"></i> Essential Real Estate Glossary</h3>
                <div class="accordion shadow-sm border-0 rounded-4 overflow-hidden" id="glossaryAccordion">
                    <div class="accordion-item border-0 border-bottom border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                Debt Service Ratio (DSR)
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#glossaryAccordion">
                            <div class="accordion-body text-light opacity-75 small bg-dark">
                                A formula used by Malaysian banks to evaluate a borrower's ability to repay a loan. Formula: (Total Monthly Debt commitments / Net Monthly Income) x 100. Most banks cap this at 60% to 70%.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 border-bottom border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold bg-dark text-white collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                Loan-To-Value (LTV) Ratio
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#glossaryAccordion">
                            <div class="accordion-body text-light opacity-75 small bg-dark">
                                The percentage of the property price that the bank is willing to finance. For your first and second residential property in Malaysia, the maximum LTV is usually 90%.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 border-bottom border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold bg-dark text-white collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                Defect Liability Period (DLP)
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#glossaryAccordion">
                            <div class="accordion-body text-light opacity-75 small bg-dark">
                                A warranty period spanning 24 months from the date of Vacant Possession (VP). Developers are mandated by HDA to repair any structural flaws or poor workmanship at no extra cost to the buyer.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold bg-dark text-white collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                                Base Rate (BR) & Overnight Policy Rate (OPR)
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#glossaryAccordion">
                            <div class="accordion-body text-light opacity-75 small bg-dark">
                                OPR is set by Bank Negara Malaysia. BR is set by commercial banks based on the OPR. When OPR rises, your floating rate mortgage monthly installment will increase.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <h3 class="fw-bold text-white mb-4 border-bottom border-warning border-3 pb-3"><i class="fas fa-landmark text-warning me-2"></i> Regulatory Agencies Matrix</h3>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 border border-secondary rounded-3 bg-dark text-light h-100 hover-border-shift">
                            <h6 class="fw-bold text-white m-0 mb-1">BNM</h6>
                            <small class="text-warning d-block mb-2" style="font-size: 0.75rem;">Bank Negara Malaysia</small>
                            <p class="small text-light opacity-75 m-0">Regulates banking systems, oversees OPR, and maintains the CCRIS central credit database.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 border border-secondary rounded-3 bg-dark text-light h-100 hover-border-shift">
                            <h6 class="fw-bold text-white m-0 mb-1">LHDN</h6>
                            <small class="text-warning d-block mb-2" style="font-size: 0.75rem;">Lembaga Hasil Dalam Negeri</small>
                            <p class="small text-light opacity-75 m-0">Validates legal documents and collects Ad Valorem Stamp Duty for property transfers.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 border border-secondary rounded-3 bg-dark text-light h-100 hover-border-shift">
                            <h6 class="fw-bold text-white m-0 mb-1">PTG</h6>
                            <small class="text-warning d-block mb-2" style="font-size: 0.75rem;">Pejabat Tanah & Galian</small>
                            <p class="small text-light opacity-75 m-0">The state land authority responsible for updating title deeds and recording ownership.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 border border-secondary rounded-3 bg-dark text-light h-100 hover-border-shift">
                            <h6 class="fw-bold text-white m-0 mb-1">KPKT</h6>
                            <small class="text-warning d-block mb-2" style="font-size: 0.75rem;">Ministry of Housing</small>
                            <p class="small text-light opacity-75 m-0">Enforces the Housing Development Act (HDA) protecting buyers from abandoned projects.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-5 pb-5">
        <div class="bg-black text-white rounded-4 p-5 text-center shadow-lg position-relative overflow-hidden border border-secondary">
            <div class="position-absolute top-0 start-0 w-100 h-100 opacity-25" style="background: repeating-linear-gradient(45deg, transparent, transparent 10px, #222 10px, #222 20px);"></div>
            <div class="position-relative z-3">
                <h2 class="fw-bold mb-3 text-white">Ready to Embark on Your Journey?</h2>
                <p class="text-light opacity-75 mb-4">You are now equipped with the ultimate Malaysian property procurement knowledge. Take the first analytical step.</p>
                <a href="financial_planner.php" class="btn btn-warning btn-lg px-5 rounded-pill fw-bold text-dark shadow"><i class="fas fa-calculator me-2"></i> Launch Financial Planner</a>
            </div>
        </div>
    </div>

</div> <!-- End of Dark Wrapper -->

<style>
    /* Premium Timeline Styling */
    .timeline-wrapper { padding: 40px 0; }
    .timeline-spine { position: absolute; left: 50%; top: 0; bottom: 0; width: 4px; background: #444; transform: translateX(-50%); z-index: 1; }
    .timeline-dot { width: 30px; height: 30px; border-radius: 50%; position: relative; margin: 0 auto; transition: all 0.3s ease; }
    .timeline-item:hover .timeline-dot { transform: scale(1.3); }
    
    .tracking-widest { letter-spacing: 2px; }
    .tracking-tight { letter-spacing: -1px; }
    .img-wrapper img { transition: transform 0.8s ease; }
    .img-wrapper:hover img { transform: scale(1.05); }

    .hover-border-shift { transition: all 0.3s; border-left: 4px solid transparent !important; }
    .hover-border-shift:hover { border-left: 4px solid #ffc107 !important; box-shadow: 0 .5rem 1rem rgba(0,0,0,.5)!important; transform: translateY(-3px); }

    /* Override Accordion colors for dark mode */
    .accordion-button:not(.collapsed) {
        background-color: #212529 !important;
        color: #ffc107 !important;
    }
    .accordion-button::after {
        filter: invert(1);
    }
    .accordion-button:not(.collapsed)::after {
        filter: invert(1);
    }

    /* Scroll Reveal Animation Classes */
    .reveal-item { opacity: 0; transform: translateY(50px); transition: all 0.8s cubic-bezier(0.25, 1, 0.5, 1); }
    .reveal-item.active { opacity: 1; transform: translateY(0); }
</style>

<script>
    // Smooth Scroll Reveal Engine
    document.addEventListener("DOMContentLoaded", function() {
        const revealItems = document.querySelectorAll(".reveal-item");
        
        const revealObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add("active");
                    observer.unobserve(entry.target); // Trigger only once
                }
            });
        }, {
            root: null,
            threshold: 0.15, // Trigger when 15% of the element is visible
            rootMargin: "0px 0px -50px 0px"
        });

        revealItems.forEach(function(item) {
            revealObserver.observe(item);
        });

        // Smooth scroll for the hero banner button
        document.querySelector('.explore-btn').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('#timeline-start').scrollIntoView({ behavior: 'smooth' });
        });
    });
</script>

<?php include_once 'includes/footer.php'; ?>