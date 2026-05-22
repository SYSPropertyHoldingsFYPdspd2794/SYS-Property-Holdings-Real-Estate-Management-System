<?php
/**
 * PROJECT: SYS Property Holdings
 * FILE: showrooms.php (ROOT DIRECTORY)
 * DESCRIPTION: Details offline services using left-right alternating layouts and Map logic.
 * FIX: Corrected Google Maps iframe URL syntax.
 */
include_once 'includes/header.php';
$imgBase = $root_prefix . "SYS Property Catalog/";
?>

<div class="container-fluid bg-dark text-white py-5 text-center shadow-sm">
    <h1 class="display-4 fw-bold mb-3 mt-3">Locate Our Showrooms</h1>
    <p class="lead w-50 mx-auto opacity-75 mb-4">Bridging the digital divide. Explore properties online, but finalize your purchase securely at our physical branches nationwide.</p>
    
    <div class="d-flex justify-content-center pb-4">
        <select id="mapStateSelect" class="form-select form-select-lg w-auto text-center shadow fw-bold border-0" style="min-width: 350px;">
            <option value="" selected disabled>-- Locate Your Nearest Branch --</option>
            <optgroup label="States">
                <option value="Johor Bahru">Johor</option>
                <option value="Alor Setar">Kedah</option>
                <option value="Kota Bharu">Kelantan</option>
                <option value="Melaka City">Melaka</option>
                <option value="Seremban">Negeri Sembilan</option>
                <option value="Kuantan">Pahang</option>
                <option value="Ipoh">Perak</option>
                <option value="Kangar">Perlis</option>
                <option value="George Town Penang">Penang</option>
                <option value="Kota Kinabalu">Sabah</option>
                <option value="Kuching">Sarawak</option>
                <option value="Shah Alam">Selangor</option>
                <option value="Kuala Terengganu">Terengganu</option>
            </optgroup>
            <optgroup label="Federal Territories">
                <option value="Kuala Lumpur">WP Kuala Lumpur</option>
                <option value="Labuan">WP Labuan</option>
                <option value="Putrajaya">WP Putrajaya</option>
            </optgroup>
        </select>
    </div>
</div>

<div class="container mt-n5 position-relative z-1 mb-5">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
        <div class="card-body p-0" style="height: 450px;" id="showroomMapFrame">
            <div class="d-flex h-100 flex-column justify-content-center align-items-center text-muted bg-light">
                <i class="fas fa-map-marked-alt fa-4x mb-3 opacity-25"></i>
                <h5>Select a region above to load our showroom location</h5>
            </div>
        </div>
    </div>
</div>

<div class="container my-5 py-4">
    <div class="text-center mb-5 pb-4">
        <h2 class="fw-bold text-white">The O2O Showroom Experience</h2>
        <p class="text-light">No online payments. No hidden gateways. 100% secure offline commitments.</p>
    </div>

    <div class="row align-items-center mb-5 pb-4">
        <div class="col-md-6 mb-4 mb-md-0">
            <img src="<?php echo $imgBase; ?>Office.jpg" class="img-fluid rounded-4 shadow" alt="Our Offline Office">
        </div>
        <div class="col-md-6 px-lg-5">
            <h3 class="fw-bold text-gold mb-3">Welcome to Our Showrooms</h3>
            <p class="fs-5 text-light">Step into our state-of-the-art physical branches. While our online platform allows you to browse and calculate loans seamlessly, our doors are always open for you to explore architectural models and finalize material selections in person.</p>
        </div>
    </div>

    <div class="row align-items-center mb-5 pb-4 flex-md-row-reverse">
        <div class="col-md-6 mb-4 mb-md-0">
            <img src="<?php echo $imgBase; ?>Service_Staff.jpg" class="img-fluid rounded-4 shadow" alt="Professional Staff">
        </div>
        <div class="col-md-6 px-lg-5 text-md-end">
            <h3 class="fw-bold text-gold mb-3">Professional Consultation</h3>
            <p class="fs-5 text-light">Our dedicated service staff are rigorously trained to provide exemplary hospitality. During your scheduled offline visit, our consultants will guide you through tailored financial solutions, ensuring you make the most informed decision.</p>
        </div>
    </div>

    <div class="row align-items-center mb-5 pb-4">
        <div class="col-md-6 mb-4 mb-md-0">
            <img src="<?php echo $imgBase; ?>Standard_Service.jpg" class="img-fluid rounded-4 shadow" alt="Transparent Standards">
        </div>
        <div class="col-md-6 px-lg-5">
            <h3 class="fw-bold text-gold mb-3">Transparent Processes</h3>
            <p class="fs-5 text-light">We pride ourselves on absolute transparency. What you see on our digital catalog is exactly what you get. Our standard service protocol guarantees no hidden surcharges—every property detail and purchasing flow is explained clearly offline.</p>
        </div>
    </div>

    <div class="row align-items-center mb-5 flex-md-row-reverse">
        <div class="col-md-6 mb-4 mb-md-0">
            <img src="<?php echo $imgBase; ?>Offline_Service.jpg" class="img-fluid rounded-4 shadow" alt="Secure Offline Services">
        </div>
        <div class="col-md-6 px-lg-5 text-md-end">
            <h3 class="fw-bold text-gold mb-3">Secure Offline Transactions</h3>
            <p class="fs-5 text-light">Your security is our priority. We strictly prohibit online deposits or financial transactions through our portal. All sensitive matters—including downpayments, legal contract signing, and the official handover of keys—are conducted face-to-face.</p>
        </div>
    </div>
</div>

<script>
document.getElementById('mapStateSelect').addEventListener('change', function() {
    const query = this.value.replace(/ /g, '+');
    const mapBox = document.getElementById('showroomMapFrame');
    
    // FIX: Using correct string interpolation ${query} and reliable maps URL
    mapBox.innerHTML = `
        <iframe 
            width="100%" 
            height="100%" 
            style="border:0;" 
            loading="lazy" 
            allowfullscreen 
            referrerpolicy="no-referrer-when-downgrade" 
            src="https://maps.google.com/maps?q=${query},+Malaysia&t=&z=13&ie=UTF8&iwloc=&output=embed">
        </iframe>
    `;
});
</script>

<?php include_once 'includes/footer.php'; ?>
