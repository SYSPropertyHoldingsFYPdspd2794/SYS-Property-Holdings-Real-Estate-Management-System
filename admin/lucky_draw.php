<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

protect_admin_page('ADMIN', $conn);

$account_id = $_SESSION['account_id'];
$alert = '';
$pre_winners_json = '[]';
$selected_property_id = 0;
$draw_limit = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['execute_real_draw'])) {
    $prop_id = (int)$_POST['property_id'];
    $winners_array = json_decode($_POST['winners_data'], true);
    if (!empty($winners_array)) {
        foreach ($winners_array as $winner_id) {
            $stmt = $conn->prepare("UPDATE affordable_housing_applications SET status = 'WINNER' WHERE application_id = ? AND property_id = ?");
            $stmt->bind_param("ii", $winner_id, $prop_id);
            $stmt->execute();

            $log_stmt = $conn->prepare("INSERT INTO audit_logs (account_id, action_type, entity_type, entity_id) VALUES (?, 'LUCKY_DRAW_EXECUTED', 'application_id', ?)");
            $log_stmt->bind_param("ii", $account_id, $winner_id);
            $log_stmt->execute();
        }
        $alert = '<div class="alert alert-success fw-bold">Lucky Draw executed and records synchronized successfully.</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fetch_pool'])) {
    $selected_property_id = (int)$_POST['property_id'];
    $draw_limit = (int)$_POST['draw_limit'];

    $pool_stmt = $conn->prepare("SELECT aha.application_id, c.full_name, c.dependents_count, c.monthly_income FROM affordable_housing_applications aha JOIN customers c ON aha.customer_id = c.customer_id WHERE aha.property_id = ? AND aha.status = 'APPROVED_FOR_DRAW' ORDER BY c.dependents_count DESC, c.monthly_income ASC");
    $pool_stmt->bind_param("i", $selected_property_id);
    $pool_stmt->execute();
    $pool_result = $pool_stmt->get_result();

    $calculated_winners = [];
    $count = 0;
    while ($row = $pool_result->fetch_assoc()) {
        if ($count < $draw_limit) {
            $calculated_winners[] = $row;
            $count++;
        }
    }
    $pre_winners_json = json_encode($calculated_winners);
}

$properties = $conn->query("SELECT property_id, project_name, property_code FROM properties WHERE is_affordable = 1 AND status = 'ACTIVE'");
$winners = $conn->query("SELECT aha.application_id, c.full_name, c.monthly_income, p.project_name, p.state FROM affordable_housing_applications aha JOIN customers c ON aha.customer_id = c.customer_id JOIN properties p ON aha.property_id = p.property_id WHERE aha.status = 'WINNER' ORDER BY aha.application_id DESC");

include '../includes/header.php';
?>

<div class="container mt-5">
    <?php echo $alert; ?>
    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">Configure Scheme Draw Engine</h4>
                    <form method="POST">
                        <input type="hidden" name="fetch_pool" value="1">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Target Affordable Project</label>
                            <select name="property_id" class="form-select" required>
                                <option value="">Select Government Housing...</option>
                                <?php while ($p = $properties->fetch_assoc()): ?>
                                    <option value="<?php echo $p['property_id']; ?>" <?php echo $selected_property_id === (int)$p['property_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars(($p['property_code'] ?? '') . ' - ' . $p['project_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Winner Cap Limits</label>
                            <input type="number" name="draw_limit" class="form-control" min="1" value="<?php echo $draw_limit > 0 ? $draw_limit : ''; ?>" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold btn-lg">Initialize Randomization Sequence</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">Live Dynamic System Winners Registry</h4>
                    <div class="table-responsive">
                        <table id="winnersTable" class="table table-striped align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>App ID</th>
                                    <th>Winner Name</th>
                                    <th>Income</th>
                                    <th>Property Scheme</th>
                                    <th>State</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($w = $winners->fetch_assoc()): ?>
                                    <tr>
                                        <td>WIN-<?php echo str_pad($w['application_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                        <td class="fw-bold text-success"><?php echo htmlspecialchars($w['full_name']); ?></td>
                                        <td>RM <?php echo number_format($w['monthly_income'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($w['project_name']); ?></td>
                                        <td><span class="badge bg-primary"><?php echo htmlspecialchars($w['state']); ?></span></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="wheelModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-0 shadow-lg">
            <div class="modal-body text-center p-5">
                <h3 class="fw-bold mb-4 text-warning">SYS Algorithmic Allocation Wheel</h3>
                <div class="position-relative d-inline-block mb-4">
                    <div style="position: absolute; top: -15px; left: 50%; transform: translateX(-50%); width: 0; height: 0; border-left: 15px solid transparent; border-right: 15px solid transparent; border-top: 25px solid #ffc107; z-index: 10;"></div>
                    <canvas id="wheelCanvas" width="360" height="360" style="background:#222; border-radius:50%; border: 8px solid #333; box-shadow: 0 0 20px rgba(0,0,0,0.5);"></canvas>
                </div>
                <h4 id="wheelStatus" class="fw-bold text-info mb-4">Preparing Canvas Metrics...</h4>
                <button type="button" id="spinBtn" class="btn btn-warning btn-lg fw-bold px-5 text-dark">Spin Wheel</button>
                <form id="hiddenWinnersForm" method="POST" class="d-none">
                    <input type="hidden" name="execute_real_draw" value="1">
                    <input type="hidden" name="property_id" value="<?php echo $selected_property_id; ?>">
                    <input type="hidden" id="winnersDataInput" name="winners_data" value="">
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#winnersTable').DataTable({
            "order": [[0, "desc"]]
        });

        const preWinners = <?php echo $pre_winners_json; ?>;
        if (preWinners.length > 0) {
            const wheelModal = new bootstrap.Modal(document.getElementById('wheelModal'));
            wheelModal.show();

            const canvas = document.getElementById('wheelCanvas');
            const ctx = canvas.getContext('2d');
            const spinBtn = document.getElementById('spinBtn');
            const statusText = document.getElementById('wheelStatus');

            let sectors = preWinners.map(w => ({
                id: w.application_id,
                label: w.full_name.substring(0, 12)
            }));

            if(sectors.length === 1) {
                sectors.push({id: 0, label: "Evaluating Pool"});
            }

            let currentWinnersSubmitted = [];
            let currentWinnerIndex = 0;
            let angle = 0;

            function drawWheel() {
                const numSectors = sectors.length;
                const arc = 2 * Math.PI / numSectors;
                ctx.clearRect(0, 0, 360, 360);
                
                for (let i = 0; i < numSectors; i++) {
                    ctx.save();
                    ctx.beginPath();
                    ctx.moveTo(180, 180);
                    ctx.arc(180, 180, 170, arc * i + angle, arc * (i + 1) + angle);
                    ctx.fillStyle = i % 2 === 0 ? '#198754' : '#0dcaf0';
                    ctx.fill();
                    ctx.lineWidth = 2;
                    ctx.strokeStyle = '#222';
                    ctx.stroke();

                    ctx.translate(180, 180);
                    ctx.rotate(arc * (i + 0.5) + angle);
                    ctx.fillStyle = '#fff';
                    ctx.font = 'bold 12px Arial';
                    ctx.textAlign = 'right';
                    ctx.fillText(sectors[i].label, 150, 5);
                    ctx.restore();
                }
            }

            drawWheel();
            statusText.innerText = "Next Draw Sequence Primed!";

            spinBtn.addEventListener('click', () => {
                if (currentWinnerIndex >= preWinners.length) return;
                
                spinBtn.disabled = true;
                statusText.innerText = "Spinning System Real-Time Matrix...";
                
                const targetWinnerId = preWinners[currentWinnerIndex].application_id;
                let targetSectorIndex = sectors.findIndex(s => s.id === targetWinnerId);
                
                const numSectors = sectors.length;
                const arc = 2 * Math.PI / numSectors;
                
                const targetAngle = 1.5 * Math.PI - (arc * (targetSectorIndex + 0.5));
                const loops = 4 * 2 * Math.PI;
                const finalAngle = loops + targetAngle;
                
                let startTimestamp = null;
                const duration = 4000;

                function animateWheel(timestamp) {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const elapsed = timestamp - startTimestamp;
                    const progress = Math.min(elapsed / duration, 1);
                    
                    const easeOut = 1 - Math.pow(1 - progress, 3);
                    angle = easeOut * finalAngle;
                    
                    drawWheel();

                    if (progress < 1) {
                        requestAnimationFrame(animateWheel);
                    } else {
                        const trueWinnerName = preWinners[currentWinnerIndex].full_name;
                        statusText.innerText = "WINNER ACQUIRED: " + trueWinnerName;
                        currentWinnersSubmitted.push(targetWinnerId);
                        
                        setTimeout(() => {
                            alert("CONGRATULATIONS!\nWinner selected for system allocation:\n" + trueWinnerName);
                            
                            currentWinnerIndex++;
                            if (currentWinnerIndex < preWinners.length) {
                                sectors = sectors.filter(s => s.id !== targetWinnerId);
                                if(sectors.length === 1) {
                                    sectors.push({id: 0, label: "Evaluating Pool"});
                                }
                                angle = angle % (2 * Math.PI);
                                drawWheel();
                                spinBtn.disabled = false;
                                statusText.innerText = "Next Draw Sequence Primed!";
                            } else {
                                statusText.innerText = "All Units Allocated! Synchronizing Matrix...";
                                document.getElementById('winnersDataInput').value = JSON.stringify(currentWinnersSubmitted);
                                setTimeout(() => {
                                    document.getElementById('hiddenWinnersForm').submit();
                                }, 1500);
                            }
                        }, 500);
                    }
                }
                requestAnimationFrame(animateWheel);
            });
        }
    });
</script>

<?php include '../includes/footer.php'; ?>