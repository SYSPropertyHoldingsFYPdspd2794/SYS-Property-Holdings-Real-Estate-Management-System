<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: customer/financial_planner.php
 * DESCRIPTION: Advanced PropTech Financial Engine. Includes Hidden Cost Billing, Rent vs Buy Simulation, and DSR Lifestyle Affordability Matrix.
 */

session_start();
require_once '../includes/db_connect.php';

// Allow both logged-in customers and guests to use the planner to attract leads
$is_logged_in = isset($_SESSION['account_id']) && $_SESSION['role'] === 'CUSTOMER';
$account_id = $is_logged_in ? $_SESSION['account_id'] : null;

include_once '../includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="position-relative overflow-hidden bg-dark text-white py-5" style="background: linear-gradient(rgba(17, 20, 24, 0.85), rgba(17, 20, 24, 0.95)), url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=2070&auto=format&fit=crop') center center / cover no-repeat; padding-top: 80px !important; padding-bottom: 80px !important;">
    <div class="container py-5 text-center position-relative z-3">
        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill mb-3 fw-bold tracking-widest text-uppercase">PropTech Intelligence</span>
        <h1 class="display-3 fw-bold text-white mb-4 tracking-tight">Smart Financial Planner</h1>
        <p class="lead w-75 mx-auto text-light opacity-75 fs-5">Make data-driven property decisions. Uncover hidden transaction costs, simulate long-term rent vs. buy trajectories, and stress-test your lifestyle affordability using real Malaysian banking algorithms.</p>
    </div>
    <div class="position-absolute bottom-0 start-0 w-100 bg-light" style="height: 20px; clip-path: polygon(0 100%, 100% 100%, 100% 0);"></div>
</div>

<div class="container my-5 py-3">
    <ul class="nav nav-pills nav-fill gap-3 mb-5 p-2 bg-white shadow-sm rounded-pill border" id="plannerTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill fw-bold py-3 fs-5" id="hidden-costs-tab" data-bs-toggle="pill" data-bs-target="#hidden-costs" type="button" role="tab">
                <i class="fas fa-file-invoice-dollar me-2"></i> Hidden Costs Bill
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-bold py-3 fs-5" id="rent-buy-tab" data-bs-toggle="pill" data-bs-target="#rent-buy" type="button" role="tab">
                <i class="fas fa-balance-scale me-2"></i> Rent vs Buy Simulator
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-bold py-3 fs-5" id="lifestyle-tab" data-bs-toggle="pill" data-bs-target="#lifestyle" type="button" role="tab">
                <i class="fas fa-heartbeat me-2"></i> Lifestyle Affordability
            </button>
        </li>
    </ul>

    <div class="tab-content" id="plannerTabsContent">
        
        <div class="tab-pane fade show active fade-up" id="hidden-costs" role="tabpanel">
            <div class="row g-5">
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-4 p-md-5">
                        <h4 class="fw-bold text-dark mb-4 border-bottom pb-3"><i class="fas fa-sliders-h text-primary me-2"></i> Asset Parameters</h4>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">Target Property State</label>
                            <select id="hc-state" class="form-select form-select-lg bg-light border-0 shadow-sm">
                                <option value="Johor">Johor</option>
                                <option value="Kuala Lumpur">Kuala Lumpur</option>
                                <option value="Selangor">Selangor</option>
                                <option value="Penang">Penang</option>
                                <option value="Others">Other States</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary d-flex justify-content-between">
                                <span>Property Valuation Price</span>
                                <span class="text-primary" id="hc-price-display">RM 500,000</span>
                            </label>
                            <input type="range" class="form-range custom-slider" id="hc-price-slider" min="100000" max="2500000" step="10000" value="500000">
                            <div class="d-flex justify-content-between text-muted small mt-1 font-monospace">
                                <span>100K</span><span>2.5M+</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary d-flex justify-content-between">
                                <span>Downpayment (%)</span>
                                <span class="text-primary" id="hc-dp-display">10%</span>
                            </label>
                            <input type="range" class="form-range custom-slider" id="hc-dp-slider" min="0" max="100" step="5" value="10">
                        </div>

                        <div class="mb-4 p-3 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="hc-first-home" checked>
                                <label class="form-check-label fw-bold text-dark ms-2" for="hc-first-home">First Time Home Buyer</label>
                            </div>
                            <small class="text-muted d-block mt-2 ms-5">Activates MOT and Legal Stamp Duty exemptions up to RM 500,000 based on Malaysian standard regulations.</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card border-0 shadow-lg rounded-4 h-100 bg-dark text-white p-4 p-md-5 receipt-card position-relative overflow-hidden">
                        <div class="receipt-edge-top position-absolute top-0 start-0 w-100 h-100 pointer-events-none"></div>
                        
                        <div class="text-center mb-4 position-relative z-index-2">
                            <h2 class="fw-bold tracking-widest text-uppercase text-warning m-0">Transaction Bill</h2>
                            <p class="text-light opacity-50 font-monospace small">SYS PROPERTY HOLDINGS - ESTIMATE</p>
                        </div>

                        <div class="receipt-content font-monospace position-relative z-index-2">
                            <h6 class="text-uppercase text-muted border-bottom border-secondary pb-2 mb-3 fw-bold">A. Upfront Entry Capital (One-Time)</h6>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2 hover-highlight p-1 rounded">
                                <div><span class="d-block text-light">Downpayment Amount</span><small class="text-muted tiny">Asset Equity Deposit</small></div>
                                <div class="text-end fw-bold text-white fs-5" id="bill-dp">RM 0.00</div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2 hover-highlight p-1 rounded">
                                <div><span class="d-block text-light">SPA Legal Fees</span><small class="text-muted tiny">Tiered algorithm based on price</small></div>
                                <div class="text-end fw-bold text-white" id="bill-spa-legal">RM 0.00</div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 hover-highlight p-1 rounded">
                                <div><span class="d-block text-light">SPA Stamp Duty (MOT)</span><small class="text-muted tiny" id="bill-mot-note">Transfer of ownership tax</small></div>
                                <div class="text-end fw-bold text-white" id="bill-mot">RM 0.00</div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 hover-highlight p-1 rounded">
                                <div><span class="d-block text-light">Loan Agreement Legal</span><small class="text-muted tiny">Bank facility documentation</small></div>
                                <div class="text-end fw-bold text-white" id="bill-loan-legal">RM 0.00</div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 hover-highlight p-1 rounded">
                                <div><span class="d-block text-light">Loan Stamp Duty</span><small class="text-muted tiny">0.5% of total loan amount</small></div>
                                <div class="text-end fw-bold text-white" id="bill-loan-stamp">RM 0.00</div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4 hover-highlight p-1 rounded">
                                <div><span class="d-block text-light">Valuation Fee</span><small class="text-muted tiny">Standard bank property appraisal</small></div>
                                <div class="text-end fw-bold text-white" id="bill-valuation">RM 0.00</div>
                            </div>

                            <h6 class="text-uppercase text-muted border-bottom border-secondary pb-2 mb-3 fw-bold mt-4">B. Subsequent Run Rate (Recurring)</h6>

                            <div class="d-flex justify-content-between align-items-center mb-2 hover-highlight p-1 rounded">
                                <div><span class="d-block text-light">Strata Maintenance</span><small class="text-warning tiny fw-bold">[MONTHLY]</small></div>
                                <div class="text-end fw-bold text-white" id="bill-maintenance">RM 0.00</div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 hover-highlight p-1 rounded">
                                <div><span class="d-block text-light">Quit Rent (Cukai Tanah)</span><small class="text-info tiny fw-bold">[YEARLY]</small></div>
                                <div class="text-end fw-bold text-white" id="bill-cukai-tanah">RM 0.00</div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4 hover-highlight p-1 rounded">
                                <div><span class="d-block text-light">Assessment (Cukai Pintu)</span><small class="text-info tiny fw-bold">[YEARLY]</small></div>
                                <div class="text-end fw-bold text-white" id="bill-cukai-pintu">RM 0.00</div>
                            </div>

                            <hr class="border-light opacity-50 my-4" style="border-top: 2px dashed;">

                            <div class="d-flex justify-content-between align-items-end p-3 bg-warning bg-opacity-25 rounded-3 border border-warning border-opacity-50">
                                <div>
                                    <h5 class="text-warning fw-bold m-0 text-uppercase">Total Cash Required</h5>
                                    <small class="text-light opacity-75 tiny">Day 1 capital to prepare</small>
                                </div>
                                <div class="text-end text-warning fw-bold display-6 m-0" id="bill-total-upfront">RM 0.00</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade fade-up" id="rent-buy" role="tabpanel">
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card bg-white border-0 shadow-sm rounded-4 p-4 h-100 border-top border-danger border-4">
                        <h5 class="fw-bold text-dark mb-4"><i class="fas fa-home text-danger me-2"></i> Current Renting Trajectory</h5>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label text-muted small fw-bold">Current Monthly Rent (RM)</label>
                                <input type="number" id="rb-rent" class="form-control form-control-lg bg-light" value="1500">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label text-muted small fw-bold">Annual Rent Increase (%)</label>
                                <input type="number" id="rb-rent-inc" class="form-control form-control-lg bg-light" value="3.5" step="0.1">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-white border-0 shadow-sm rounded-4 p-4 h-100 border-top border-success border-4">
                        <h5 class="fw-bold text-dark mb-4"><i class="fas fa-building text-success me-2"></i> Buying Asset Trajectory</h5>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label class="form-label text-muted small fw-bold">Property Value (RM)</label>
                                <input type="number" id="rb-price" class="form-control form-control-lg bg-light" value="500000">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label text-muted small fw-bold">Loan Rate (%)</label>
                                <input type="number" id="rb-rate" class="form-control form-control-lg bg-light" value="4.0" step="0.1">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label text-muted small fw-bold">Asset Appreciation (%)</label>
                                <input type="number" id="rb-appreciation" class="form-control form-control-lg bg-light" value="4.0" step="0.1">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
                <div class="card-header bg-dark text-white p-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0"><i class="fas fa-chart-area text-warning me-2"></i> 30-Year Wealth Accumulation Projection</h5>
                    <select id="rb-tenure" class="form-select form-select-sm w-auto bg-dark text-white border-secondary">
                        <option value="10">10 Year Projection</option>
                        <option value="20">20 Year Projection</option>
                        <option value="30" selected>30 Year Projection</option>
                        <option value="35">35 Year Projection</option>
                    </select>
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <div class="p-4 bg-danger bg-opacity-10 rounded-4 border border-danger border-opacity-25 text-center h-100">
                                <h6 class="text-danger fw-bold text-uppercase mb-2">Total Sunk Cost (Renting)</h6>
                                <h2 class="display-6 fw-bold text-danger m-0" id="rb-sunk-cost">RM 0.00</h2>
                                <p class="text-muted small mt-2 m-0">Zero equity retained. 100% expense.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 bg-success bg-opacity-10 rounded-4 border border-success border-opacity-25 text-center h-100">
                                <h6 class="text-success fw-bold text-uppercase mb-2">Net Property Equity (Buying)</h6>
                                <h2 class="display-6 fw-bold text-success m-0" id="rb-net-equity">RM 0.00</h2>
                                <p class="text-muted small mt-2 m-0">Property Value minus Remaining Loan Balance.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div style="position: relative; height:400px; width:100%;">
                        <canvas id="rentBuyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade fade-up" id="lifestyle" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-4 p-md-5">
                        <h4 class="fw-bold text-dark mb-2"><i class="fas fa-file-invoice text-info me-2"></i> Cash Flow & Commitments</h4>
                        <p class="text-muted small mb-4 pb-3 border-bottom">Fill in your monthly parameters to stress-test your Debt Service Ratio (DSR).</p>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-success"><i class="fas fa-wallet me-1"></i> Net Income (After Tax/EPF)</label>
                                <div class="input-group input-group-lg shadow-sm">
                                    <span class="input-group-text bg-light border-0">RM</span>
                                    <input type="number" id="dsr-income" class="form-control bg-light border-0" value="5000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-danger"><i class="fas fa-car me-1"></i> Car Loan Installment</label>
                                <div class="input-group input-group-lg shadow-sm">
                                    <span class="input-group-text bg-light border-0">RM</span>
                                    <input type="number" id="dsr-car" class="form-control bg-light border-0" value="600">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-danger"><i class="fas fa-credit-card me-1"></i> Credit Card Minimum</label>
                                <div class="input-group input-group-lg shadow-sm">
                                    <span class="input-group-text bg-light border-0">RM</span>
                                    <input type="number" id="dsr-cc" class="form-control bg-light border-0" value="150">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-danger"><i class="fas fa-graduation-cap me-1"></i> PTPTN / Personal Loan</label>
                                <div class="input-group input-group-lg shadow-sm">
                                    <span class="input-group-text bg-light border-0">RM</span>
                                    <input type="number" id="dsr-ptptn" class="form-control bg-light border-0" value="200">
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <h6 class="fw-bold text-secondary text-uppercase tracking-wider small border-bottom pb-2">Lifestyle Expenditures (Non-Bank)</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted small">Food & Groceries</label>
                                <input type="number" id="dsr-food" class="form-control bg-light" value="1000">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted small">Entertainment</label>
                                <input type="number" id="dsr-ent" class="form-control bg-light" value="400">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted small">Utilities & Subs</label>
                                <input type="number" id="dsr-util" class="form-control bg-light" value="300">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card border-0 shadow-lg rounded-4 h-100 bg-dark text-white p-4 p-md-5 d-flex flex-column">
                        <div class="text-center mb-4">
                            <h4 class="fw-bold text-warning mb-1">Affordability Matrix</h4>
                            <p class="text-light opacity-50 small m-0">Bank Standard DSR Evaluation</p>
                        </div>

                        <div class="position-relative mx-auto mb-4" style="width: 220px; height: 220px;">
                            <canvas id="dsrChart"></canvas>
                            <div class="position-absolute top-50 start-50 translate-middle text-center w-100">
                                <h2 class="fw-bold m-0" id="dsr-percentage-display" style="font-size: 2.5rem;">0%</h2>
                                <small class="text-uppercase tracking-wider text-muted fw-bold" style="font-size: 0.7rem;">Current DSR</small>
                            </div>
                        </div>

                        <div id="dsr-warning-box" class="alert alert-success border-0 small text-center fw-bold mb-4 shadow-sm">
                            <i class="fas fa-check-circle me-1"></i> Excellent financial health! High approval chance.
                        </div>

                        <div class="mt-auto bg-white bg-opacity-10 p-4 rounded-4 border border-secondary border-opacity-25">
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary border-opacity-25 pb-3">
                                <div>
                                    <span class="d-block text-light fw-bold">Max Safe Housing Installment</span>
                                    <small class="text-muted tiny">Remaining buffer before hitting DSR limit</small>
                                </div>
                                <div class="text-end fw-bold text-info fs-5" id="dsr-max-installment">RM 0.00</div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="d-block text-warning fw-bold fs-6 text-uppercase">Recommended Max Price</span>
                                    <small class="text-muted tiny">Based on 35 Years, 4.0% Rate</small>
                                </div>
                                <div class="text-end fw-bold text-warning display-6 m-0" id="dsr-max-price">RM 0.00</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    /* Premium UI Enhancements */
    .tracking-widest { letter-spacing: 2px; }
    .tracking-tight { letter-spacing: -1px; }
    .tiny { font-size: 0.7rem; }
    
    .fade-up { animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes fadeUp {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    .custom-slider { -webkit-appearance: none; width: 100%; height: 8px; border-radius: 4px; background: #e9ecef; outline: none; transition: 0.2s; }
    .custom-slider::-webkit-slider-thumb { -webkit-appearance: none; width: 28px; height: 28px; border-radius: 50%; background: #0d6efd; border: 4px solid #fff; cursor: pointer; box-shadow: 0 0 15px rgba(13,110,253,0.4); transition: 0.1s; }
    .custom-slider::-webkit-slider-thumb:hover { transform: scale(1.1); }
    
    .receipt-card { background: #111418; border-top: 15px solid #212529; }
    .receipt-edge-top { background-image: radial-gradient(#fff 4px, transparent 4px); background-size: 15px 15px; background-position: -5px -5px; background-repeat: repeat-x; height: 10px; z-index: 10; }
    .hover-highlight { transition: background-color 0.2s; }
    .hover-highlight:hover { background-color: rgba(255,255,255,0.05); }

    .nav-pills .nav-link { color: #6c757d; transition: all 0.3s; border: 2px solid transparent; }
    .nav-pills .nav-link.active { background-color: #212529; color: #fff; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); }
    .nav-pills .nav-link:hover:not(.active) { border-color: #dee2e6; color: #212529; }
</style>

<script>
/**
 * -------------------------------------------------------------------
 * SYS PROPERTY FINANCIAL ENGINE - CORE LOGIC
 * Implements real-world Malaysian housing transaction algorithms.
 * -------------------------------------------------------------------
 */

// Utility Formatter
const formatRM = (num) => 'RM ' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

// ==========================================
// ENGINE 1: HIDDEN COSTS ALGORITHMS (MALAYSIA)
// ==========================================
function calculateHiddenCosts() {
    const price = parseFloat(document.getElementById('hc-price-slider').value);
    const dpPercent = parseFloat(document.getElementById('hc-dp-slider').value) / 100;
    const isFirstHome = document.getElementById('hc-first-home').checked;
    
    // Update Slider Displays
    document.getElementById('hc-price-display').innerText = formatRM(price);
    document.getElementById('hc-dp-display').innerText = (dpPercent * 100).toFixed(0) + '%';

    const loanAmount = price * (1 - dpPercent);
    const dpAmount = price * dpPercent;

    // 1. SPA Legal Fees (Scale: 1.25% first 500k, 1% next 7M)
    let spaLegal = 0;
    if (price <= 500000) spaLegal = price * 0.0125;
    else spaLegal = (500000 * 0.0125) + ((price - 500000) * 0.01);
    if (spaLegal < 500) spaLegal = 500; // Minimum fee

    // 2. SPA Stamp Duty (MOT) (Scale: 1% first 100k, 2% next 400k, 3% next 500k, 4% above 1M)
    let mot = 0;
    if (price <= 100000) mot = price * 0.01;
    else if (price <= 500000) mot = (100000 * 0.01) + ((price - 100000) * 0.02);
    else if (price <= 1000000) mot = (100000 * 0.01) + (400000 * 0.02) + ((price - 500000) * 0.03);
    else mot = (100000 * 0.01) + (400000 * 0.02) + (500000 * 0.03) + ((price - 1000000) * 0.04);

    // First Home Buyer Exemption Logic (Standard Budget 2024 mapping up to 500k)
    let motNote = "Transfer of ownership tax";
    if (isFirstHome && price <= 500000) {
        mot = 0;
        motNote = "Exempted for 1st Home Buyer";
        document.getElementById('bill-mot').classList.replace('text-white', 'text-success');
    } else {
        document.getElementById('bill-mot').classList.replace('text-success', 'text-white');
    }

    // 3. Loan Legal Fees (Similar scale to SPA Legal applied to Loan Amount)
    let loanLegal = 0;
    if (loanAmount <= 500000) loanLegal = loanAmount * 0.0125;
    else loanLegal = (500000 * 0.0125) + ((loanAmount - 500000) * 0.01);
    if (loanAmount > 0 && loanLegal < 500) loanLegal = 500;
    if (loanAmount === 0) loanLegal = 0;

    // 4. Loan Stamp Duty (Flat 0.5% of loan amount)
    let loanStamp = loanAmount * 0.005;
    if (isFirstHome && price <= 500000) {
        loanStamp = 0;
        document.getElementById('bill-loan-stamp').classList.replace('text-white', 'text-success');
    } else {
        document.getElementById('bill-loan-stamp').classList.replace('text-success', 'text-white');
    }

    // 5. Valuation Fee (Scale: 0.25% first 100k, 0.2% next 2M)
    let valFee = 0;
    if (price <= 100000) valFee = price * 0.0025;
    else valFee = (100000 * 0.0025) + ((price - 100000) * 0.002);
    if (loanAmount === 0) valFee = 0; // No valuation if cash buyer

    // 6. Running Costs Estimates (Based on price proxy for size)
    const maintenance = price * 0.0006; // Rough estimate monthly
    const cukaiPintu = price * 0.001; // Rough yearly
    const cukaiTanah = price * 0.0002; // Rough yearly

    // Total Upfront Day 1
    const totalUpfront = dpAmount + spaLegal + mot + loanLegal + loanStamp + valFee;

    // Inject to DOM
    document.getElementById('bill-dp').innerText = formatRM(dpAmount);
    document.getElementById('bill-spa-legal').innerText = formatRM(spaLegal);
    document.getElementById('bill-mot').innerText = formatRM(mot);
    document.getElementById('bill-mot-note').innerText = motNote;
    document.getElementById('bill-loan-legal').innerText = formatRM(loanLegal);
    document.getElementById('bill-loan-stamp').innerText = formatRM(loanStamp);
    document.getElementById('bill-valuation').innerText = formatRM(valFee);
    
    document.getElementById('bill-maintenance').innerText = formatRM(maintenance);
    document.getElementById('bill-cukai-pintu').innerText = formatRM(cukaiPintu);
    document.getElementById('bill-cukai-tanah').innerText = formatRM(cukaiTanah);
    
    document.getElementById('bill-total-upfront').innerText = formatRM(totalUpfront);
}

document.getElementById('hc-price-slider').addEventListener('input', calculateHiddenCosts);
document.getElementById('hc-dp-slider').addEventListener('input', calculateHiddenCosts);
document.getElementById('hc-first-home').addEventListener('change', calculateHiddenCosts);


// ==========================================
// ENGINE 2: RENT VS BUY SIMULATION
// ==========================================
let rbChartInstance = null;

function calculateRentVsBuy() {
    const currentRent = parseFloat(document.getElementById('rb-rent').value) || 0;
    const rentIncRate = parseFloat(document.getElementById('rb-rent-inc').value) / 100 || 0;
    const propertyPrice = parseFloat(document.getElementById('rb-price').value) || 0;
    const loanRate = parseFloat(document.getElementById('rb-rate').value) / 100 || 0;
    const propAppreciation = parseFloat(document.getElementById('rb-appreciation').value) / 100 || 0;
    const tenureYears = parseInt(document.getElementById('rb-tenure').value) || 30;

    const labels = [];
    const sunkCostData = [];
    const netEquityData = [];

    let totalSunkCost = 0;
    let currentMonthlyRent = currentRent;
    
    // Standard Loan Math
    const dp = propertyPrice * 0.10; // Assume 10% DP for simulation
    let loanBalance = propertyPrice - dp;
    const monthlyRate = loanRate / 12;
    const totalMonths = tenureYears * 12;
    const monthlyInstallment = (loanBalance * monthlyRate) / (1 - Math.pow(1 + monthlyRate, -totalMonths));

    for (let year = 1; year <= tenureYears; year++) {
        labels.push('Year ' + year);
        
        // Renting Track (Accumulated Rent Paid)
        let yearlyRent = 0;
        for (let m = 0; m < 12; m++) yearlyRent += currentMonthlyRent;
        totalSunkCost += yearlyRent;
        sunkCostData.push(totalSunkCost);
        currentMonthlyRent *= (1 + rentIncRate); // Rent increases annually

        // Buying Track (Property Value - Remaining Loan)
        const currentPropValue = propertyPrice * Math.pow(1 + propAppreciation, year);
        // Calculate remaining loan balance after 'year' years
        const monthsPaid = year * 12;
        let remainingBalance = 0;
        if (loanRate > 0) {
            remainingBalance = loanBalance * (Math.pow(1 + monthlyRate, totalMonths) - Math.pow(1 + monthlyRate, monthsPaid)) / (Math.pow(1 + monthlyRate, totalMonths) - 1);
        } else {
            remainingBalance = loanBalance - (monthlyInstallment * monthsPaid);
        }
        
        const netEquity = currentPropValue - Math.max(0, remainingBalance);
        netEquityData.push(netEquity);
    }

    // Update displays
    document.getElementById('rb-sunk-cost').innerText = formatRM(sunkCostData[sunkCostData.length - 1]);
    document.getElementById('rb-net-equity').innerText = formatRM(netEquityData[netEquityData.length - 1]);

    // Render Chart
    const ctx = document.getElementById('rentBuyChart').getContext('2d');
    if (rbChartInstance) {
        rbChartInstance.destroy();
    }
    
    rbChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Sunk Cost (Accumulated Rent)',
                    data: sunkCostData,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Net Property Equity (Buying)',
                    data: netEquityData,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': RM ' + context.parsed.y.toLocaleString('en-US', {maximumFractionDigits: 0});
                        }
                    }
                },
                legend: { position: 'bottom' }
            },
            scales: {
                y: {
                    ticks: { callback: function(value) { return 'RM ' + (value/1000) + 'k'; } },
                    grid: { color: '#e9ecef' }
                },
                x: { grid: { display: false } }
            }
        }
    });
}

const rbInputs = ['rb-rent', 'rb-rent-inc', 'rb-price', 'rb-rate', 'rb-appreciation', 'rb-tenure'];
rbInputs.forEach(id => {
    document.getElementById(id).addEventListener('input', calculateRentVsBuy);
});


// ==========================================
// ENGINE 3: DSR & LIFESTYLE AFFORDABILITY
// ==========================================
let dsrChartInstance = null;

function calculateDSR() {
    const income = parseFloat(document.getElementById('dsr-income').value) || 0;
    const car = parseFloat(document.getElementById('dsr-car').value) || 0;
    const cc = parseFloat(document.getElementById('dsr-cc').value) || 0;
    const ptptn = parseFloat(document.getElementById('dsr-ptptn').value) || 0;
    
    // Lifestyle metrics (Not used for bank DSR, but used for reality check)
    const food = parseFloat(document.getElementById('dsr-food').value) || 0;
    const ent = parseFloat(document.getElementById('dsr-ent').value) || 0;
    const util = parseFloat(document.getElementById('dsr-util').value) || 0;

    const totalBankDebt = car + cc + ptptn;
    const dsrLimit = income < 3500 ? 0.60 : 0.70; // Bank Negara safe limits
    
    let currentDSR = 0;
    if (income > 0) {
        currentDSR = (totalBankDebt / income) * 100;
    }

    const maxAllowedTotalDebt = income * dsrLimit;
    let maxSafeInstallment = maxAllowedTotalDebt - totalBankDebt;
    if (maxSafeInstallment < 0) maxSafeInstallment = 0;

    // Reverse Engineer Max Property Price based on Max Installment (Assume 4% 35Y)
    const rate = 0.04;
    const years = 35;
    const monthlyRate = rate / 12;
    const n = years * 12;
    // P = (A / r) * (1 - (1+r)^-n)
    let maxLoanAmount = 0;
    if (monthlyRate > 0) {
        maxLoanAmount = (maxSafeInstallment / monthlyRate) * (1 - Math.pow(1 + monthlyRate, -n));
    }
    const maxPropPrice = maxLoanAmount / 0.90; // Assuming 90% LTV Margin of Finance

    // Update Text UI
    document.getElementById('dsr-percentage-display').innerText = currentDSR.toFixed(1) + '%';
    document.getElementById('dsr-max-installment').innerText = formatRM(maxSafeInstallment);
    document.getElementById('dsr-max-price').innerText = formatRM(maxPropPrice);

    // Update Warning Box
    const warnBox = document.getElementById('dsr-warning-box');
    if (currentDSR > (dsrLimit * 100)) {
        warnBox.className = 'alert alert-danger border-0 small text-center fw-bold mb-4 shadow-sm';
        warnBox.innerHTML = '<i class="fas fa-ban me-1"></i> DSR Limit Exceeded! Bank loan rejection highly probable.';
        document.getElementById('dsr-percentage-display').classList.replace('text-success', 'text-danger');
    } else if (currentDSR > 50) {
        warnBox.className = 'alert alert-warning text-dark border-0 small text-center fw-bold mb-4 shadow-sm';
        warnBox.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Borderline DSR. Minimize debts to secure better loan margins.';
        document.getElementById('dsr-percentage-display').classList.remove('text-success', 'text-danger');
        document.getElementById('dsr-percentage-display').classList.add('text-warning');
    } else {
        warnBox.className = 'alert alert-success border-0 small text-center fw-bold mb-4 shadow-sm';
        warnBox.innerHTML = '<i class="fas fa-check-circle me-1"></i> Excellent financial health! High loan approval probability.';
        document.getElementById('dsr-percentage-display').classList.remove('text-danger', 'text-warning');
        document.getElementById('dsr-percentage-display').classList.add('text-success');
    }

    // Render Doughnut Chart
    const ctx = document.getElementById('dsrChart').getContext('2d');
    if (dsrChartInstance) dsrChartInstance.destroy();
    
    // Residual calculation for visual chart
    let residual = 100 - currentDSR;
    if (residual < 0) residual = 0;
    
    let color = '#198754';
    if (currentDSR > 50) color = '#ffc107';
    if (currentDSR > (dsrLimit * 100)) color = '#dc3545';

    dsrChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Current Bank Debt Ratio', 'Available Free Ratio'],
            datasets: [{
                data: [currentDSR, residual],
                backgroundColor: [color, '#333333'],
                borderWidth: 0,
                cutout: '80%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            },
            animation: { duration: 800, animateScale: true }
        }
    });
}

const dsrInputs = ['dsr-income', 'dsr-car', 'dsr-cc', 'dsr-ptptn', 'dsr-food', 'dsr-ent', 'dsr-util'];
dsrInputs.forEach(id => {
    document.getElementById(id).addEventListener('input', calculateDSR);
});


// ==========================================
// INITIALIZATION KICK-OFF
// ==========================================
window.onload = function() {
    calculateHiddenCosts();
    calculateRentVsBuy();
    calculateDSR();
};

</script>

<?php include_once '../includes/footer.php'; ?>