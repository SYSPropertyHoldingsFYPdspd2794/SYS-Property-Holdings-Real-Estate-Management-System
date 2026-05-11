<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: about_us.php (ROOT DIRECTORY)
 * DESCRIPTION: Diversified corporate profile resembling top-tier developers.
 */
include_once 'includes/header.php';
?>
<div class="container-fluid bg-light py-5">
    <div class="container my-4 text-center">
        <h6 class="text-uppercase fw-bold text-primary tracking-widest mb-2">Master Community Developer</h6>
        <h1 class="display-3 fw-bold text-dark mb-4">Building Sustainable Futures</h1>
        <p class="lead text-muted w-75 mx-auto">SYS Property Holdings is at the forefront of Malaysia's real estate revolution. We don't just build houses; we engineer interconnected, smart communities powered by our proprietary Online-to-Offline (O2O) ecosystem.</p>
    </div>
</div>

<div class="container my-5 py-5">
    <div class="row g-5 align-items-center mb-5">
        <div class="col-lg-6">
            <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=2070&auto=format&fit=crop" class="img-fluid rounded-4 shadow-lg" alt="SYS Vision">
        </div>
        <div class="col-lg-6 px-lg-5">
            <h2 class="fw-bold mb-4">Our Vision & Mission</h2>
            <p class="fs-5 text-muted mb-4">To be the undisputed leader in delivering innovative property solutions that enrich lives and safeguard the environment for future generations.</p>
            <ul class="list-unstyled fs-5 text-muted">
                <li class="mb-3"><i class="fas fa-bullseye text-primary me-3"></i> <strong>Innovation First:</strong> Pioneering the O2O property browsing experience.</li>
                <li class="mb-3"><i class="fas fa-bullseye text-primary me-3"></i> <strong>Integrity:</strong> Transparent pricing with zero hidden online fees.</li>
                <li class="mb-3"><i class="fas fa-bullseye text-primary me-3"></i> <strong>Inclusivity:</strong> Strong partnerships to deliver affordable housing.</li>
            </ul>
        </div>
    </div>

    <div class="text-center mb-5 mt-5 pt-4">
        <h2 class="fw-bold text-dark">Our Pillars of Excellence</h2>
        <p class="text-muted">Driving growth across multiple disciplines.</p>
    </div>

    <div class="row g-4 text-center">
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-lift">
                <div class="mb-3"><i class="fas fa-city fa-3x text-primary"></i></div>
                <h5 class="fw-bold">Integrated Townships</h5>
                <p class="text-muted small">Developing self-sustaining cities that integrate residential, commercial, and recreational spaces.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-lift">
                <div class="mb-3"><i class="fas fa-leaf fa-3x text-success"></i></div>
                <h5 class="fw-bold">Green Architecture</h5>
                <p class="text-muted small">Committed to low-carbon footprints and environmentally friendly building materials.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-lift">
                <div class="mb-3"><i class="fas fa-handshake fa-3x text-warning"></i></div>
                <h5 class="fw-bold">Government Synergy</h5>
                <p class="text-muted small">Authorized partners for RMMJ and RSKU, ensuring housing accessibility for the B40 & M40.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-lift">
                <div class="mb-3"><i class="fas fa-mobile-alt fa-3x text-info"></i></div>
                <h5 class="fw-bold">O2O Integration</h5>
                <p class="text-muted small">Browse online, transact securely offline. A seamless digital bridge to your physical home.</p>
            </div>
        </div>
    </div>
</div>

<style>
.tracking-widest { letter-spacing: 3px; }
.hover-lift { transition: transform 0.3s; border-bottom: 4px solid transparent; }
.hover-lift:hover { transform: translateY(-10px); border-bottom: 4px solid #0d6efd; }
</style>

<?php include_once 'includes/footer.php'; ?>