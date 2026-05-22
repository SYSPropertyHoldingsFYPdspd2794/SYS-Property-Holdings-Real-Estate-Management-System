<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn);

$res_prop = $conn->query("SELECT COUNT(*) as count FROM properties WHERE status = 'ACTIVE' AND is_affordable = 0");
$total_prop = $res_prop->fetch_assoc()['count'];

$res_leads = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'REQUESTED'");
$total_leads = $res_leads->fetch_assoc()['count'];

$res_app = $conn->query("SELECT COUNT(*) as count FROM affordable_housing_applications WHERE status = 'PENDING_REVIEW'");
$total_app = $res_app->fetch_assoc()['count'];

$res_cust = $conn->query("SELECT COUNT(*) as count FROM accounts WHERE role = 'CUSTOMER'");
$total_cust = $res_cust->fetch_assoc()['count'];

$chart_data = [];
$chart_labels = [];
$res_chart = $conn->query("SELECT p.state, COUNT(a.appointment_id) as lead_count FROM appointments a JOIN properties p ON a.property_id = p.property_id GROUP BY p.state");
while ($row = $res_chart->fetch_assoc()) {
    $chart_labels[] = $row['state'];
    $chart_data[] = (int)$row['lead_count'];
}

include '../includes/header.php';
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Global Administrator Dashboard</h2> 
        </div>

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-dark text-white shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <h6 class="text-uppercase text-light mb-2">Total Active Properties</h6>
                    <h2 class="fw-bold"><?php echo $total_prop; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <h6 class="text-uppercase text-white mb-2">Pending Leads</h6>
                    <h2 class="fw-bold"><?php echo $total_leads; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <h6 class="text-uppercase text-white mb-2">Housing Applications</h6>
                    <h2 class="fw-bold"><?php echo $total_app; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-dark shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <h6 class="text-uppercase text-dark mb-2">Total Customers</h6>
                    <h2 class="fw-bold"><?php echo $total_cust; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">O2O Regional Lead Distribution</h4>
                    <canvas id="leadsChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">Quick Actions</h4>
                    <div class="d-grid gap-3">
                        <a href="properties.php" class="btn btn-dark btn-lg fw-bold">Manage Inventory</a>
                        <a href="appointments.php" class="btn btn-primary btn-lg fw-bold">Assign Leads</a>
                        <a href="lucky_draw.php" class="btn btn-success btn-lg fw-bold">Execute Lucky Draw</a>
                        <a href="affordable_approvals.php" class="btn btn-warning btn-lg fw-bold text-dark">View Approvals Log</a>
                        <a href="user.php" class="btn btn-outline-dark btn-lg fw-bold">Manage Users</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('leadsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Pending Leads',
                data: <?php echo json_encode($chart_data); ?>,
                backgroundColor: 'rgba(13, 110, 253, 0.8)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
