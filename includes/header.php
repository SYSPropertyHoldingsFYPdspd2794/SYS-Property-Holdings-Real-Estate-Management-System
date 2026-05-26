<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/db_connect.php';

$current_folder = basename(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])));
$root_prefix = in_array($current_folder, ['admin', 'customer', 'staff'], true) ? '../' : '';

$dashboard_link = $root_prefix . 'login.php';
$profile_link = $root_prefix . 'login.php';
$wishlist_link = $root_prefix . 'login.php';
$wishlist_text = 'Wishlist';
$wishlist_icon = 'fas fa-heart';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'ADMIN') {
        $dashboard_link = $root_prefix . 'admin/dashboard.php';
        $profile_link = $root_prefix . 'admin/profile.php';
        $wishlist_link = $root_prefix . 'admin/interest_timeline.php'; 
        $wishlist_text = 'Interest Timeline';
        $wishlist_icon = 'fas fa-chart-line';
    } elseif ($_SESSION['role'] === 'STAFF') {
        $dashboard_link = $root_prefix . 'staff/dashboard.php';
        $profile_link = $root_prefix . 'staff/profile.php';
        $wishlist_link = $root_prefix . 'staff/state_inventory.php';
        $wishlist_text = 'State Inventory';
        $wishlist_icon = 'fas fa-map-location-dot';
    } elseif ($_SESSION['role'] === 'CUSTOMER') {
        $dashboard_link = $root_prefix . 'customer/dashboard.php';
        $profile_link = $root_prefix . 'customer/profile.php';
        $wishlist_link = $root_prefix . 'customer/wishlist.php';
        $wishlist_text = 'Wishlist';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SYS Property Holdings</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --luxury-bg: #05060a;
    --luxury-surface: rgba(255,255,255,0.08);
    --luxury-border: rgba(255,255,255,0.12);
    --luxury-gold: #c5a059;
    --luxury-white: #f4f1e8;
    --luxury-muted: rgba(236,233,225,0.78);
}
* {
    box-sizing: border-box;
}
html {
    scroll-behavior: smooth;
}
body {
    margin: 0;
    min-height: 100vh;
    background: radial-gradient(circle at top, rgba(197,160,89,0.06), transparent 28%), linear-gradient(180deg, #08101c 0%, #05060a 100%);
    color: var(--luxury-white);
    font-family: 'Montserrat', sans-serif;
}
body a {
    transition: color 0.2s ease, transform 0.2s ease;
}
h1, h2, h3, h4, h5, h6 {
    font-family: 'Playfair Display', serif;
    letter-spacing: 0.03em;
}
.navbar {
    background: rgba(8, 10, 16, 0.96) !important;
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.navbar-brand {
    font-family: 'Playfair Display', serif;
    color: var(--luxury-gold) !important;
    text-transform: uppercase;
    letter-spacing: 0.18em;
    font-size: 1.05rem;
}
.navbar .nav-link {
    color: var(--luxury-muted) !important;
}
.navbar .nav-link:hover,
.navbar .nav-link.active {
    color: var(--luxury-white) !important;
}
.btn-primary {
    background: linear-gradient(135deg, #c5a059 0%, #d5b06d 100%) !important;
    border-color: transparent !important;
    box-shadow: 0 14px 30px rgba(197,160,89,0.25);
    color: #11151b !important;
    font-weight: 600;
}
.btn-primary:hover,
.btn-primary:focus {
    background: #d8b676 !important;
    border-color: transparent !important;
    color: #11151b !important;
}
.btn-outline-secondary {
    color: var(--luxury-white) !important;
    border-color: rgba(255,255,255,0.2) !important;
}
.btn-outline-secondary:hover {
    color: var(--luxury-gold) !important;
    border-color: var(--luxury-gold) !important;
}
.bg-info.text-white,
.badge.bg-info.text-white,
.btn-info.text-white,
.bg-warning.text-white,
.badge.bg-warning.text-white,
.btn-warning.text-white {
    color: #11151b !important;
}
.bg-light,
.bg-white,
.card:not(.bg-dark):not(.estimator-card),
.list-group-item,
.dropdown-menu,
.modal-content:not(.bg-dark),
.form-control,
.form-select {
    color: #212529;
}
.bg-light .lead:not([class*="text-"]),
.bg-white .lead:not([class*="text-"]),
.card:not(.bg-dark):not(.estimator-card) .lead:not([class*="text-"]) {
    color: #343a40;
}
.bg-light h1:not([class*="text-"]),
.bg-light h2:not([class*="text-"]),
.bg-light h3:not([class*="text-"]),
.bg-light h4:not([class*="text-"]),
.bg-light h5:not([class*="text-"]),
.bg-light h6:not([class*="text-"]),
.bg-white h1:not([class*="text-"]),
.bg-white h2:not([class*="text-"]),
.bg-white h3:not([class*="text-"]),
.bg-white h4:not([class*="text-"]),
.bg-white h5:not([class*="text-"]),
.bg-white h6:not([class*="text-"]) {
    color: #11151b;
}
footer.luxury-footer {
    background: #07080d !important;
    color: var(--luxury-muted) !important;
}
footer.luxury-footer .text-muted,
footer.luxury-footer a.text-muted,
footer.luxury-footer .text-secondary {
    color: rgba(244,241,232,0.82) !important;
}
footer.luxury-footer a {
    color: var(--luxury-muted) !important;
}
footer.luxury-footer a:hover {
    color: var(--luxury-white) !important;
}
footer.luxury-footer .social-links a {
    border-color: rgba(255,255,255,0.14) !important;
}
.hero-banner {
    background: linear-gradient(rgba(0,0,0,0.56), rgba(0,0,0,0.56)), url('https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80') center/cover no-repeat;
    color: white;
    padding: 150px 0;
}
.horizontal-scroll { display: flex; overflow-x: auto; gap: 1.5rem; padding-bottom: 1.5rem; scroll-snap-type: x mandatory; scrollbar-width: thin; }
.scroll-card { flex: 0 0 320px; scroll-snap-align: start; border: none; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.2); transition: transform 0.3s ease, box-shadow 0.3s ease; }
.scroll-card:hover { transform: translateY(-5px); box-shadow: 0 14px 40px rgba(0,0,0,0.22); }
.step-icon { font-size: 3rem; color: var(--luxury-gold); margin-bottom: 1rem; }
.gov-housing-section { background-color: rgba(255,255,255,0.04); border-left: 5px solid var(--luxury-gold); }
.password-toggle-btn { min-width: 44px; }
.password-toggle-btn i { color: #6c757d; }
.password-toggle-btn:focus { box-shadow: 0 0 0 .25rem rgba(197,160,89,.24); }
.luxury-title { font-family: 'Playfair Display', serif; letter-spacing: 0.08em; }
.tracking-wider { letter-spacing: 0.1em; }
.tracking-widest { letter-spacing: 0.22em; }
.text-gold { color: var(--luxury-gold) !important; }
.bg-gold { background-color: var(--luxury-gold) !important; }
.filter-glass {
    background: rgba(255,255,255,0.08) !important;
    backdrop-filter: blur(18px);
    border: 1px solid rgba(255,255,255,0.12) !important;
    box-shadow: 0 24px 60px rgba(0,0,0,0.18) !important;
}
.hover-card {
    transition: all 0.45s cubic-bezier(0.165, 0.84, 0.44, 1);
    border: 1px solid rgba(255,255,255,0.08) !important;
    box-shadow: 0 18px 45px rgba(0,0,0,0.16) !important;
    background: rgba(255,255,255,0.02) !important;
}
.bg-light .hover-card,
.bg-white .hover-card,
.hover-card.bg-white {
    background: #ffffff !important;
    color: #212529;
}
.hover-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 26px 70px rgba(0,0,0,0.24) !important;
}
.hover-card:hover .image-zoom { transform: scale(1.05); }
.image-zoom { transition: transform 0.8s ease; }
.image-overlay { position: absolute; bottom: 0; left: 0; width: 100%; height: 55%; background: linear-gradient(to top, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0) 100%); z-index: 1; }
.premium-badge { letter-spacing: 1px; font-weight: 500; font-size: 0.65rem; padding: 0.6em 1.2em; border-radius: 0; border: 1px solid rgba(255,255,255,0.22); background: rgba(0,0,0,0.35); color: var(--luxury-white) !important; }
.luxury-wishlist-btn { width: 45px; height: 45px; transition: all 0.3s ease; }
.luxury-wishlist-btn:hover { background-color: rgba(197,160,89,0.94) !important; transform: scale(1.1); }

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
.bg-dark .text-muted,
.bg-black .text-muted,
.text-bg-dark .text-muted,
.receipt-card .text-muted,
.filter-glass .text-muted,
.gov-housing-section .text-muted,
.bg-dark small.text-muted,
.bg-black small.text-muted,
.receipt-card small.text-muted,
.filter-glass label {
    color: rgba(248,250,252,0.78) !important;
}
.bg-dark .text-dark:not(.btn):not(.badge):not(.alert),
.bg-black .text-dark:not(.btn):not(.badge):not(.alert),
.text-bg-dark .text-dark:not(.btn):not(.badge):not(.alert),
.receipt-card .text-dark:not(.btn):not(.badge):not(.alert) {
    color: #f8fafc !important;
}
.bg-light .text-light:not(.btn):not(.badge):not(.alert),
.bg-white .text-light:not(.btn):not(.badge):not(.alert),
.card:not(.bg-dark):not(.bg-black):not(.receipt-card):not(.estimator-card) .text-light:not(.btn):not(.badge):not(.alert),
.modal-content:not(.bg-dark):not(.bg-black) .text-light:not(.btn):not(.badge):not(.alert) {
    color: #1f2937 !important;
}
.bg-light .text-white:not(.btn):not(.badge):not(.alert),
.bg-white .text-white:not(.btn):not(.badge):not(.alert),
.card:not(.bg-dark):not(.bg-black):not(.receipt-card):not(.estimator-card) .text-white:not(.btn):not(.badge):not(.alert),
.modal-content:not(.bg-dark):not(.bg-black) .text-white:not(.btn):not(.badge):not(.alert) {
    color: #111827 !important;
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
        padding: 1rem 0 0.5rem;
    }
    .navbar .nav-link {
        padding: 0.75rem 0.85rem !important;
    }
    .navbar .d-flex {
        align-items: stretch;
        flex-direction: column;
        gap: 0.65rem;
        padding-top: 0.75rem;
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
}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
<div class="container">
<a class="navbar-brand fw-bold" href="<?php echo htmlspecialchars($root_prefix . 'index.php'); ?>">SYS Property</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav me-auto">
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($root_prefix . 'index.php'); ?>">Home</a></li>
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($root_prefix . 'government_housing.php'); ?>">Government Housing</a></li>
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($root_prefix . 'showrooms.php'); ?>">Showrooms</a></li>
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($root_prefix . 'buying_journey.php'); ?>"><i class="fas fa-map-signs me-1"></i>Buying Journey</a></li>
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($root_prefix . 'financial_planner.php'); ?>"><i class="fas fa-calculator me-1"></i>Financial Planner</a></li>
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($root_prefix . 'bank_rates.php'); ?>"><i class="fas fa-university me-1"></i>Bank Rates</a></li>
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($wishlist_link); ?>"><i class="<?php echo htmlspecialchars($wishlist_icon); ?> me-1"></i><?php echo htmlspecialchars($wishlist_text); ?></a></li>
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($root_prefix . 'about_us.php'); ?>"><i class="fas fa-info-circle me-1"></i>About Us</a></li>
</ul>
<div class="d-flex">
<?php if (isset($_SESSION['account_id'])):?>
<div class="dropdown me-2">
<button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">My Account</button>
<ul class="dropdown-menu dropdown-menu-end">
<li><a class="dropdown-item" href="<?php echo htmlspecialchars($dashboard_link); ?>">Dashboard</a></li>
<li><a class="dropdown-item" href="<?php echo htmlspecialchars($profile_link); ?>">Profile</a></li>
</ul>
</div>
<a href="<?php echo htmlspecialchars($root_prefix . 'logout.php'); ?>" class="btn btn-danger">Logout</a>
<?php else:?>
<a href="<?php echo htmlspecialchars($root_prefix . 'login.php'); ?>" class="btn btn-primary shadow-sm">Sign In</a>
<?php endif;?>
</div>
</div>
</div>
</nav>
