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

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'ADMIN') {
        $dashboard_link = $root_prefix . 'admin/dashboard.php';
        $profile_link = $root_prefix . 'admin/profile.php';
        $wishlist_link = '#'; 
    } elseif ($_SESSION['role'] === 'STAFF') {
        $dashboard_link = $root_prefix . 'staff/dashboard.php';
        $profile_link = $root_prefix . 'staff/profile.php';
        $wishlist_link = '#';
    } elseif ($_SESSION['role'] === 'CUSTOMER') {
        $dashboard_link = $root_prefix . 'customer/dashboard.php';
        $profile_link = $root_prefix . 'customer/profile.php';
        $wishlist_link = $root_prefix . 'customer/wishlist.php';
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
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
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
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($root_prefix . 'bank_rates.php'); ?>"><i class="fas fa-university me-1"></i>Bank Rates</a></li>
    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($wishlist_link); ?>"><i class="fas fa-heart me-1"></i>Wishlist</a></li>
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
