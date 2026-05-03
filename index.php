<?php include 'includes/header.php';?>

<section class="hero-banner text-center">
<div class="container">
<h1 class="display-3 fw-bold mb-4">Your First Home Starts Here</h1>
<p class="lead mb-5">Experience Malaysia's premier Online-to-Offline real estate platform. Exclusive access to top-tier commercial units, standard terraces, and government subsidized housing.</p>
<a href="#" class="btn btn-primary btn-lg px-5 py-3 rounded-pill fw-bold shadow">View Catalog</a>
</div>
</section>

<section class="container my-5 py-5">
<h2 class="fw-bold mb-4">New & Upcoming Developments</h2>
<div class="horizontal-scroll">
<div class="card scroll-card">
<img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=500&h=300&fit=crop" class="card-img-top" alt="Development 1">
<div class="card-body">
<h5 class="card-title fw-bold">Palmwood Residences</h5>
<p class="card-text text-muted"><i class="fas fa-map-marker-alt"></i> Johor Bahru</p>
</div>
</div>
<div class="card scroll-card">
<img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=500&h=300&fit=crop" class="card-img-top" alt="Development 2">
<div class="card-body">
<h5 class="card-title fw-bold">Eco Square Hub</h5>
<p class="card-text text-muted"><i class="fas fa-map-marker-alt"></i> Shah Alam</p>
</div>
</div>
<div class="card scroll-card">
<img src="https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?w=500&h=300&fit=crop" class="card-img-top" alt="Development 3">
<div class="card-body">
<h5 class="card-title fw-bold">Citrine Hills Phase 2</h5>
<p class="card-text text-muted"><i class="fas fa-map-marker-alt"></i> Kulai</p>
</div>
</div>
<div class="card scroll-card">
<img src="https://images.unsplash.com/photo-1515263487990-61b07816b324?w=500&h=300&fit=crop" class="card-img-top" alt="Development 4">
<div class="card-body">
<h5 class="card-title fw-bold">Summera Grove</h5>
<p class="card-text text-muted"><i class="fas fa-map-marker-alt"></i> Petaling Jaya</p>
</div>
</div>
</div>
</section>

<section class="bg-light py-5">
<div class="container">
<div class="row align-items-center">
<div class="col-lg-6">
<h2 class="fw-bold mb-4">Locate Our Offline Showrooms</h2>
<p class="lead mb-4">Select your region to find the nearest physical showroom for your exclusive offline tour and financial consultation.</p>
<select id="stateSelect" class="form-select form-select-lg mb-4">
<option value="" selected disabled>Select a State</option>
<option value="Johor">Johor</option>
<option value="Selangor">Selangor</option>
<option value="Penang">Penang</option>
</select>
</div>
<div class="col-lg-6">
<div class="card border-0 shadow-sm">
<div class="card-body p-4 text-center">
<h4 class="fw-bold mb-3">Designated HQ</h4>
<div id="showroomDisplay" class="alert alert-primary fs-5 mb-0">Please select a state to view the showroom location.</div>
</div>
</div>
</div>
</div>
</div>
</section>

<section class="container my-5 py-5">
<div class="gov-housing-section p-5 rounded shadow-sm">
<div class="row">
<div class="col-lg-8">
<h2 class="fw-bold display-6 mb-3">Government Affordable Housing Initiative</h2>
<p class="lead mb-4">Partnering with RMMJ and Rumah Selangorku to provide high-quality, subsidized housing for eligible citizens.</p>
<ul class="list-unstyled fs-5 mb-4">
<li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Malaysian Citizen</li>
<li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Age 18 and above</li>
<li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Individual/Combined Household Income below RM10,000</li>
<li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> First-Time Homebuyer</li>
</ul>
<p class="text-danger fw-bold"><i class="fas fa-info-circle me-1"></i> Priority given to applicants with dependents</p>
<a href="#" class="btn btn-dark btn-lg mt-3 px-5">Apply Now</a>
</div>
<div class="col-lg-4 d-flex align-items-center justify-content-center mt-4 mt-lg-0">
<i class="fas fa-home text-primary" style="font-size: 10rem; opacity: 0.2;"></i>
</div>
</div>
</div>
</section>

<section class="container my-5 py-5 text-center">
<h2 class="fw-bold mb-5">Your O2O Property Journey</h2>
<div class="row">
<div class="col-md-3 mb-4">
<i class="fas fa-laptop-house step-icon"></i>
<h5 class="fw-bold">1. Explore 3D Catalogs</h5>
<p class="text-muted">Browse our extensive online inventory and find your perfect match.</p>
</div>
<div class="col-md-3 mb-4">
<i class="fas fa-calculator step-icon"></i>
<h5 class="fw-bold">2. Financial Pre-Check</h5>
<p class="text-muted">Use our smart calculator and upload abstracts for secure review.</p>
</div>
<div class="col-md-3 mb-4">
<i class="fas fa-map-marked-alt step-icon"></i>
<h5 class="fw-bold">3. Offline Showroom Tour</h5>
<p class="text-muted">Visit our physical locations for a personalized guided experience.</p>
</div>
<div class="col-md-3 mb-4">
<i class="fas fa-key step-icon"></i>
<h5 class="fw-bold">4. Secure Ownership</h5>
<p class="text-muted">Finalize contracts offline and receive the keys to your new home.</p>
</div>
</div>
</section>

<section class="bg-light py-5">
<div class="container my-5">
<h2 class="fw-bold mb-4 text-center">Featured Active Projects</h2>
<div class="row">
<?php
$sql = "SELECT project_name, state, property_type, price FROM properties WHERE status = 'ACTIVE' LIMIT 3";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $formatted_price = number_format($row['price'], 2);
        echo '<div class="col-md-4 mb-4">';
        echo '<div class="card h-100 border-0 shadow-sm">';
        echo '<div class="card-body">';
        echo '<span class="badge bg-primary mb-2">'. htmlspecialchars($row['property_type']). '</span>';
        echo '<h5 class="card-title fw-bold">'. htmlspecialchars($row['project_name']). '</h5>';
        echo '<p class="card-text text-muted"><i class="fas fa-map-marker-alt me-1"></i> '. htmlspecialchars($row['state']). '</p>';
        echo '<h4 class="text-success fw-bold">RM '. $formatted_price. '</h4>';
        echo '<a href="#" class="btn btn-outline-dark w-100 mt-3">View Details</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
} else {
    echo '<div class="col-12 text-center"><p>No active projects found.</p></div>';
}
?>
</div>
</div>
</section>

<script>
document.getElementById('stateSelect').addEventListener('change', function() {
    const display = document.getElementById('showroomDisplay');
    const state = this.value;
    if (state === 'Johor') {
        display.textContent = 'SYS Johor Bahru HQ';
    } else if (state === 'Selangor') {
        display.textContent = 'SYS Shah Alam HQ';
    } else if (state === 'Penang') {
        display.textContent = 'SYS George Town HQ';
    } else {
        display.textContent = 'Please select a state to view the showroom location.';
    }
});
</script>

<?php include 'includes/footer.php';?>

