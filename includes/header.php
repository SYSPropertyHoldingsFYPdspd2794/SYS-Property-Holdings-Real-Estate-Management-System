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
        $wishlist_link = '#'; // Wishlist is customer-only
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.horizontal-scroll { display: flex; overflow-x: auto; gap: 1.5rem; padding-bottom: 1.5rem; scroll-snap-type: x mandatory; scrollbar-width: thin; }
.scroll-card { flex: 0 0 320px; scroll-snap-align: start; border: none; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.3s; }
.scroll-card:hover { transform: translateY(-5px); }
.hero-banner { background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80') center/cover; color: white; padding: 150px 0; }
.step-icon { font-size: 3rem; color: #0d6efd; margin-bottom: 1rem; }
.gov-housing-section { background-color: #f8f9fa; border-left: 5px solid #0d6efd; }
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
    <li class="nav-item"><a class="nav-link" href="#">Government Housing</a></li>
    <li class="nav-item"><a class="nav-link" href="#">Showrooms</a></li>
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