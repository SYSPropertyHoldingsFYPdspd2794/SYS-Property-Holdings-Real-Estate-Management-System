<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'CUSTOMER') {
    header("Location: ../login.php");
    exit();
}
include '../includes/db_connect.php';

$account_id = $_SESSION['account_id'];

// Get appointments with remarks
$appt_stmt = $conn->prepare("SELECT a.*, p.project_name, s.full_name AS staff_name FROM appointments a JOIN properties p ON a.property_id = p.property_id LEFT JOIN staff s ON a.assigned_staff_id = s.staff_id WHERE a.customer_id = ? AND a.staff_remarks IS NOT NULL AND a.staff_remarks != '' AND a.customer_deleted_at IS NULL ORDER BY a.appointment_date DESC");
$appt_stmt->bind_param("i", $account_id);
$appt_stmt->execute();
$appt_feedbacks = $appt_stmt->get_result();

// Get applications with implied feedback
$app_stmt = $conn->prepare("SELECT ah.*, p.project_name, s.full_name AS staff_name FROM affordable_housing_applications ah JOIN properties p ON ah.property_id = p.property_id LEFT JOIN staff s ON ah.reviewed_by_staff_id = s.staff_id WHERE ah.customer_id = ? AND ah.status IN ('REJECTED', 'WINNER', 'APPROVED_FOR_DRAW') ORDER BY ah.application_date DESC");
$app_stmt->bind_param("i", $account_id);
$app_stmt->execute();
$app_feedbacks = $app_stmt->get_result();

$page_title = "Feedback Inbox";
include '../includes/header.php';
?>
<div class="container my-5" style="max-width: 800px;">
    <div class="d-flex align-items-center mb-5">
        <a href="track_status.php" class="btn btn-outline-secondary rounded-circle me-3 shadow-sm" style="width:40px; height:40px; display:flex; align-items:center; justify-content:center;"><i class="fas fa-arrow-left"></i></a>
        <h2 class="fw-bold m-0"><i class="fas fa-bell text-warning me-3"></i>Feedback Inbox</h2>
    </div>

    <?php if ($appt_feedbacks->num_rows === 0 && $app_feedbacks->num_rows === 0): ?>
        <div class="text-center py-5">
            <div class="mb-4 text-muted opacity-25">
                <i class="fas fa-inbox display-1"></i>
            </div>
            <h4 class="text-muted fw-bold">You have no new feedback.</h4>
            <p class="text-muted">Staff remarks and application updates will appear here.</p>
        </div>
    <?php endif; ?>

    <div class="list-group list-group-flush shadow-sm rounded-4 mb-5 border-0">
        <?php while ($row = $appt_feedbacks->fetch_assoc()): ?>
            <div class="list-group-item p-4 border-bottom">
                <div class="d-flex align-items-start gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fas fa-user-tie fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h5 class="fw-bold m-0"><?php echo htmlspecialchars($row['staff_name'] ?? 'System Staff'); ?></h5>
                            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i><?php echo date('d M Y', strtotime($row['appointment_date'])); ?></small>
                        </div>
                        <p class="text-muted small fw-bold mb-3 text-uppercase" style="letter-spacing: 0.05em;">Showroom Appointment: <?php echo htmlspecialchars($row['project_name']); ?></p>
                        <div class="bg-light p-3 rounded-3 border-start border-warning border-4 text-dark fs-6 shadow-sm">
                            <?php echo nl2br(htmlspecialchars($row['staff_remarks'])); ?>
                        </div>
                        <div class="mt-3">
                            <a href="adjust_appointment.php?type=appointment&id=<?php echo $row['appointment_id']; ?>" class="btn btn-sm btn-dark rounded-pill px-4 shadow-sm">View Appointment</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>

        <?php while ($row = $app_feedbacks->fetch_assoc()): ?>
            <div class="list-group-item p-4 border-bottom">
                <div class="d-flex align-items-start gap-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fas fa-home fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h5 class="fw-bold m-0"><?php echo htmlspecialchars($row['staff_name'] ?? 'Housing Officer'); ?></h5>
                            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i><?php echo date('d M Y', strtotime($row['application_date'])); ?></small>
                        </div>
                        <p class="text-muted small fw-bold mb-3 text-uppercase" style="letter-spacing: 0.05em;">Affordable Housing: <?php echo htmlspecialchars($row['project_name']); ?></p>
                        <div class="bg-light p-3 rounded-3 border-start border-success border-4 text-dark fs-6 shadow-sm">
                            <?php if ($row['status'] === 'REJECTED'): ?>
                                <span class="text-danger fw-bold"><i class="fas fa-times-circle me-1"></i>Application Disqualified</span><br>
                                Your application was carefully reviewed but currently disqualified based on the state's affordable housing income ceiling policies or document verification failure. You may try applying for other properties.
                            <?php elseif ($row['status'] === 'APPROVED_FOR_DRAW'): ?>
                                <span class="text-info fw-bold"><i class="fas fa-check-circle me-1"></i>Approved for Draw</span><br>
                                Your documents are verified and you are now enrolled in the ballot draw. We will notify you once the draw is completed.
                            <?php elseif ($row['status'] === 'WINNER'): ?>
                                <span class="text-success fw-bold"><i class="fas fa-trophy me-1"></i>Selected in Draw!</span><br>
                                Congratulations! You have been successfully selected in the ballot allocation pool. Our housing officers will contact you shortly for contract signing.
                            <?php endif; ?>
                        </div>
                        <div class="mt-3">
                            <a href="adjust_appointment.php?type=housing&id=<?php echo $row['application_id']; ?>" class="btn btn-sm btn-dark rounded-pill px-4 shadow-sm">View Application</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
