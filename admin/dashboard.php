<?php
session_start();
if (!isset($_SESSION['role']) |

| $_SESSION['role']!== 'ADMIN') {
    header("Location:../login.php");
    exit();
}
include '../includes/db_connect.php';

$res_prop = $conn->query("SELECT COUNT(*) as count FROM properties WHERE status = 'ACTIVE'");
$total_prop = $res_prop->fetch_assoc()['count'];

$res_leads = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'REQUESTED'");
$total_leads = $res_leads->fetch_assoc()['count'];

$res_app = $conn->query("SELECT COUNT(*) as count FROM affordable_housing_applications WHERE status = 'PENDING_REVIEW'");
$total_app = $res_app->fetch_assoc()['count'];

$res_cust = $conn->query("SELECT COUNT(*) as count FROM accounts WHERE role = 'CUSTOMER'");
$total_cust = $res_cust->fetch_assoc()['count'];

$chart_data =;
$chart_labels =;
$res_chart = $conn->query("SELECT p.state, COUNT(a.appointment_id) as lead_count FROM appointments a JOIN properties p ON a.property_id = p.property_id GROUP BY p.state");
while ($row = $res_chart->fetch_assoc()) {
    $chart_labels = $row['state'];
    $chart_data = $row['lead_count'];
}

include '../includes/header.php';
?>
<div class="container my-5">
    <h2 class="fw-bold mb-4">Global Administrator Dashboard</h2>
    <div class="row mb-5">
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 border-start border-primary border-5">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-muted text-uppercase mb-2">Total Active Properties</h6>
                    <h2 class="display-5 fw-bold text-dark m-0"><?php echo $total_prop;?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 border-start border-warning border-5">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-muted text-uppercase mb-2">Pending Leads</h6>
                    <h2 class="display-5 fw-bold text-dark m-0"><?php echo $total_leads;?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 border-start border-success border-5">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-muted text-uppercase mb-2">Pending Housing Applicants</h6>
                    <h2 class="display-5 fw-bold text-dark m-0"><?php echo $total_app;?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 border-start border-info border-5">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-muted text-uppercase mb-2">Registered Customers</h6>
                    <h2 class="display-5 fw-bold text-dark m-0"><?php echo $total_cust;?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <h4 class="fw-bold mb-4">Lead Distribution by State</h4>
                    <canvas id="leadsChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-5">
                    <h4 class="fw-bold mb-4">Quick Actions</h4>
                    <div class="d-grid gap-3">
                        <a href="properties.php" class="btn btn-dark btn-lg fw-bold">Manage Inventory</a>
                        <a href="appointments.php" class="btn btn-primary btn-lg fw-bold">Assign Leads</a>
                        <a href="lucky_draw.php" class="btn btn-success btn-lg fw-bold">Execute Lucky Draw</a>
                        <a href="users.php" class="btn btn-outline-dark btn-lg fw-bold">Manage Staff</a>
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
            labels: <?php echo json_encode($chart_labels);?>,
            datasets:
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
</script>

<?php include '../includes/footer.php';?>

