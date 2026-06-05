<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn);

// Handle month/year filtering
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$report_type = isset($_GET['report']) ? $_GET['report'] : 'engagement';
$auto_print = isset($_GET['print']) && $_GET['print'] == '1';

// Determine which reports to render
$reports_to_run = ($report_type === 'all') ? ['engagement', 'demographics', 'operations', 'staff', 'market', 'pricing', 'affordable'] : [$report_type];

// Helper function to build date conditions
function getDateCondition($dateColumn, $month, $year) {
    return "MONTH($dateColumn) = $month AND YEAR($dateColumn) = $year";
}

$metrics = [];
$charts = [];

// Base counts for cards
$res_reg = $conn->query("SELECT COUNT(*) as count FROM accounts WHERE role = 'CUSTOMER' AND " . getDateCondition('created_at', $selected_month, $selected_year));
$metrics['new_registrations'] = $res_reg->fetch_assoc()['count'];

$res_wish = $conn->query("SELECT COUNT(*) as count FROM wishlists WHERE " . getDateCondition('created_at', $selected_month, $selected_year));
$metrics['wishlist_adds'] = $res_wish->fetch_assoc()['count'];

$res_appt = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE " . getDateCondition('appointment_date', $selected_month, $selected_year));
$metrics['total_appointments'] = $res_appt->fetch_assoc()['count'];

// --- PROCESS ENGAGEMENT ---
if (in_array('engagement', $reports_to_run)) {
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $selected_month, $selected_year);
    $days_labels = range(1, $days_in_month);
    
    $reg_data = array_fill(1, $days_in_month, 0);
    $res = $conn->query("SELECT DAY(created_at) as d, COUNT(*) as count FROM accounts WHERE role = 'CUSTOMER' AND " . getDateCondition('created_at', $selected_month, $selected_year) . " GROUP BY d");
    while ($row = $res->fetch_assoc()) { $reg_data[$row['d']] = (int)$row['count']; }
    
    $wish_data = array_fill(1, $days_in_month, 0);
    $res = $conn->query("SELECT DAY(created_at) as d, COUNT(*) as count FROM wishlists WHERE " . getDateCondition('created_at', $selected_month, $selected_year) . " GROUP BY d");
    while ($row = $res->fetch_assoc()) { $wish_data[$row['d']] = (int)$row['count']; }

    $charts['daily_trends'] = [
        'labels' => $days_labels,
        'datasets' => [
            [
                'label' => 'New Registrations', 
                'data' => array_values($reg_data), 
                'borderColor' => '#0d6efd', 
                'backgroundColor' => 'rgba(13,110,253,0.1)', 
                'fill' => true
            ],
            [
                'label' => 'Wishlist Additions', 
                'data' => array_values($wish_data), 
                'borderColor' => '#198754', 
                'backgroundColor' => 'rgba(25,135,84,0.1)', 
                'fill' => true
            ]
        ]
    ];
    
    // Fetch detailed data for table
    $res = $conn->query("SELECT a.email, a.created_at, c.full_name FROM accounts a LEFT JOIN customers c ON a.account_id = c.customer_id WHERE a.role = 'CUSTOMER' AND " . getDateCondition('a.created_at', $selected_month, $selected_year) . " ORDER BY a.created_at DESC LIMIT 20");
    $metrics['recent_users'] = [];
    while($row = $res->fetch_assoc()) { $metrics['recent_users'][] = $row; }
}

// --- PROCESS DEMOGRAPHICS ---
if (in_array('demographics', $reports_to_run)) {
    $active_customers_query = "
        SELECT DISTINCT c.customer_id, c.full_name, c.monthly_income, c.marital_status, c.dependents_count, c.occupation
        FROM customers c
        LEFT JOIN wishlists w ON c.customer_id = w.customer_id AND " . getDateCondition('w.created_at', $selected_month, $selected_year) . "
        LEFT JOIN appointments ap ON c.customer_id = ap.customer_id AND " . getDateCondition('ap.appointment_date', $selected_month, $selected_year) . "
        LEFT JOIN affordable_housing_applications aha ON c.customer_id = aha.customer_id AND " . getDateCondition('aha.application_date', $selected_month, $selected_year) . "
        WHERE w.wishlist_id IS NOT NULL OR ap.appointment_id IS NOT NULL OR aha.application_id IS NOT NULL
    ";
    
    $res = $conn->query($active_customers_query);
    $total_income = 0; $active_count = 0;
    $marital_stats = ['SINGLE' => 0, 'MARRIED' => 0];
    $total_dependents = 0;
    $income_brackets = ['< RM3,000' => 0, 'RM3,000 - RM6,000' => 0, 'RM6,001 - RM10,000' => 0, '> RM10,000' => 0];
    $occupations = [];
    $demo_list = [];

    while ($row = $res->fetch_assoc()) {
        $active_count++;
        $income = (float)$row['monthly_income'];
        $total_income += $income;
        if (isset($marital_stats[$row['marital_status']])) { $marital_stats[$row['marital_status']]++; }
        $total_dependents += (int)$row['dependents_count'];
        
        if ($income < 3000) { $income_brackets['< RM3,000']++; }
        elseif ($income <= 6000) { $income_brackets['RM3,000 - RM6,000']++; }
        elseif ($income <= 10000) { $income_brackets['RM6,001 - RM10,000']++; }
        else { $income_brackets['> RM10,000']++; }

        $occ = ucwords(strtolower(trim($row['occupation'])));
        if (!empty($occ)) {
            if(!isset($occupations[$occ])) $occupations[$occ] = 0;
            $occupations[$occ]++;
        }
        
        if(count($demo_list) < 20) {
            $demo_list[] = $row;
        }
    }
    arsort($occupations);

    $metrics['avg_income'] = $active_count > 0 ? $total_income / $active_count : 0;
    $metrics['avg_dependents'] = $active_count > 0 ? $total_dependents / $active_count : 0;
    $metrics['top_occupations'] = array_slice($occupations, 0, 5);
    $metrics['demo_list'] = $demo_list;
    
    $charts['marital_status'] = ['labels' => array_keys($marital_stats), 'data' => array_values($marital_stats)];
    $charts['income_brackets'] = ['labels' => array_keys($income_brackets), 'data' => array_values($income_brackets)];
}

// --- PROCESS OPERATIONS ---
if (in_array('operations', $reports_to_run)) {
    $res = $conn->query("SELECT status, COUNT(*) as count FROM appointments WHERE " . getDateCondition('appointment_date', $selected_month, $selected_year) . " GROUP BY status");
    $status_data = []; $status_labels = [];
    while ($row = $res->fetch_assoc()) { $status_labels[] = $row['status']; $status_data[] = (int)$row['count']; }
    $charts['appointment_status'] = ['labels' => $status_labels, 'data' => $status_data];

    $res = $conn->query("SELECT service_type, COUNT(*) as count FROM appointments WHERE " . getDateCondition('appointment_date', $selected_month, $selected_year) . " GROUP BY service_type");
    $serv_data = []; $serv_labels = [];
    while ($row = $res->fetch_assoc()) { $serv_labels[] = str_replace('_', ' ', $row['service_type']); $serv_data[] = (int)$row['count']; }
    $charts['service_type'] = ['labels' => $serv_labels, 'data' => $serv_data];
    
    $res = $conn->query("SELECT c.full_name, p.project_name, a.appointment_date, a.status FROM appointments a JOIN customers c ON a.customer_id = c.customer_id JOIN properties p ON a.property_id = p.property_id WHERE " . getDateCondition('a.appointment_date', $selected_month, $selected_year) . " ORDER BY a.appointment_date DESC LIMIT 20");
    $metrics['appt_list'] = [];
    while($row = $res->fetch_assoc()) { $metrics['appt_list'][] = $row; }
}

// --- PROCESS STAFF ---
if (in_array('staff', $reports_to_run)) {
    $res = $conn->query("
        SELECT s.staff_id, s.full_name, a.status, COUNT(a.appointment_id) as count 
        FROM appointments a 
        JOIN staff s ON a.assigned_staff_id = s.staff_id 
        WHERE " . getDateCondition('a.appointment_date', $selected_month, $selected_year) . " 
        GROUP BY s.staff_id, a.status 
    ");
    $staff_raw = [];
    $statuses = ['COMPLETED', 'ASSIGNED', 'NO_SHOW', 'CANCELLED', 'REQUESTED'];
    
    while ($row = $res->fetch_assoc()) {
        $staff_name = $row['full_name'];
        if (!isset($staff_raw[$staff_name])) {
            $staff_raw[$staff_name] = array_fill_keys($statuses, 0);
            $staff_raw[$staff_name]['TOTAL'] = 0;
        }
        $staff_raw[$staff_name][$row['status']] = (int)$row['count'];
        $staff_raw[$staff_name]['TOTAL'] += (int)$row['count'];
    }
    
    uasort($staff_raw, function($a, $b) { return $b['TOTAL'] <=> $a['TOTAL']; });
    $staff_raw = array_slice($staff_raw, 0, 10);
    
    $charts['staff_performance'] = [
        'labels' => array_keys($staff_raw),
        'datasets' => ['COMPLETED' => [], 'ASSIGNED' => [], 'CANCELLED' => [], 'NO_SHOW' => []]
    ];
    
    foreach ($staff_raw as $name => $counts) {
        $charts['staff_performance']['datasets']['COMPLETED'][] = $counts['COMPLETED'];
        $charts['staff_performance']['datasets']['ASSIGNED'][] = $counts['ASSIGNED'] + $counts['REQUESTED'];
        $charts['staff_performance']['datasets']['CANCELLED'][] = $counts['CANCELLED'];
        $charts['staff_performance']['datasets']['NO_SHOW'][] = $counts['NO_SHOW'];
    }
}

// --- PROCESS MARKET ---
if (in_array('market', $reports_to_run)) {
    // 1. Demand Conversion: Wishlists vs Appointments per Property Type
    $res = $conn->query("
        SELECT p.property_type, 
               COUNT(DISTINCT w.wishlist_id) as wishlist_count,
               COUNT(DISTINCT a.appointment_id) as appointment_count
        FROM properties p 
        LEFT JOIN wishlists w ON p.property_id = w.property_id AND " . getDateCondition('w.created_at', $selected_month, $selected_year) . "
        LEFT JOIN appointments a ON p.property_id = a.property_id AND " . getDateCondition('a.appointment_date', $selected_month, $selected_year) . "
        GROUP BY p.property_type
        HAVING wishlist_count > 0 OR appointment_count > 0
        ORDER BY wishlist_count DESC
    ");
    $market_types_labels = [];
    $market_wishlist_data = [];
    $market_appt_data = [];
    while ($row = $res->fetch_assoc()) {
        $market_types_labels[] = $row['property_type'];
        $market_wishlist_data[] = (int)$row['wishlist_count'];
        $market_appt_data[] = (int)$row['appointment_count'];
    }
    $charts['market_conversion'] = [
        'labels' => $market_types_labels,
        'wishlist' => $market_wishlist_data,
        'appointment' => $market_appt_data
    ];

    // 2. Regional Hotspots: Stacked Bar showing Property Types demand per State
    $res = $conn->query("
        SELECT p.state, p.property_type, COUNT(w.wishlist_id) as count 
        FROM wishlists w 
        JOIN properties p ON w.property_id = p.property_id 
        WHERE " . getDateCondition('w.created_at', $selected_month, $selected_year) . " 
        GROUP BY p.state, p.property_type
    ");
    $state_type_data = [];
    $all_m_states = [];
    $all_m_types = [];
    while ($row = $res->fetch_assoc()) {
        $st = $row['state'];
        $pt = $row['property_type'];
        if (!in_array($st, $all_m_states)) $all_m_states[] = $st;
        if (!in_array($pt, $all_m_types)) $all_m_types[] = $pt;
        if (!isset($state_type_data[$st])) $state_type_data[$st] = [];
        $state_type_data[$st][$pt] = (int)$row['count'];
    }
    $charts['regional_hotspots'] = ['labels' => $all_m_states, 'datasets' => []];
    $market_colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6f42c1'];
    $mc_idx = 0;
    foreach ($all_m_types as $type) {
        $ds_data = [];
        foreach ($all_m_states as $st) {
            $ds_data[] = $state_type_data[$st][$type] ?? 0;
        }
        $charts['regional_hotspots']['datasets'][] = [
            'label' => $type,
            'data' => $ds_data,
            'backgroundColor' => $market_colors[$mc_idx % count($market_colors)]
        ];
        $mc_idx++;
    }

    // 3. Trending Properties Table (Leaderboard)
    $res = $conn->query("
        SELECT p.project_name, p.property_type, p.state, p.price,
               COUNT(DISTINCT w.wishlist_id) as total_wishlists,
               COUNT(DISTINCT a.appointment_id) as total_appointments
        FROM properties p
        LEFT JOIN wishlists w ON p.property_id = w.property_id AND " . getDateCondition('w.created_at', $selected_month, $selected_year) . "
        LEFT JOIN appointments a ON p.property_id = a.property_id AND " . getDateCondition('a.appointment_date', $selected_month, $selected_year) . "
        GROUP BY p.property_id
        HAVING total_wishlists > 0 OR total_appointments > 0
        ORDER BY total_wishlists DESC, total_appointments DESC
        LIMIT 20
    ");
    $metrics['trending_properties'] = [];
    while($row = $res->fetch_assoc()) { $metrics['trending_properties'][] = $row; }
}

// --- PROCESS PRICING ---
if (in_array('pricing', $reports_to_run)) {
    $res = $conn->query("
        SELECT 
            CASE 
                WHEN p.price <= 300000 THEN '< RM 300k'
                WHEN p.price BETWEEN 300001 AND 600000 THEN 'RM 300k - 600k'
                WHEN p.price BETWEEN 600001 AND 1000000 THEN 'RM 600k - 1M'
                ELSE '> RM 1M'
            END as price_bracket,
            COUNT(w.wishlist_id) as count 
        FROM wishlists w JOIN properties p ON w.property_id = p.property_id 
        WHERE " . getDateCondition('w.created_at', $selected_month, $selected_year) . " 
        GROUP BY price_bracket
    ");
    $ordered_brackets = ['< RM 300k' => 0, 'RM 300k - 600k' => 0, 'RM 600k - 1M' => 0, '> RM 1M' => 0];
    while ($row = $res->fetch_assoc()) { $ordered_brackets[$row['price_bracket']] += (int)$row['count']; }
    $charts['pricing_analysis'] = ['labels' => array_keys($ordered_brackets), 'data' => array_values($ordered_brackets)];
}

// --- PROCESS AFFORDABLE ---
if (in_array('affordable', $reports_to_run)) {
    // Chart 1: Stacked Bar Chart for Status vs State (Month/Year Filtered)
    $res = $conn->query("
        SELECT p.state, a.status, COUNT(*) as count 
        FROM affordable_housing_applications a 
        JOIN properties p ON a.property_id = p.property_id 
        WHERE " . getDateCondition('a.application_date', $selected_month, $selected_year) . " 
        GROUP BY p.state, a.status
    ");
    $stacked_data = [];
    $all_states = [];
    while ($row = $res->fetch_assoc()) {
        $state = $row['state'];
        $status = $row['status'];
        
        if ($status === 'PENDING_REVIEW') $status_label = 'Pending Review';
        elseif ($status === 'APPROVED_FOR_DRAW') $status_label = 'Accept';
        elseif ($status === 'REJECTED') $status_label = 'Reject';
        elseif ($status === 'WINNER') $status_label = 'Winner';
        else $status_label = $status;

        if (!in_array($state, $all_states)) {
            $all_states[] = $state;
        }

        if (!isset($stacked_data[$state])) {
            $stacked_data[$state] = ['Pending Review' => 0, 'Accept' => 0, 'Reject' => 0, 'Winner' => 0];
        }
        $stacked_data[$state][$status_label] += (int)$row['count'];
    }

    $charts['aff_stacked'] = [
        'labels' => ['Pending Review', 'Accept', 'Reject', 'Winner'],
        'datasets' => []
    ];
    $state_colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6610f2', '#6f42c1', '#d63384', '#fd7e14', '#20c997'];
    $color_idx = 0;
    foreach ($all_states as $state) {
        $charts['aff_stacked']['datasets'][] = [
            'label' => $state,
            'data' => [
                $stacked_data[$state]['Pending Review'] ?? 0,
                $stacked_data[$state]['Accept'] ?? 0,
                $stacked_data[$state]['Reject'] ?? 0,
                $stacked_data[$state]['Winner'] ?? 0
            ],
            'backgroundColor' => $state_colors[$color_idx % count($state_colors)]
        ];
        $color_idx++;
    }

    // Chart 3: Top 5 Popular Affordable Housing Projects (Month/Year Filtered)
    $res = $conn->query("
        SELECT p.project_name, COUNT(a.application_id) as count 
        FROM affordable_housing_applications a 
        JOIN properties p ON a.property_id = p.property_id 
        WHERE " . getDateCondition('a.application_date', $selected_month, $selected_year) . " 
        GROUP BY p.property_id 
        ORDER BY count DESC 
        LIMIT 5
    ");
    $hot_proj_labels = []; $hot_proj_data = [];
    while ($row = $res->fetch_assoc()) { 
        $hot_proj_labels[] = $row['project_name']; 
        $hot_proj_data[] = (int)$row['count']; 
    }
    $charts['hot_affordable_projects'] = ['labels' => $hot_proj_labels, 'data' => $hot_proj_data];

    // Chart 2: Cumulative applications per state (No month/year filter)
    $res = $conn->query("
        SELECT p.state, COUNT(a.application_id) as count 
        FROM affordable_housing_applications a 
        JOIN properties p ON a.property_id = p.property_id 
        GROUP BY p.state 
        ORDER BY count DESC
    ");
    $aff_state_data = []; $aff_state_labels = [];
    while ($row = $res->fetch_assoc()) { 
        $aff_state_labels[] = $row['state']; 
        $aff_state_data[] = (int)$row['count']; 
    }
    $charts['affordable_states_cumulative'] = ['labels' => $aff_state_labels, 'data' => $aff_state_data];
    
    // Bottom part: Table data
    $res = $conn->query("
        SELECT c.full_name, p.project_name, p.state, a.application_date, s.full_name as reviewed_by
        FROM affordable_housing_applications a 
        JOIN customers c ON a.customer_id = c.customer_id 
        JOIN properties p ON a.property_id = p.property_id 
        LEFT JOIN staff s ON a.reviewed_by_staff_id = s.staff_id
        WHERE " . getDateCondition('a.application_date', $selected_month, $selected_year) . " 
        ORDER BY a.application_date DESC 
        LIMIT 50
    ");
    $metrics['aff_list'] = [];
    while($row = $res->fetch_assoc()) { $metrics['aff_list'][] = $row; }
}


$page_title = "Business Reports";
include '../includes/header.php';
?>

<!-- Print Styles for Exporting Reports -->
<style>
@media print {
    @page { size: A4 portrait; margin: 0.5cm; }
    body { background-color: #fff !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
    .sidebar-heading, .list-group, form, .btn, header, footer, .navbar, .d-print-none { display: none !important; }
    .col-md-3, .col-lg-2 { display: none !important; } 
    .col-md-9, .col-lg-10 { width: 100% !important; max-width: 100% !important; flex: 0 0 100% !important; padding: 0 !important; margin: 0 !important; }
    .card { border: none !important; box-shadow: none !important; margin-bottom: 2rem !important; width: 100% !important; }
    .card-header, .card-body { padding: 0 !important; width: 100% !important; }
    canvas { max-width: 100% !important; height: auto !important; max-height: 400px !important; page-break-inside: avoid; }
    .container-fluid { padding: 0 !important; margin: 0 !important; width: 100% !important; }
    .row { display: block !important; width: 100% !important; margin: 0 !important; }
    .col-12, .col-md-4, .col-md-6 { display: block !important; width: 100% !important; max-width: 100% !important; flex: none !important; padding: 0 !important; }
    .table-responsive { overflow: visible !important; width: 100% !important; }
    .print-title { display: block !important; text-align: center; margin-bottom: 20px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 10px; }
    .print-table { width: 100% !important; border-collapse: collapse !important; font-size: 11px; table-layout: fixed; word-wrap: break-word; }
    .print-table th, .print-table td { border: 1px solid #ddd !important; padding: 6px !important; text-align: left !important; white-space: normal !important; overflow-wrap: break-word; }
    .print-table th { background-color: #f8f9fa !important; font-weight: bold !important; }
}
.print-title { display: none; }
</style>

<div class="container-fluid my-5">
    <!-- Print Official Header (Visible only in PDF/Print mode) -->
    <div class="print-title">
        <h1 class="m-0">SYS Property Holdings</h1>
        <h3>Official Business Report</h3>
        <p class="text-muted m-0">Generated on: <?= date('d M Y, h:i A') ?></p>
        <p class="text-muted m-0">Report Period: <?= date('F', mktime(0,0,0,$selected_month,1)) . ' ' . $selected_year ?></p>
    </div>

    <!-- Header & Date Filter -->
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3 d-print-none">
        <h2 class="fw-bold m-0"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Business Intelligence Analytics</h2>
        <div class="d-flex align-items-center gap-2">
            <form class="d-flex align-items-center gap-2 m-0" method="GET" action="business_reports.php">
                <input type="hidden" name="report" value="<?= htmlspecialchars($report_type) ?>">
                <select name="month" class="form-select shadow-sm" style="width: auto;">
                    <?php for($m=1; $m<=12; ++$m): ?>
                        <option value="<?= $m ?>" <?= $m == $selected_month ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                    <?php endfor; ?>
                </select>
                <select name="year" class="form-select shadow-sm" style="width: auto;">
                    <?php $current_year = date('Y'); for($y=$current_year-2; $y<=$current_year; ++$y): ?>
                        <option value="<?= $y ?>" <?= $y == $selected_year ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-dark shadow-sm px-4">Filter</button>
            </form>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card bg-primary text-white shadow-sm border-0 h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="text-uppercase fw-bold mb-2">New Registrations</h6>
                    <h2 class="fw-bold m-0"><?= $metrics['new_registrations'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-info text-dark shadow-sm border-0 h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="text-uppercase fw-bold mb-2">Wishlist Additions (Leads)</h6>
                    <h2 class="fw-bold m-0"><?= $metrics['wishlist_adds'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-success text-white shadow-sm border-0 h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="text-uppercase fw-bold mb-2">Total Appointments</h6>
                    <h2 class="fw-bold m-0"><?= $metrics['total_appointments'] ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Structured Sidebar Navigation -->
        <div class="col-md-3 col-lg-2 mb-4 d-print-none">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush rounded">
                        
                        <h6 class="sidebar-heading px-3 mt-3 mb-2 text-muted fw-bold text-uppercase" style="font-size: 0.75rem;">Overview & Growth</h6>
                        <a href="?report=engagement&month=<?= $selected_month ?>&year=<?= $selected_year ?>" class="list-group-item list-group-item-action <?= $report_type === 'engagement' ? 'active bg-dark border-dark' : '' ?>">
                            <i class="bi bi-activity me-2"></i> Engagement Trends
                        </a>
                        <a href="?report=demographics&month=<?= $selected_month ?>&year=<?= $selected_year ?>" class="list-group-item list-group-item-action <?= $report_type === 'demographics' ? 'active bg-dark border-dark' : '' ?>">
                            <i class="bi bi-people-fill me-2"></i> Demographics
                        </a>

                        <h6 class="sidebar-heading px-3 mt-4 mb-2 text-muted fw-bold text-uppercase" style="font-size: 0.75rem;">Operations & Sales</h6>
                        <a href="?report=operations&month=<?= $selected_month ?>&year=<?= $selected_year ?>" class="list-group-item list-group-item-action <?= $report_type === 'operations' ? 'active bg-dark border-dark' : '' ?>">
                            <i class="bi bi-calendar-check-fill me-2"></i> Appointments
                        </a>
                        <a href="?report=staff&month=<?= $selected_month ?>&year=<?= $selected_year ?>" class="list-group-item list-group-item-action <?= $report_type === 'staff' ? 'active bg-dark border-dark' : '' ?>">
                            <i class="bi bi-person-badge-fill me-2"></i> Staff Performance
                        </a>

                        <h6 class="sidebar-heading px-3 mt-4 mb-2 text-muted fw-bold text-uppercase" style="font-size: 0.75rem;">Market Intelligence</h6>
                        <a href="?report=market&month=<?= $selected_month ?>&year=<?= $selected_year ?>" class="list-group-item list-group-item-action <?= $report_type === 'market' ? 'active bg-dark border-dark' : '' ?>">
                            <i class="bi bi-geo-alt-fill me-2"></i> Locations & Types
                        </a>
                        <a href="?report=pricing&month=<?= $selected_month ?>&year=<?= $selected_year ?>" class="list-group-item list-group-item-action <?= $report_type === 'pricing' ? 'active bg-dark border-dark' : '' ?>">
                            <i class="bi bi-tags-fill me-2"></i> Pricing Analysis
                        </a>

                        <h6 class="sidebar-heading px-3 mt-4 mb-2 text-muted fw-bold text-uppercase" style="font-size: 0.75rem;">Special Programs</h6>
                        <a href="?report=affordable&month=<?= $selected_month ?>&year=<?= $selected_year ?>" class="list-group-item list-group-item-action <?= $report_type === 'affordable' ? 'active bg-dark border-dark' : '' ?>">
                            <i class="bi bi-house-heart-fill me-2"></i> Affordable Housing
                        </a>

                        <!-- EXPORT ALL BUTTON IN SIDEBAR -->
                        <div class="mt-4 p-3 border-top">
                            <a href="?report=all&month=<?= $selected_month ?>&year=<?= $selected_year ?>&print=1" class="btn btn-warning w-100 fw-bold shadow-sm text-dark">
                                <i class="bi bi-file-earmark-pdf-fill me-2"></i> Export All to PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-md-9 col-lg-10">

            <?php if (in_array('engagement', $reports_to_run)): ?>
                <?php if ($report_type === 'all'): ?><h3 class="fw-bold mt-5 mb-4 border-bottom pb-2">Engagement Trends</h3><?php endif; ?>
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h4 class="fw-bold mb-3">Daily Engagement Trends</h4>
                                <p class="text-muted mb-4 d-print-none">Track daily user registrations and property wishlist additions to measure marketing impact.</p>
                                <?php if(empty(array_filter($charts['daily_trends']['datasets'][0]['data'])) && empty(array_filter($charts['daily_trends']['datasets'][1]['data']))): ?>
                                    <p class="text-muted text-center my-5">No activity data for this period.</p>
                                <?php else: ?>
                                    <div style="position: relative; height: 350px; width: 100%;">
                                        <canvas id="dailyTrendsChart"></canvas>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Data Table for Export -->
                <div class="card shadow-sm border-0 mb-4 page-break-before">
                    <div class="card-header bg-dark text-white"><h5 class="m-0 py-1"><i class="bi bi-list-columns me-2"></i>Detailed Record: Recent Registrations</h5></div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table print-table mb-0 table-striped">
                            <thead><tr><th>Full Name</th><th>Email</th><th>Registration Date</th></tr></thead>
                            <tbody>
                                <?php if(empty($metrics['recent_users'])): ?><tr><td colspan="3" class="text-center">No recent records</td></tr><?php endif; ?>
                                <?php foreach($metrics['recent_users'] as $user): ?>
                                    <tr><td><?= htmlspecialchars($user['full_name'] ?? 'N/A') ?></td><td><?= htmlspecialchars($user['email']) ?></td><td><?= htmlspecialchars($user['created_at']) ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <?php if ($report_type === 'engagement'): ?>
                <!-- Single Report Export Button -->
                <div class="row mt-2 mb-5 d-print-none">
                    <div class="col-12">
                        <button onclick="window.print()" class="btn btn-dark btn-lg w-100 fw-bold shadow-sm text-white">
                            <i class="bi bi-printer-fill me-2"></i> Export Engagement Report to PDF
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (in_array('demographics', $reports_to_run)): ?>
                <?php if ($report_type === 'all'): ?><h3 class="fw-bold mt-5 mb-4 border-bottom pb-2" style="page-break-before: always;">Demographics</h3><?php endif; ?>
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h4 class="fw-bold mb-3">Income Brackets Analysis</h4>
                                <?php if(empty(array_filter($charts['income_brackets']['data']))): ?>
                                    <p class="text-muted text-center my-5">No data for this period.</p>
                                <?php else: ?>
                                    <div style="position: relative; height: 300px; width: 100%;">
                                        <canvas id="incomeChart"></canvas>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body p-4">
                                <h4 class="fw-bold mb-3">Top 5 Occupations</h4>
                                <?php if(empty($metrics['top_occupations'])): ?>
                                    <p class="text-muted text-center my-4">No data for this period.</p>
                                <?php else: ?>
                                    <ul class="list-group list-group-flush mt-3">
                                        <?php $occ_index = 1; foreach($metrics['top_occupations'] as $occ_name => $occ_count): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                                <span class="fs-5"><span class="badge bg-dark me-3">#<?= $occ_index++ ?></span> <?= htmlspecialchars($occ_name) ?></span>
                                                <span class="badge bg-info text-dark rounded-pill fs-6"><?= $occ_count ?> Customers</span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body p-4 text-center">
                                <h4 class="fw-bold mb-3">Marital Status</h4>
                                <?php if(empty(array_filter($charts['marital_status']['data']))): ?>
                                    <p class="text-muted text-center my-5">No data for this period.</p>
                                <?php else: ?>
                                    <div class="d-flex justify-content-center" style="position: relative; height: 250px; width: 100%;">
                                        <canvas id="maritalChart"></canvas>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Data Table for Export -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white"><h5 class="m-0 py-1"><i class="bi bi-list-columns me-2"></i>Detailed Record: Sample Active Customers</h5></div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table print-table mb-0 table-striped">
                            <thead><tr><th>Full Name</th><th>Occupation</th><th>Monthly Income (RM)</th></tr></thead>
                            <tbody>
                                <?php if(empty($metrics['demo_list'])): ?><tr><td colspan="3" class="text-center">No records</td></tr><?php endif; ?>
                                <?php foreach($metrics['demo_list'] as $demo): ?>
                                    <tr><td><?= htmlspecialchars($demo['full_name']) ?></td><td><?= htmlspecialchars(ucwords(strtolower($demo['occupation']))) ?></td><td><?= number_format($demo['monthly_income'], 2) ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if ($report_type === 'demographics'): ?>
                <!-- Single Report Export Button -->
                <div class="row mt-2 mb-5 d-print-none">
                    <div class="col-12">
                        <button onclick="window.print()" class="btn btn-outline-dark btn-lg w-100 fw-bold shadow-sm">
                            <i class="bi bi-printer-fill me-2"></i> Export Demographics Report to PDF
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (in_array('operations', $reports_to_run)): ?>
                <?php if ($report_type === 'all'): ?><h3 class="fw-bold mt-5 mb-4 border-bottom pb-2" style="page-break-before: always;">Operations & Sales</h3><?php endif; ?>
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h4 class="fw-bold mb-3">Appointment Status Breakdown</h4>
                                <?php if(empty($charts['appointment_status']['data'])): ?>
                                    <p class="text-muted text-center my-5">No data for this period.</p>
                                <?php else: ?>
                                    <div style="position: relative; height: 300px; width: 100%;">
                                        <canvas id="appStatusChart"></canvas>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h4 class="fw-bold mb-3">Service Type Demand</h4>
                                <?php if(empty($charts['service_type']['data'])): ?>
                                    <p class="text-muted text-center my-5">No data for this period.</p>
                                <?php else: ?>
                                    <div style="position: relative; height: 250px; width: 100%;">
                                        <canvas id="serviceTypeChart"></canvas>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Data Table for Export -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white"><h5 class="m-0 py-1"><i class="bi bi-list-columns me-2"></i>Detailed Record: Recent Appointments</h5></div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table print-table mb-0 table-striped">
                            <thead><tr><th>Customer Name</th><th>Property Project</th><th>Appointment Date</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if(empty($metrics['appt_list'])): ?><tr><td colspan="4" class="text-center">No recent records</td></tr><?php endif; ?>
                                <?php foreach($metrics['appt_list'] as $appt): ?>
                                    <tr><td><?= htmlspecialchars($appt['full_name']) ?></td><td><?= htmlspecialchars($appt['project_name']) ?></td><td><?= htmlspecialchars($appt['appointment_date']) ?></td><td><?= htmlspecialchars($appt['status']) ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if ($report_type === 'operations'): ?>
                <div class="row mt-2 mb-5 d-print-none">
                    <div class="col-12">
                        <button onclick="window.print()" class="btn btn-outline-dark btn-lg w-100 fw-bold shadow-sm">
                            <i class="bi bi-printer-fill me-2"></i> Export Operations Report to PDF
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (in_array('staff', $reports_to_run)): ?>
                <?php if ($report_type === 'all'): ?><h3 class="fw-bold mt-5 mb-4 border-bottom pb-2" style="page-break-before: always;">Staff Performance</h3><?php endif; ?>
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h4 class="fw-bold mb-3">Top Staff Performance & Lead Conversions</h4>
                                <?php if(empty($charts['staff_performance']['labels'])): ?>
                                    <p class="text-muted text-center my-5">No data for this period.</p>
                                <?php else: ?>
                                    <div style="position: relative; height: 400px; width: 100%;">
                                        <canvas id="staffChart"></canvas>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($report_type === 'staff'): ?>
                <div class="row mt-2 mb-5 d-print-none">
                    <div class="col-12">
                        <button onclick="window.print()" class="btn btn-outline-dark btn-lg w-100 fw-bold shadow-sm">
                            <i class="bi bi-printer-fill me-2"></i> Export Staff Report to PDF
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (in_array('market', $reports_to_run)): ?>
                <?php if ($report_type === 'all'): ?><h3 class="fw-bold mt-5 mb-4 border-bottom pb-2" style="page-break-before: always;">Market Intelligence</h3><?php endif; ?>
                
                <!-- Chart 1: Demand Conversion -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h4 class="fw-bold mb-3">Interest vs Action (Wishlists & Appointments by Type)</h4>
                                <p class="text-muted mb-4 d-print-none">Compares raw interest (wishlists) against actual actions (appointments) to calculate conversion effectiveness.</p>
                                <?php if(empty($charts['market_conversion']['labels'])): ?>
                                    <p class="text-muted text-center my-5">No data for this period.</p>
                                <?php else: ?>
                                    <div style="position: relative; height: 350px; width: 100%;">
                                        <canvas id="marketConversionChart"></canvas>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart 2: Regional Hotspots -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h4 class="fw-bold mb-3">Regional Hotspots (Property Type Demand per State)</h4>
                                <p class="text-muted mb-4 d-print-none">Shows what type of properties are trending in different geographical regions.</p>
                                <?php if(empty($charts['regional_hotspots']['datasets'])): ?>
                                    <p class="text-muted text-center my-5">No data for this period.</p>
                                <?php else: ?>
                                    <div style="position: relative; height: 400px; width: 100%;">
                                        <canvas id="regionalHotspotsChart"></canvas>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Data Table -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white"><h5 class="m-0 py-1"><i class="bi bi-star-fill me-2 text-warning"></i>Trending Properties Leaderboard</h5></div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table print-table mb-0 table-hover table-striped">
                            <thead><tr><th>Project Name</th><th>Type</th><th>State</th><th>Price (RM)</th><th>Wishlists <i class="bi bi-heart-fill text-danger"></i></th><th>Appointments <i class="bi bi-calendar-check-fill text-success"></i></th></tr></thead>
                            <tbody>
                                <?php if(empty($metrics['trending_properties'])): ?><tr><td colspan="6" class="text-center">No trending properties this month</td></tr><?php endif; ?>
                                <?php foreach($metrics['trending_properties'] as $prop): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($prop['project_name']) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($prop['property_type']) ?></span></td>
                                        <td><?= htmlspecialchars($prop['state']) ?></td>
                                        <td><?= number_format($prop['price'], 2) ?></td>
                                        <td class="fw-bold text-danger"><?= $prop['total_wishlists'] ?></td>
                                        <td class="fw-bold text-success"><?= $prop['total_appointments'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if ($report_type === 'market'): ?>
                <div class="row mt-2 mb-5 d-print-none">
                    <div class="col-12">
                        <button onclick="window.print()" class="btn btn-outline-dark btn-lg w-100 fw-bold shadow-sm">
                            <i class="bi bi-printer-fill me-2"></i> Export Market Intelligence to PDF
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (in_array('pricing', $reports_to_run)): ?>
                <?php if ($report_type === 'all'): ?><h3 class="fw-bold mt-5 mb-4 border-bottom pb-2" style="page-break-before: always;">Pricing Analysis</h3><?php endif; ?>
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h4 class="fw-bold mb-3">Pricing Analysis (Demand vs. Price)</h4>
                                <?php if(empty(array_filter($charts['pricing_analysis']['data']))): ?>
                                    <p class="text-muted text-center my-5">No data for this period.</p>
                                <?php else: ?>
                                    <div style="position: relative; height: 400px; width: 100%;">
                                        <canvas id="pricingChart"></canvas>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($report_type === 'pricing'): ?>
                <div class="row mt-2 mb-5 d-print-none">
                    <div class="col-12">
                        <button onclick="window.print()" class="btn btn-outline-dark btn-lg w-100 fw-bold shadow-sm">
                            <i class="bi bi-printer-fill me-2"></i> Export Pricing Analysis to PDF
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (in_array('affordable', $reports_to_run)): ?>
                <?php if ($report_type === 'all'): ?><h3 class="fw-bold mt-5 mb-4 border-bottom pb-2" style="page-break-before: always;">Special Programs: Affordable Housing</h3><?php endif; ?>
                
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h4 class="fw-bold mb-3">Application Status by State (Filtered by Month & Year)</h4>
                                <?php if(empty($charts['aff_stacked']['datasets'])): ?>
                                    <p class="text-muted text-center my-5">No data for this period.</p>
                                <?php else: ?>
                                    <div style="position: relative; height: 350px; width: 100%;">
                                        <canvas id="affStackedChart"></canvas>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h4 class="fw-bold mb-3">Total Applicants By State (Cumulative)</h4>
                                <?php if(empty($charts['affordable_states_cumulative']['data'])): ?>
                                    <p class="text-muted text-center my-5">No data available.</p>
                                <?php else: ?>
                                    <div style="position: relative; height: 350px; width: 100%;">
                                        <canvas id="affStatesCumulativeChart"></canvas>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h4 class="fw-bold mb-3">Top 5 Popular Affordable Housing Projects (Filtered by Month & Year)</h4>
                                <?php if(empty($charts['hot_affordable_projects']['data'])): ?>
                                    <p class="text-muted text-center my-5">No data available.</p>
                                <?php else: ?>
                                    <div style="position: relative; height: 350px; width: 100%;">
                                        <canvas id="affHotProjectsChart"></canvas>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table for Export -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white"><h5 class="m-0 py-1"><i class="bi bi-list-columns me-2"></i>Detailed Record: Applications</h5></div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table print-table mb-0 table-striped">
                            <thead><tr><th>Application Date</th><th>Applicant Name</th><th>Affordable House Name</th><th>State</th><th>Reviewed By</th></tr></thead>
                            <tbody>
                                <?php if(empty($metrics['aff_list'])): ?><tr><td colspan="5" class="text-center">No records</td></tr><?php endif; ?>
                                <?php foreach($metrics['aff_list'] as $app): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($app['application_date']) ?></td>
                                        <td><?= htmlspecialchars($app['full_name']) ?></td>
                                        <td><?= htmlspecialchars($app['project_name']) ?></td>
                                        <td><?= htmlspecialchars($app['state']) ?></td>
                                        <td><?= htmlspecialchars($app['reviewed_by'] ?? 'Pending Review') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if ($report_type === 'affordable'): ?>
                <div class="row mt-2 mb-5 d-print-none">
                    <div class="col-12">
                        <button onclick="window.print()" class="btn btn-outline-dark btn-lg w-100 fw-bold shadow-sm">
                            <i class="bi bi-printer-fill me-2"></i> Export Affordable Housing Report to PDF
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function getColors(count) {
        const colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6610f2', '#6f42c1', '#d63384', '#fd7e14', '#20c997'];
        return Array.from({length: count}, (_, i) => colors[i % colors.length]);
    }

    const barOptions = {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
        plugins: { legend: { display: false } },
        animation: { duration: <?= $auto_print ? 0 : 1000 ?> } // Disable animation for immediate printing
    };

    <?php if (in_array('engagement', $reports_to_run)): ?>
        <?php if(!empty(array_filter($charts['daily_trends']['datasets'][0]['data'])) || !empty(array_filter($charts['daily_trends']['datasets'][1]['data']))): ?>
        new Chart(document.getElementById('dailyTrendsChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: <?= json_encode($charts['daily_trends']['labels']) ?>,
                datasets: <?= json_encode($charts['daily_trends']['datasets']) ?>
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                plugins: { legend: { position: 'bottom' } },
                animation: { duration: <?= $auto_print ? 0 : 1000 ?> }
            }
        });
        <?php endif; ?>
    <?php endif; ?>

    <?php if (in_array('demographics', $reports_to_run)): ?>
        <?php if(!empty(array_filter($charts['marital_status']['data']))): ?>
        new Chart(document.getElementById('maritalChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($charts['marital_status']['labels']) ?>,
                datasets: [{
                    data: <?= json_encode($charts['marital_status']['data']) ?>,
                    backgroundColor: ['#0d6efd', '#d63384']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, animation: { duration: <?= $auto_print ? 0 : 1000 ?> } }
        });
        <?php endif; ?>
        
        <?php if(!empty(array_filter($charts['income_brackets']['data']))): ?>
        new Chart(document.getElementById('incomeChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($charts['income_brackets']['labels']) ?>,
                datasets: [{
                    label: 'Customers',
                    data: <?= json_encode($charts['income_brackets']['data']) ?>,
                    backgroundColor: ['#198754', '#ffc107', '#fd7e14', '#dc3545']
                }]
            },
            options: barOptions
        });
        <?php endif; ?>
    <?php endif; ?>

    <?php if (in_array('operations', $reports_to_run)): ?>
        <?php if(!empty($charts['appointment_status']['data'])): ?>
        new Chart(document.getElementById('appStatusChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($charts['appointment_status']['labels']) ?>,
                datasets: [{
                    data: <?= json_encode($charts['appointment_status']['data']) ?>,
                    backgroundColor: getColors(<?= count($charts['appointment_status']['data']) ?>)
                }]
            },
            options: barOptions
        });
        <?php endif; ?>

        <?php if(!empty($charts['service_type']['data'])): ?>
        new Chart(document.getElementById('serviceTypeChart').getContext('2d'), {
            type: 'bar',
            indexAxis: 'y',
            data: {
                labels: <?= json_encode($charts['service_type']['labels']) ?>,
                datasets: [{
                    data: <?= json_encode($charts['service_type']['data']) ?>,
                    backgroundColor: ['#0d6efd', '#20c997']
                }]
            },
            options: barOptions
        });
        <?php endif; ?>
    <?php endif; ?>

    <?php if (in_array('staff', $reports_to_run)): ?>
        <?php if(!empty($charts['staff_performance']['labels'])): ?>
        new Chart(document.getElementById('staffChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($charts['staff_performance']['labels']) ?>,
                datasets: [
                    { label: 'Completed', data: <?= json_encode($charts['staff_performance']['datasets']['COMPLETED']) ?>, backgroundColor: '#198754' },
                    { label: 'Assigned', data: <?= json_encode($charts['staff_performance']['datasets']['ASSIGNED']) ?>, backgroundColor: '#0d6efd' },
                    { label: 'Cancelled', data: <?= json_encode($charts['staff_performance']['datasets']['CANCELLED']) ?>, backgroundColor: '#dc3545' },
                    { label: 'No Show', data: <?= json_encode($charts['staff_performance']['datasets']['NO_SHOW']) ?>, backgroundColor: '#ffc107' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } } },
                plugins: { legend: { position: 'bottom' } },
                animation: { duration: <?= $auto_print ? 0 : 1000 ?> }
            }
        });
        <?php endif; ?>
    <?php endif; ?>

    <?php if (in_array('market', $reports_to_run)): ?>
        <?php if(!empty($charts['market_conversion']['labels'])): ?>
        new Chart(document.getElementById('marketConversionChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($charts['market_conversion']['labels']) ?>,
                datasets: [
                    {
                        label: 'Wishlists (Interest)',
                        data: <?= json_encode($charts['market_conversion']['wishlist']) ?>,
                        backgroundColor: 'rgba(220, 53, 69, 0.8)',
                        borderRadius: 4
                    },
                    {
                        label: 'Appointments (Action)',
                        data: <?= json_encode($charts['market_conversion']['appointment']) ?>,
                        backgroundColor: 'rgba(25, 135, 84, 0.8)',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                plugins: { legend: { position: 'top' } },
                animation: { duration: <?= $auto_print ? 0 : 1000 ?> }
            }
        });
        <?php endif; ?>

        <?php if(!empty($charts['regional_hotspots']['datasets'])): ?>
        new Chart(document.getElementById('regionalHotspotsChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($charts['regional_hotspots']['labels']) ?>,
                datasets: <?= json_encode($charts['regional_hotspots']['datasets']) ?>
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { 
                    x: { stacked: true }, 
                    y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } } 
                },
                plugins: { legend: { position: 'right' } },
                animation: { duration: <?= $auto_print ? 0 : 1000 ?> }
            }
        });
        <?php endif; ?>
    <?php endif; ?>
        
    <?php if (in_array('pricing', $reports_to_run)): ?>
        <?php if(!empty(array_filter($charts['pricing_analysis']['data']))): ?>
        new Chart(document.getElementById('pricingChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($charts['pricing_analysis']['labels']) ?>,
                datasets: [{
                    data: <?= json_encode($charts['pricing_analysis']['data']) ?>,
                    backgroundColor: ['#20c997', '#0d6efd', '#ffc107', '#dc3545']
                }]
            },
            options: barOptions
        });
        <?php endif; ?>
    <?php endif; ?>
        
    <?php if (in_array('affordable', $reports_to_run)): ?>
        <?php if(!empty($charts['aff_stacked']['datasets'])): ?>
        new Chart(document.getElementById('affStackedChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($charts['aff_stacked']['labels']) ?>,
                datasets: <?= json_encode($charts['aff_stacked']['datasets']) ?>
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { 
                    x: { stacked: true }, 
                    y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } } 
                },
                plugins: { legend: { position: 'right' } },
                animation: { duration: <?= $auto_print ? 0 : 1000 ?> }
            }
        });
        <?php endif; ?>

        <?php if(!empty($charts['affordable_states_cumulative']['data'])): ?>
        new Chart(document.getElementById('affStatesCumulativeChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($charts['affordable_states_cumulative']['labels']) ?>,
                datasets: [{
                    label: 'Applicants',
                    data: <?= json_encode($charts['affordable_states_cumulative']['data']) ?>,
                    backgroundColor: '#6f42c1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                plugins: { legend: { display: false } },
                animation: { duration: <?= $auto_print ? 0 : 1000 ?> }
            }
        });
        <?php endif; ?>

        <?php if(!empty($charts['hot_affordable_projects']['data'])): ?>
        new Chart(document.getElementById('affHotProjectsChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($charts['hot_affordable_projects']['labels']) ?>,
                datasets: [{
                    label: 'Applications',
                    data: <?= json_encode($charts['hot_affordable_projects']['data']) ?>,
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                plugins: { legend: { display: false } },
                animation: { duration: <?= $auto_print ? 0 : 1000 ?> }
            }
        });
        <?php endif; ?>
    <?php endif; ?>

    // Auto trigger print if requested via URL
    <?php if ($auto_print): ?>
    window.addEventListener('load', function() {
        setTimeout(function() { window.print(); }, 500); // slight delay to ensure charts rendered without animation
    });
    <?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>
