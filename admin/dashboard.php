<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn);

if (isset($_GET['ajax_chart'])) {
    $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

    // Stats
    $stmt = $conn->prepare("SELECT 
        SUM(CASE WHEN status = 'REQUESTED' THEN 1 ELSE 0 END) as waiting,
        SUM(CASE WHEN assigned_staff_id IS NOT NULL THEN 1 ELSE 0 END) as assigned
        FROM appointments
        WHERE MONTH(appointment_date) = ? AND YEAR(appointment_date) = ?");
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();

    // Chart
    $stmt_chart = $conn->prepare("SELECT p.state,
        SUM(CASE WHEN a.status = 'REQUESTED' THEN 1 ELSE 0 END) as waiting,
        SUM(CASE WHEN a.assigned_staff_id IS NOT NULL THEN 1 ELSE 0 END) as assigned
        FROM appointments a
        JOIN properties p ON a.property_id = p.property_id
        WHERE MONTH(a.appointment_date) = ? AND YEAR(a.appointment_date) = ?
        GROUP BY p.state");
    $stmt_chart->bind_param("ii", $month, $year);
    $stmt_chart->execute();
    $res_chart = $stmt_chart->get_result();
    
    $labels = [];
    $waiting_data = [];
    $assigned_data = [];
    while ($row = $res_chart->fetch_assoc()) {
        $labels[] = $row['state'];
        $waiting_data[] = (int)$row['waiting'];
        $assigned_data[] = (int)$row['assigned'];
    }

    echo json_encode([
        'stats' => [
            'applied' => (int)$stats['waiting'] + (int)$stats['assigned'],
            'waiting' => (int)$stats['waiting'],
            'assigned' => (int)$stats['assigned']
        ],
        'chart' => [
            'labels' => $labels,
            'waiting' => $waiting_data,
            'assigned' => $assigned_data
        ]
    ]);
    exit;
}

$res_prop = $conn->query("SELECT COUNT(*) as count FROM properties");
$total_prop = $res_prop->fetch_assoc()['count'];

$res_leads = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'REQUESTED'");
$total_leads = $res_leads->fetch_assoc()['count'];

$res_app = $conn->query("SELECT COUNT(*) as count FROM affordable_housing_applications WHERE status = 'PENDING_REVIEW'");
$total_app = $res_app->fetch_assoc()['count'];

$res_cust = $conn->query("SELECT COUNT(*) as count FROM accounts WHERE role = 'CUSTOMER'");
$total_cust = $res_cust->fetch_assoc()['count'];

include '../includes/header.php';
?>

<div class="container my-5 d-flex align-items-center gap-3">
    <img src="../SYS%20Property%20Catalog/SYS_Property_Holdings_Icon.jpeg" alt="SYS Property Logo" style="height: 48px; width: auto; border-radius: 6px; object-fit: contain;" class="shadow-sm">
    <h2 class="fw-bold mb-0">Global Administrator Dashboard</h2> 
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
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0">O2O Regional Lead Distribution</h4>
                        <div class="d-flex gap-2">
                            <select id="filterMonth" class="form-select form-select-sm w-auto">
                                <?php
                                $currentMonth = date('n');
                                for ($m = 1; $m <= 12; $m++) {
                                    $monthName = date('F', mktime(0, 0, 0, $m, 1));
                                    $selected = ($m == $currentMonth) ? 'selected' : '';
                                    echo "<option value=\"$m\" $selected>$monthName</option>";
                                }
                                ?>
                            </select>
                            <select id="filterYear" class="form-select form-select-sm w-auto">
                                <?php
                                $currentYear = date('Y');
                                for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++) {
                                    $selected = ($y == $currentYear) ? 'selected' : '';
                                    echo "<option value=\"$y\" $selected>$y</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <canvas id="leadsChart" height="150"></canvas>
                    <div class="mt-4 pt-3 border-top d-flex justify-content-around text-center" id="realTimeStats">
                        <div>
                            <h6 class="text-muted mb-1" id="appliedLabel">Applied for Appointments</h6>
                            <h4 class="fw-bold text-dark mb-0" id="statApplied">0</h4>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1" id="waitingLabel">Waiting for Assignment</h6>
                            <h4 class="fw-bold text-primary mb-0" id="statWaiting">0</h4>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1" id="assignedLabel">Received Assignment</h6>
                            <h4 class="fw-bold text-success mb-0" id="statAssigned">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">Quick Actions</h4>
                    <div class="d-grid gap-3">
                        <a href="properties.php" class="btn btn-dark btn-lg fw-bold">Manage Inventory</a>
                        <a href="appointments.php" class="btn btn-primary btn-lg fw-bold">Assign Leads</a>
                        <a href="lucky_draw.php" class="btn btn-success btn-lg fw-bold">Execute Lucky Draw</a>
                        <a href="affordable_approvals.php" class="btn btn-warning btn-lg fw-bold text-dark">View Approvals Log</a>
                        <a href="user.php" class="btn btn-outline-dark btn-lg fw-bold">Manage Users</a>
                        <a href="business_reports.php" class="btn btn-info btn-lg fw-bold text-white">Generate Business Reports</a>
                        <a href="pdpa_management.php" class="btn btn-danger btn-lg fw-bold">PDPA Document Management</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('leadsChart').getContext('2d');
    let leadsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Waiting for Assignment',
                    data: [],
                    backgroundColor: 'rgba(13, 110, 253, 0.8)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Received Assignment',
                    data: [],
                    backgroundColor: 'rgba(25, 135, 84, 0.8)',
                    borderColor: 'rgba(25, 135, 84, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    stacked: true,
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });

    function loadChartData() {
        const monthSelect = document.getElementById('filterMonth');
        const month = monthSelect.value;
        const monthText = monthSelect.options[monthSelect.selectedIndex].text;
        const year = document.getElementById('filterYear').value;

        fetch(`dashboard.php?ajax_chart=1&month=${month}&year=${year}`)
            .then(response => response.json())
            .then(data => {
                // Update stats
                document.getElementById('statApplied').textContent = data.stats.applied;
                document.getElementById('statWaiting').textContent = data.stats.waiting;
                document.getElementById('statAssigned').textContent = data.stats.assigned;

                // Update labels
                document.getElementById('appliedLabel').innerHTML = `Applied for Appointments<br>(${monthText} ${year})`;
                document.getElementById('waitingLabel').innerHTML = `Waiting for Assignment<br>(${monthText} ${year})`;
                document.getElementById('assignedLabel').innerHTML = `Received Assignment<br>(${monthText} ${year})`;

                // Update chart
                leadsChart.data.labels = data.chart.labels;
                leadsChart.data.datasets[0].data = data.chart.waiting;
                leadsChart.data.datasets[1].data = data.chart.assigned;
                leadsChart.update();
            });
    }

    document.getElementById('filterMonth').addEventListener('change', loadChartData);
    document.getElementById('filterYear').addEventListener('change', loadChartData);

    // Initial load
    loadChartData();
</script>

<?php include '../includes/footer.php'; ?>
