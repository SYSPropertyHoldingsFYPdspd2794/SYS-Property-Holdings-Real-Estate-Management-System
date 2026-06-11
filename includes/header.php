<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/db_connect.php';

// Set PHP and MySQL to Malaysian time (GMT+8).
date_default_timezone_set('Asia/Kuala_Lumpur');
if (isset($conn)) {
    $conn->query("SET time_zone = '+08:00'");
}

$current_folder = basename(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])));
$root_prefix = in_array($current_folder, ['admin', 'customer', 'staff'], true) ? '../' : '';

$dashboard_link = $root_prefix . 'login.php';
$profile_link = $root_prefix . 'login.php';
$profile_text = 'Profile';
$wishlist_link = $root_prefix . 'login.php';
$wishlist_text = 'Wishlist';
$wishlist_icon = 'fas fa-heart';
$catalog_nav_link = $root_prefix . 'login.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'ADMIN') {
        $dashboard_link = $root_prefix . 'admin/dashboard.php';
        $profile_link = $root_prefix . 'admin/profile.php';
        $profile_text = 'Setting';
        $wishlist_link = $root_prefix . 'admin/interest_timeline.php'; 
        $wishlist_text = 'Interest Timeline';
        $wishlist_icon = 'fas fa-chart-line';
        $catalog_nav_link = $root_prefix . 'properties.php';
    } elseif ($_SESSION['role'] === 'STAFF') {
        $dashboard_link = $root_prefix . 'staff/dashboard.php';
        $profile_link = $root_prefix . 'staff/profile.php';
        $profile_text = 'Setting';
        $wishlist_link = $root_prefix . 'staff/state_inventory.php';
        $wishlist_text = 'State Inventory';
        $wishlist_icon = 'fas fa-map-location-dot';
        $catalog_nav_link = $root_prefix . 'properties.php';
    } elseif ($_SESSION['role'] === 'CUSTOMER') {
        $dashboard_link = $root_prefix . 'customer/dashboard.php';
        $profile_link = $root_prefix . 'customer/profile.php';
        $profile_text = 'Setting';
        $wishlist_link = $root_prefix . 'customer/wishlist.php';
        $wishlist_text = 'Wishlist';
        $catalog_nav_link = $root_prefix . 'customer/properties.php';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/jpeg" href="<?php echo htmlspecialchars($root_prefix . 'SYS Property Catalog/SYS_Property_Holdings_Icon.jpeg'); ?>">
<link rel="apple-touch-icon" href="<?php echo htmlspecialchars($root_prefix . 'SYS Property Catalog/SYS_Property_Holdings_Icon.jpeg'); ?>">
<?php 
    // Check if the current page belongs to private administrative or client directories
    if (isset($current_folder) && in_array($current_folder, ['admin', 'customer', 'staff'], true)): 
    ?>
        <meta name="robots" content="noindex, nofollow">
    <?php else: ?>
        <meta name="robots" content="index, follow">
    <?php endif; ?>

<?php
// Get the filename of the currently running webpage.
$current_page = basename($_SERVER['PHP_SELF']);

// 1. SEO settings for the default homepage (index.php)
$seo_title = "SYS Property Holdings | Real Estate Management System";
$seo_desc = "Welcome to SYS Property Holdings Real Estate Management System. Explore verified homes, compare financing, and book physical showroom visits across Malaysia from one secure property platform.";

// 2. Automatically switch and change the title to the appropriate professional search engine title based on the different pages.
if ($current_page == 'about_us.php') {
    $seo_title = "About Us | SYS Property Holdings";
    $seo_desc = "Learn more about SYS Property Holdings. Discover our vision, mission, and the professional team behind Malaysia's leading real estate O2O tech evolution.";
} elseif ($current_page == 'government_housing.php') {
    $seo_title = "Government Housing Schemes | Affordable Homes Malaysia";
    $seo_desc = "Explore regional state housing programs and affordable housing schemes with equal allocation rights via SYS Property Holdings.";
} elseif ($current_page == 'showrooms.php') {
    $seo_title = "Book Physical Showroom Visits | Malaysia Real Estate";
    $seo_desc = "Select your state, view live map locations, and seamlessly book your physical showroom appointments online.";
} elseif ($current_page == 'financial_planner.php') {
    $seo_title = "Property Financial Planner & Loan Calculator";
    $seo_desc = "Calculate your property loan eligibility, estimate monthly installments, and structure your housing budget accurately.";
} elseif ($current_page == 'bank_rates.php') {
    $seo_title = "Latest Bank Housing Interest Rates | Malaysia";
    $seo_desc = "Compare up-to-date home loan interest rates across major Malaysian banks to optimize your property financing.";
} elseif ($current_page == 'login.php') {
    $seo_title = "Portal Login | SYS Property Holdings";
    $seo_desc = "Sign in to your SYS Property Holdings account to manage your property applications, wishlists, and showroom bookings.";
}
?>

<title><?php echo $seo_title; ?></title>
<meta name="description" content="<?php echo $seo_desc; ?>">
<meta name="keywords" content="SYS Property Holdings, Real Estate Management System, UTM SPACE, Malaysia Property, O2O Real Estate, Affordable Housing, Bank Rates Malaysia">
<?php 
// 1. Get the current page file name
$current_page = basename($_SERVER['PHP_SELF']);

// 2. Check if it belongs to a private management folder or is a sensitive authentication page.
$is_private_folder = isset($current_folder) && in_array($current_folder, ['admin', 'customer', 'staff'], true);
$is_sensitive_page = in_array($current_page, ['login.php', 'reset_action.php'], true);

if ($is_private_folder || $is_sensitive_page): 
?>
    <meta name="robots" content="noindex, nofollow, noarchive">
<?php else: ?>
    <meta name="robots" content="index, follow">
<?php endif; ?>

<meta property="og:type" content="website">
<meta property="og:url" content="https://syspropertyholdings.infinityfreeapp.com/">
<meta property="og:title" content="<?php echo $seo_title; ?>">
<meta property="og:description" content="<?php echo $seo_desc; ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* Unified product-style refresh */
:root {
    --luxury-bg: #08111f;
    --luxury-ink: #101827;
    --luxury-gold: #d7a84e;
    --luxury-blue: #2d6cdf;
    --luxury-teal: #10a6a0;
    --luxury-white: #fbfaf7;
    --luxury-muted: rgba(238,242,247,0.76);
    --surface: #ffffff;
    --surface-soft: #f6f8fb;
    --text-muted: #64748b;
    --radius: 18px;
    --shadow-soft: 0 18px 48px rgba(15, 23, 42, 0.12);
    --shadow-lift: 0 28px 70px rgba(15, 23, 42, 0.18);
}
body {
    background:
        radial-gradient(circle at 18% 0%, rgba(215,168,78,0.12), transparent 26rem),
        radial-gradient(circle at 90% 8%, rgba(16,166,160,0.12), transparent 28rem),
        linear-gradient(180deg, #0b1322 0%, #08111f 42%, #0a1220 100%);
    color: var(--luxury-white);
    font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    line-height: 1.6;
    text-rendering: optimizeLegibility;
}
body a {
    transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}
h1, h2, h3, h4, h5, h6 {
    font-family: 'Inter', system-ui, sans-serif;
    letter-spacing: 0;
    line-height: 1.12;
}
.display-1, .display-2, .display-3, .display-4, .luxury-title {
    font-family: 'Playfair Display', Georgia, serif;
}
.container {
    width: min(100% - 2rem, 1180px);
}
.navbar {
    background: rgba(8, 17, 31, 0.88) !important;
    backdrop-filter: blur(18px);
    border-bottom: 1px solid rgba(255,255,255,0.10);
    box-shadow: 0 14px 40px rgba(0,0,0,0.18) !important;
}
.navbar .container {
    width: min(100% - 1.5rem, 1320px);
}
.navbar-brand {
    font-family: 'Inter', system-ui, sans-serif;
    letter-spacing: 0.12em;
    font-size: 0.95rem;
    font-weight: 800;
}
.navbar .nav-link {
    border-radius: 999px;
    font-size: 0.88rem;
    font-weight: 600;
    padding: 0.62rem 0.72rem !important;
    white-space: nowrap;
    color: var(--luxury-white) !important;
}
.navbar .nav-link:hover,
.navbar .nav-link.active {
    background: rgba(255,255,255,0.08);
}
.navbar-toggler {
    border: 1px solid rgba(255,255,255,0.14);
    border-radius: 12px;
    padding: 0.45rem 0.65rem;
}
.dropdown-menu {
    border: 0;
    border-radius: 14px;
    box-shadow: var(--shadow-soft);
    padding: 0.5rem;
}
.dropdown-item {
    border-radius: 10px;
    font-weight: 600;
    padding: 0.65rem 0.8rem;
}
.btn {
    border-radius: 12px;
    font-weight: 700;
    letter-spacing: 0;
    transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
}
.btn:hover {
    transform: translateY(-1px);
}
.btn-primary {
    background: linear-gradient(135deg, #d7a84e 0%, #f0c66e 100%) !important;
    box-shadow: 0 16px 34px rgba(215,168,78,0.24);
    color: #0f172a !important;
}
.btn-primary:hover,
.btn-primary:focus {
    background: #f0c66e !important;
    color: #0f172a !important;
    box-shadow: 0 20px 42px rgba(215,168,78,0.30);
}
.btn-dark {
    background: #101827 !important;
    border-color: #101827 !important;
}
.btn-outline-dark,
.btn-outline-secondary,
.btn-outline-light {
    border-width: 1px;
    font-weight: 700;
}
.bg-light {
    background-color: var(--surface-soft) !important;
}
.bg-white {
    background-color: var(--surface) !important;
}
.text-muted {
    color: var(--text-muted) !important;
}
.card {
    border: 1px solid rgba(15,23,42,0.08);
    border-radius: var(--radius);
    box-shadow: var(--shadow-soft);
}
.card-img-top {
    aspect-ratio: 16 / 10;
    object-fit: cover;
}
.form-control,
.form-select {
    border-color: #d9e1ea;
    border-radius: 12px;
    min-height: 46px;
}
.form-control:focus,
.form-select:focus {
    border-color: var(--luxury-blue);
    box-shadow: 0 0 0 0.25rem rgba(45,108,223,0.14);
}
.section-kicker {
    color: var(--luxury-gold);
    font-size: 0.76rem;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}
.section-title {
    font-size: clamp(2rem, 4vw, 3.25rem);
    font-weight: 800;
}
.section-copy {
    color: var(--luxury-muted);
    font-size: clamp(1rem, 1.6vw, 1.15rem);
    max-width: 720px;
}
.auth-card {
    overflow: hidden;
}
.auth-card::before {
    content: "";
    display: block;
    height: 6px;
    background: linear-gradient(90deg, var(--luxury-gold), var(--luxury-teal), var(--luxury-blue));
}
footer.luxury-footer {
    background: #07101d !important;
}
.footer-hours-line {
    font-size: 0.78rem;
    white-space: nowrap;
}
.hero-banner {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    background: linear-gradient(105deg, rgba(8,17,31,0.95) 0%, rgba(8,17,31,0.74) 45%, rgba(8,17,31,0.34) 100%), url('https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80') center/cover no-repeat;
    padding: clamp(6rem, 12vw, 9rem) 0 clamp(4rem, 9vw, 6rem);
}
.hero-banner::after {
    content: "";
    position: absolute;
    inset: auto 0 0;
    height: 120px;
    background: linear-gradient(180deg, transparent, rgba(8,17,31,0.96));
    z-index: -1;
}
.hero-banner .lead {
    color: rgba(255,255,255,0.82);
    max-width: 760px;
}
.horizontal-scroll {
    display: grid;
    grid-auto-flow: column;
    grid-auto-columns: minmax(270px, 1fr);
    overflow-x: auto;
    gap: 1.25rem;
    padding: 0.25rem 0.25rem 1.5rem;
    scroll-snap-type: x mandatory;
    scrollbar-width: thin;
}
.scroll-card {
    scroll-snap-align: start;
    border: 0;
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow-soft);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.scroll-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-lift);
}
.step-icon {
    width: 68px;
    height: 68px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 18px;
    background: rgba(215,168,78,0.13);
    color: var(--luxury-gold);
    font-size: 1.8rem;
    margin-bottom: 1rem;
}
.gov-housing-section {
    background:
        linear-gradient(135deg, rgba(16,166,160,0.14), rgba(215,168,78,0.10)),
        rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.12);
    border-left: 5px solid var(--luxury-gold);
    box-shadow: 0 28px 70px rgba(0,0,0,0.18);
}
.hover-card,
.hover-bank-card,
.hover-lift {
    transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
    box-shadow: var(--shadow-soft) !important;
}
.hover-card:hover,
.hover-bank-card:hover,
.hover-lift:hover {
    transform: translateY(-7px);
    border-color: rgba(215,168,78,0.36) !important;
    box-shadow: var(--shadow-lift) !important;
}
@media (max-width: 1199.98px) {
    .navbar .nav-link {
        font-size: 0.84rem;
        padding-inline: 0.55rem !important;
    }
}
@media (max-width: 991.98px) {
    .navbar-collapse {
        padding: 0.85rem 0 0.4rem;
    }
    .navbar .navbar-nav {
        flex-direction: column !important;
        align-items: stretch;
        gap: 0.25rem !important;
    }
    .navbar .nav-link {
        width: 100%;
        padding: 0.72rem 0.85rem !important;
    }
    .navbar .container > .d-flex:first-child {
        flex-wrap: wrap;
        gap: 0.65rem;
    }
    .navbar-account-actions {
        gap: 0.45rem !important;
    }
    .navbar-account-actions .btn {
        font-size: 0.82rem;
        padding: 0.5rem 0.65rem;
    }
    .navbar-toggler {
        margin-left: auto;
    }
}
@media (max-width: 767.98px) {
    .hero-banner {
        text-align: left !important;
        padding-top: 5rem;
    }
    .horizontal-scroll {
        grid-auto-columns: minmax(245px, 84vw);
    }
    .container {
        width: min(100% - 1.25rem, 1180px);
    }
    .display-2,
    .display-3 {
        font-size: 2.75rem;
    }
    .card-body.p-5 {
        padding: 1.5rem !important;
    }
    .footer-hours-line {
        white-space: normal;
    }
}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm py-2">
<div class="container flex-column align-items-stretch gap-2">
<div class="d-flex w-100 justify-content-between align-items-center">
<a class="navbar-brand d-flex align-items-center gap-2 fw-bold m-0 text-white" href="<?php echo htmlspecialchars($root_prefix . 'index.php'); ?>">
    <img src="<?php echo htmlspecialchars($root_prefix . 'SYS%20Property%20Catalog/SYS_Property_Holdings_Icon.jpeg'); ?>" alt="SYS Property Holdings Logo" style="height: 32px; width: auto; border-radius: 4px; object-fit: contain;">
    SYS Property
</a>
<div class="d-flex align-items-center gap-2 navbar-account-actions">
<?php if (isset($_SESSION['account_id'])):?>
<div class="dropdown">
<button class="btn btn-outline-light dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">My Account</button>
<ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
<li><a class="dropdown-item" href="<?php echo htmlspecialchars($dashboard_link); ?>">Dashboard</a></li>
<li><a class="dropdown-item" href="<?php echo htmlspecialchars($profile_link); ?>"><?php echo htmlspecialchars($profile_text); ?></a></li>
</ul>
</div>
<a href="<?php echo htmlspecialchars($root_prefix . 'logout.php'); ?>" class="btn btn-danger">Logout</a>
<?php else:?>
<a href="<?php echo htmlspecialchars($root_prefix . 'login.php'); ?>" class="btn btn-primary shadow-sm">Sign In</a>
<?php endif;?>
</div>
<button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>
</div>
<div class="collapse navbar-collapse w-100" id="navbarNav">
<ul class="navbar-nav flex-row flex-wrap justify-content-start gap-1 gap-lg-2">
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($catalog_nav_link); ?>"><i class="fas fa-building me-1"></i>Catalog</a></li>
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($root_prefix . 'government_housing.php'); ?>"><i class="fas fa-house-user me-1"></i>Government Housing</a></li>
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($root_prefix . 'showrooms.php'); ?>"><i class="fas fa-map-location-dot me-1"></i>Showrooms</a></li>
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($root_prefix . 'buying_journey.php'); ?>"><i class="fas fa-map-signs me-1"></i>Buying Journey</a></li>
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($root_prefix . 'financial_planner.php'); ?>"><i class="fas fa-calculator me-1"></i>Financial Planner</a></li>
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($root_prefix . 'bank_rates.php'); ?>"><i class="fas fa-university me-1"></i>Bank Rates</a></li>
<?php if (isset($_SESSION['account_id'])):?>
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($wishlist_link); ?>"><i class="<?php echo htmlspecialchars($wishlist_icon); ?> me-1"></i><?php echo htmlspecialchars($wishlist_text); ?></a></li>
<?php endif;?>
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($root_prefix . 'about_us.php'); ?>"><i class="fas fa-info-circle me-1"></i>About Us</a></li>
</ul>
</div>
</div>
</nav>
