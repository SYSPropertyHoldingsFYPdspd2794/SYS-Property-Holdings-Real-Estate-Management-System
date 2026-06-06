<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$catalog_link = 'login.php'; 
$apply_link = 'login.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'CUSTOMER') {
        $catalog_link = 'customer/properties.php';
        $apply_link = 'customer/properties.php?filter_type=AFFORDABLE';
    } else {
        $catalog_link = 'properties.php';
        $apply_link = 'properties.php';
    }

include 'includes/header.php';
?>

<section class="hero-banner">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-8">
                <div class="section-kicker mb-3">Online to offline real estate</div>
                <h1 class="display-2 fw-bold mb-4">Your First Home Starts Here</h1>
                <p class="mb-5 fs-4 text-white" style="line-height: 1.6; font-weight: 400;">Explore verified homes, compare financing, and book physical showroom visits across Malaysia from one secure property platform.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?php echo $catalog_link; ?>" class="btn btn-primary btn-lg px-5 py-3">View Catalog</a>
                    <a href="showrooms.php" class="btn btn-outline-light btn-lg px-4 py-3">Find Showrooms</a>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="hero-stat-panel bg-white text-dark p-4 rounded-4 shadow-lg">
                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Active regions</small>
                            <h3 class="fw-bold mb-0">16</h3>
                        </div>
                        <div class="text-end">
                            <small class="text-muted text-uppercase fw-bold">O2O model</small>
                            <h3 class="fw-bold mb-0">100%</h3>
                        </div>
                    </div>
                    <div class="p-3 rounded-3 bg-light">
                        <div class="d-flex align-items-center gap-3">
                            <span class="step-icon mb-0"><i class="fas fa-shield-alt"></i></span>
                            <div>
                                <h6 class="fw-bold mb-1">Secure by design</h6>
                                <p class="small text-muted mb-0">Browse online, confirm commitments with trained consultants offline.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container my-5 py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="section-kicker mb-2">Featured launches</div>
            <h2 class="section-title text-white mb-0">New & Upcoming Developments</h2>
        </div>
        <a href="<?php echo $catalog_link; ?>" class="btn btn-outline-light">Browse all</a>
    </div>
    <div class="horizontal-scroll">
        <div class="card scroll-card">
            <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=500&h=300&fit=crop" class="card-img-top" alt="Development 1">
            <div class="card-body">
                <h5 class="card-title fw-bold">Palmwood Residences</h5>
                <p class="card-text text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i> Johor Bahru</p>
            </div>
        </div>
        <div class="card scroll-card">
            <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=500&h=300&fit=crop" class="card-img-top" alt="Development 2">
            <div class="card-body">
                <h5 class="card-title fw-bold">Eco Square Hub</h5>
                <p class="card-text text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i> Shah Alam</p>
            </div>
        </div>
        <div class="card scroll-card">
            <img src="https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?w=500&h=300&fit=crop" class="card-img-top" alt="Development 3">
            <div class="card-body">
                <h5 class="card-title fw-bold">Citrine Hills Phase 2</h5>
                <p class="card-text text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i> Kulai</p>
            </div>
        </div>
        <div class="card scroll-card">
            <img src="https://images.unsplash.com/photo-1515263487990-61b07816b324?w=500&h=300&fit=crop" class="card-img-top" alt="Development 4">
            <div class="card-body">
                <h5 class="card-title fw-bold">Summera Grove</h5>
                <p class="card-text text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i> Petaling Jaya</p>
            </div>
        </div>
    </div>
</section>

<section class="bg-light py-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <div class="section-kicker mb-2">Nationwide support</div>
                <h2 class="section-title text-dark fw-bold mb-4">Locate Our Offline Showrooms</h2>
                <p class="lead mb-4 text-dark" style="line-height: 1.65; font-weight: 500;">Select your state to find the nearest showroom for property tours, eligibility guidance, and financing consultation.</p>
                
                <select id="stateSelect" class="form-select form-select-lg mb-4 shadow-sm">
                    <option value="" selected disabled>-- Select a State / Territory --</option>
                    <optgroup label="States">
                        <option value="Johor">Johor</option>
                        <option value="Kedah">Kedah</option>
                        <option value="Kelantan">Kelantan</option>
                        <option value="Melaka">Melaka</option>
                        <option value="Negeri Sembilan">Negeri Sembilan</option>
                        <option value="Pahang">Pahang</option>
                        <option value="Perak">Perak</option>
                        <option value="Perlis">Perlis</option>
                        <option value="Penang">Pulau Pinang (Penang)</option>
                        <option value="Sabah">Sabah</option>
                        <option value="Sarawak">Sarawak</option>
                        <option value="Selangor">Selangor</option>
                        <option value="Terengganu">Terengganu</option>
                    </optgroup>
                    <optgroup label="Federal Territories">
                        <option value="WP Kuala Lumpur">WP Kuala Lumpur</option>
                        <option value="WP Labuan">WP Labuan</option>
                        <option value="WP Putrajaya">WP Putrajaya</option>
                    </optgroup>
                </select>
            </div>
            
            <div class="col-lg-7">
                <div class="card border-0 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 text-center">
                        <h4 class="fw-bold mb-1"><i class="fas fa-store text-primary me-2"></i>Our Showroom Location</h4>
                        <p id="showroomCity" class="text-muted mb-3">Please select a state to view details.</p>
                    </div>
                    <div class="card-body p-0 bg-light" style="height: 350px;" id="mapContainer">
                        <div class="d-flex h-100 justify-content-center align-items-center text-muted flex-column">
                            <i class="fas fa-map-marked-alt fa-4x mb-3 opacity-50"></i>
                            <p>Interactive Map will load here.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container my-5 py-5">
    <div class="gov-housing-section p-4 p-lg-5 rounded-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="section-kicker mb-2">Affordable pathway</div>
                <h2 class="fw-bold display-6 mb-3 text-white">Government Affordable Housing Initiative</h2>
                <p class="fs-5 mb-4 text-white" style="line-height: 1.6;">Partnering with RMMJ and Rumah Selangorku to provide high-quality, subsidized housing for eligible citizens.</p>
                <ul class="list-unstyled fs-5 mb-4">
                    <li class="mb-2 text-light"><i class="fas fa-check-circle text-success me-2"></i> Malaysian Citizen</li>
                    <li class="mb-2 text-light"><i class="fas fa-check-circle text-success me-2"></i> Age 18 and above</li>
                    <li class="mb-2 text-light"><i class="fas fa-check-circle text-success me-2"></i> Household Income below RM10,000</li>
                    <li class="mb-2 text-light"><i class="fas fa-check-circle text-success me-2"></i> First-Time Homebuyer</li>
                </ul>
                <p class="text-danger fw-bold"><i class="fas fa-info-circle me-1"></i> Priority given to applicants with dependents</p>
                <a href="<?php echo $apply_link; ?>" class="btn btn-primary btn-lg mt-3 px-5">Apply Now</a>
            </div>
            <div class="col-lg-4 d-none d-lg-flex align-items-center justify-content-center">
                <i class="fas fa-home text-primary" style="font-size: 10rem; opacity: 0.1;"></i>
            </div>
        </div>
    </div>
</section>

<section class="my-5 py-5 text-center bg-white">
    <div class="container">
    <div class="section-kicker mb-2">How it works</div>
    <h2 class="section-title text-dark fw-bold mb-5">Your O2O Property Journey</h2>
    <div class="row justify-content-center g-4">
        <div class="col-md-3 col-sm-6 mb-4">
            <i class="fas fa-laptop-house step-icon"></i>
            <h5 class="fw-bold text-dark">1. Explore 3D Catalogs</h5>
            <p class="text-muted">Browse our extensive online inventory and find your perfect match.</p>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <i class="fas fa-calculator step-icon"></i>
            <h5 class="fw-bold text-dark">2. Financial Pre-Check</h5>
            <p class="text-muted">Use our smart calculator and upload abstracts for secure review.</p>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <i class="fas fa-map-marked-alt step-icon"></i>
            <h5 class="fw-bold text-dark">3. Offline Showroom Tour</h5>
            <p class="text-muted">Visit our physical locations for a personalized guided experience.</p>
        </div>
    </div>
    </div>
</section>

<section class="bg-light py-5">
    <div class="container my-5">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <div class="section-kicker mb-2">Ready to explore</div>
                <h2 class="section-title text-dark fw-bold mb-0">Featured Active Projects</h2>
            </div>
            <a href="<?php echo $catalog_link; ?>" class="btn btn-outline-dark">View All <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
        <?php
        if(isset($conn)) {
            $sql = "SELECT property_id, project_name, state, property_type, price FROM properties WHERE status = 'ACTIVE' ORDER BY property_id DESC LIMIT 3";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $formatted_price = number_format($row['price'], 2);
                    
                    $detail_link = $catalog_link === 'login.php' ? 'login.php' : (isset($_SESSION['role']) && $_SESSION['role'] === 'CUSTOMER' ? 'customer/property_detail.php?id='.$row['property_id'] : 'property_detail.php?id='.$row['property_id']);

                    echo '<div class="col-md-4">';
                    echo '<div class="card h-100 border-0 hover-card">';
                    echo '<div class="card-body p-4 d-flex flex-column">';
                    echo '<div><span class="badge bg-primary mb-3 px-3 py-2 shadow-sm">'. htmlspecialchars($row['property_type']). '</span></div>';
                    echo '<h5 class="card-title fw-bold text-warning mb-2">'. htmlspecialchars($row['project_name']). '</h5>';
                    echo '<p class="card-text text-muted mb-4"><i class="fas fa-map-marker-alt text-danger me-2"></i> '. htmlspecialchars($row['state']). '</p>';
                    echo '<div class="mt-auto">';
                    echo '<small class="text-muted fw-bold d-block mb-1" style="font-size: 0.75rem;">STARTING FROM</small>';
                    echo '<h4 class="text-success fw-bold mb-3">RM '. $formatted_price. '</h4>';
                    echo '<a href="'.$detail_link.'" class="btn btn-warning text-dark fw-bold w-100">View Details</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="col-12 text-center py-4"><p class="text-muted">No active projects currently listed.</p></div>';
            }
        }
        ?>
        </div>
    </div>
</section>

<script>
const showroomLocations = {
    "Johor": { city: "Johor Bahru", query: "Johor+Bahru" },
    "Kedah": { city: "Alor Setar", query: "Alor+Setar" },
    "Kelantan": { city: "Kota Bharu", query: "Kota+Bharu" },
    "Melaka": { city: "Melaka City", query: "Melaka+City" },
    "Negeri Sembilan": { city: "Seremban", query: "Seremban" },
    "Pahang": { city: "Kuantan", query: "Kuantan" },
    "Perak": { city: "Ipoh", query: "Ipoh" },
    "Perlis": { city: "Kangar", query: "Kangar" },
    "Penang": { city: "George Town", query: "George+Town+Penang" },
    "Sabah": { city: "Kota Kinabalu", query: "Kota+Kinabalu" },
    "Sarawak": { city: "Kuching", query: "Kuching" },
    "Selangor": { city: "Shah Alam", query: "Shah+Alam" },
    "Terengganu": { city: "Kuala Terengganu", query: "Kuala+Terengganu" },
    "WP Kuala Lumpur": { city: "Kuala Lumpur", query: "Kuala+Lumpur" },
    "WP Labuan": { city: "Labuan", query: "Labuan+Malaysia" },
    "WP Putrajaya": { city: "Putrajaya", query: "Putrajaya" }
};

document.getElementById('stateSelect').addEventListener('change', function() {
    const state = this.value;
    const locationData = showroomLocations[state];
    
    const cityLabel = document.getElementById('showroomCity');
    const mapContainer = document.getElementById('mapContainer');

    if (locationData) {
        cityLabel.innerHTML = `Located in the capital: <strong class="text-dark">${locationData.city}</strong>`;
        
        mapContainer.innerHTML = `
            <iframe 
                width="100%" 
                height="100%" 
                style="border:0;" 
                loading="lazy" 
                allowfullscreen 
                referrerpolicy="no-referrer-when-downgrade" 
                src="https://www.google.com/maps?q=${locationData.query},+Malaysia&output=embed">
            </iframe>
        `;
    }
});
</script>

<?php include 'includes/footer.php';?>
