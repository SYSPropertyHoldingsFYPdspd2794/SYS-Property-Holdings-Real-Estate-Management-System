<?php
$email_change_form_action = basename($_SERVER['PHP_SELF']);
?>
<div class="modal fade" id="emailChangeOtpModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fas fa-envelope-circle-check me-2 text-warning"></i>New Email OTP
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-dark">
                <p class="text-muted small mb-3">Enter the 6-digit OTP sent to your new email. Your old email must also approve the request before the change is completed.</p>
                <form method="POST" action="<?php echo htmlspecialchars($email_change_form_action); ?>" id="emailChangeOtpForm">
                    <input type="hidden" name="email_change_action" value="verify_otp">
                    <label class="form-label fw-bold">Verification Code</label>
                    <input type="text" name="email_change_otp" class="form-control form-control-lg text-center fw-bold" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" autocomplete="one-time-code" required>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-between">
                <form method="POST" action="<?php echo htmlspecialchars($email_change_form_action); ?>" class="m-0">
                    <input type="hidden" name="email_change_action" value="resend_otp">
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="fas fa-rotate-right me-1"></i>Resend Code
                    </button>
                </form>
                <button type="submit" form="emailChangeOtpForm" class="btn btn-primary fw-bold">
                    <i class="fas fa-check me-1"></i>Verify OTP
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="emailChangeConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fas fa-shield-alt me-2 text-warning"></i>Confirm Email Change
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-dark">
                <p class="mb-2">Changing your email requires two checks:</p>
                <ul class="small text-muted mb-0">
                    <li>Your old email must approve or reject the request.</li>
                    <li>Your new email must pass OTP verification.</li>
                </ul>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary fw-bold" id="emailChangeContinueButton">Continue</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const profileForm = document.querySelector('[data-email-change-form="1"]');
    const emailInput = profileForm ? profileForm.querySelector('input[name="email"]') : null;
    const continueButton = document.getElementById('emailChangeContinueButton');
    const confirmModalElement = document.getElementById('emailChangeConfirmModal');
    const otpModalElement = document.getElementById('emailChangeOtpModal');
    const showOtpModal = <?php echo !empty($show_email_change_otp_modal) ? 'true' : 'false'; ?>;
    let bypassEmailChangeConfirm = false;
    let pendingSubmitter = null;

    if (profileForm && emailInput && confirmModalElement && continueButton && window.bootstrap) {
        const confirmModal = new bootstrap.Modal(confirmModalElement);
        const originalEmail = (emailInput.dataset.originalEmail || '').trim().toLowerCase();

        profileForm.addEventListener('submit', function (event) {
            const nextEmail = (emailInput.value || '').trim().toLowerCase();
            if (!bypassEmailChangeConfirm && originalEmail !== '' && nextEmail !== originalEmail) {
                event.preventDefault();
                pendingSubmitter = event.submitter || null;
                confirmModal.show();
            }
        });

        continueButton.addEventListener('click', function () {
            bypassEmailChangeConfirm = true;
            confirmModal.hide();
            if (pendingSubmitter && pendingSubmitter.name) {
                const hiddenSubmitter = document.createElement('input');
                hiddenSubmitter.type = 'hidden';
                hiddenSubmitter.name = pendingSubmitter.name;
                hiddenSubmitter.value = pendingSubmitter.value;
                profileForm.appendChild(hiddenSubmitter);
            }
            profileForm.submit();
        });
    }

    if (showOtpModal && otpModalElement && window.bootstrap) {
        new bootstrap.Modal(otpModalElement).show();
    }
});
</script>
