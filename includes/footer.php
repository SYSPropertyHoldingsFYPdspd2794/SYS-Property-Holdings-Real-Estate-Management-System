<?php
if (!isset($root_prefix)) {
    $current_folder = basename(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')));
    $root_prefix = in_array($current_folder, ['admin', 'customer', 'staff'], true) ? '../' : '';
}

$footer_catalog_path = $root_prefix . 'login.php';
if (isset($_SESSION['role'])) {
    $footer_catalog_path = $root_prefix . 'properties.php';

    if ($_SESSION['role'] === 'CUSTOMER') {
        $footer_catalog_path = $root_prefix . 'customer/properties.php';
    }
}

$footer_portfolio_links = [
    'market' => $footer_catalog_path,
    'terrace' => $footer_catalog_path,
    'bungalow' => $footer_catalog_path,
    'commercial' => $footer_catalog_path,
];

if (isset($_SESSION['role'])) {
    $footer_portfolio_links['terrace'] = $footer_catalog_path . '?filter_type=TERRACE';
    $footer_portfolio_links['bungalow'] = $footer_catalog_path . '?filter_type=BUNGALOW';
    $footer_portfolio_links['commercial'] = $footer_catalog_path . '?filter_type=COMMERCIAL';
}
?>
<footer class="luxury-footer text-light py-5 mt-5 border-top border-secondary border-opacity-25">
    <div class="container py-3">
        <div class="row g-4">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <h5 class="text-uppercase fw-bold mb-3 text-white tracking-wider d-flex align-items-center gap-2">
                    <img src="<?php echo htmlspecialchars($root_prefix . 'SYS%20Property%20Catalog/SYS_Property_Holdings_Icon.jpeg'); ?>" alt="SYS Property Holdings Logo" style="height: 38px; width: auto; border-radius: 6px; object-fit: contain;">
                    SYS Property
                </h5>
                <p class="text-white small lh-lg mb-4">SYS Property Holdings is a premier integrated real estate community developer in Malaysia. We engineer digital O2O solutions to simplify first-time homebuyer journeys, unlocking transparent housing data across multiple states.</p>
            </div>
            
            <div class="col-md-4 col-lg-2 mb-4 mb-md-0">
                <h6 class="text-uppercase fw-bold mb-3 text-warning tracking-wider small">Portfolios</h6>
                <ul class="list-unstyled footer-menu-links font-monospace small">
                    <li class="mb-2"><a href="<?php echo htmlspecialchars($footer_portfolio_links['market']); ?>" class="text-white text-decoration-none d-block py-1"><i class="fas fa-chevron-right me-1 text-light"></i> Market Houses</a></li>
                    <li class="mb-2"><a href="<?php echo htmlspecialchars($footer_portfolio_links['terrace']); ?>" class="text-white text-decoration-none d-block py-1"><i class="fas fa-chevron-right me-1 text-light"></i> Landed Terrace</a></li>
                    <li class="mb-2"><a href="<?php echo htmlspecialchars($footer_portfolio_links['bungalow']); ?>" class="text-white text-decoration-none d-block py-1"><i class="fas fa-chevron-right me-1 text-light"></i> Luxury Bungalows</a></li>
                    <li class="mb-2"><a href="<?php echo htmlspecialchars($footer_portfolio_links['commercial']); ?>" class="text-white text-decoration-none d-block py-1"><i class="fas fa-chevron-right me-1 text-light"></i> Commercial Hubs</a></li>
                </ul>
            </div>

            <div class="col-md-4 col-lg-3 mb-4 mb-md-0">
                <h6 class="text-uppercase fw-bold mb-3 text-warning tracking-wider small">Governance & Links</h6>
                <ul class="list-unstyled footer-menu-links font-monospace small">
                    <li class="mb-2"><a href="<?php echo htmlspecialchars($root_prefix . 'about_us.php'); ?>" class="text-white text-decoration-none d-block py-1"><i class="fas fa-shield-alt me-1 text-light"></i> Corporate Identity</a></li>
                    <li class="mb-2"><a href="<?php echo htmlspecialchars($root_prefix . 'financial_planner.php'); ?>" class="text-white text-decoration-none d-block py-1"><i class="fas fa-calculator me-1 text-light"></i> Loan Estimator Matrix</a></li>
                    <li class="mb-2"><a href="<?php echo htmlspecialchars($root_prefix . 'government_housing.php'); ?>" class="text-white text-decoration-none d-block py-1"><i class="fas fa-gavel me-1 text-light"></i> HDA Regulation Policy</a></li>
                </ul>
            </div>

            <div class="col-md-4 col-lg-3">
                <h6 class="text-uppercase fw-bold mb-3 text-warning tracking-wider small">HQ Contact Center</h6>
                <p class="text-white small mb-2"><i class="fas fa-map-marker-alt text-danger me-2 fa-fw"></i> SYS Tower HQ, Menara Iskandar, Johor Bahru, Malaysia</p>
                <p class="text-white small mb-2"><i class="fas fa-phone text-success me-2 fa-fw"></i><a href="tel:+6071234567" class="text-white text-decoration-none">+60 7-123 4567</a></p>
                <p class="text-white small mb-3"><i class="fas fa-envelope text-info me-2 fa-fw"></i><a href="mailto:solutions@sysproperty.com.my" class="text-white text-decoration-none">solutions@sysproperty.com.my</a></p>
                <div class="p-3 bg-secondary bg-opacity-10 border border-secondary border-opacity-25 rounded-3">
                    <small class="text-white d-block fw-bold mb-1"><i class="far fa-clock text-warning me-1"></i> Customer Desk Hours</small>
                    <small class="text-white d-block font-monospace">Mon - Fri: 09:00 AM - 06:00 PM</small>
                    <small class="text-white d-block font-monospace">Sat - Sun: 10:00 AM - 04:00 PM</small>
                </div>
            </div>
        </div>
        
        <hr class="bg-secondary opacity-25 my-4">
        
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 text-white small">
            <div>&copy; <?php echo date('Y'); ?> SYS Property Holdings Group. All Rights Reserved.</div>
            <div class="font-monospace text-uppercase" style="font-size: 10px; letter-spacing: 1px;">CIDB Grade G7 Corporate Registry / M&E Compliant</div>
        </div>
    </div>
</footer>

<div class="modal fade" id="globalConfirmModal" tabindex="-1" aria-labelledby="globalConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" id="globalConfirmModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Action
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0 text-dark" id="globalConfirmModalMessage">Are you sure you want to continue?</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger fw-bold" id="globalConfirmModalButton">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * password: The string from the input field
 * prefix: A unique string (e.g., 'adm_') to identify which checklist to update
 */
function checkPasswordRealtime(password, prefix) {
    const requirements = [
        { id: prefix + 'length', met: password.length >= 8 },
        { id: prefix + 'upper',  met: /[A-Z]/.test(password) },
        { id: prefix + 'lower',  met: /[a-z]/.test(password) },
        { id: prefix + 'number', met: /[0-9]/.test(password) },
        { id: prefix + 'symbol', met: /[^A-Za-z0-9]/.test(password) }
    ];

    requirements.forEach(req => {
        const element = document.getElementById(req.id);
        if (element) {
            const icon = element.querySelector('i');
            if (req.met) {
                element.className = 'text-success fw-bold py-1';
                icon.className = 'fas fa-check-circle me-2';
            } else {
                element.className = 'text-danger py-1';
                icon.className = 'fas fa-times-circle me-2';
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[type="password"]').forEach(function (input, index) {
        if (input.closest('.password-toggle-group')) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'input-group password-toggle-group';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline-secondary password-toggle-btn';
        button.setAttribute('aria-label', 'Show password');
        button.setAttribute('aria-pressed', 'false');
        button.setAttribute('title', 'Show password');

        const icon = document.createElement('i');
        icon.className = 'fas fa-eye';
        icon.setAttribute('aria-hidden', 'true');
        button.appendChild(icon);

        if (!input.id) {
            input.id = 'passwordField' + (index + 1);
        }
        button.setAttribute('aria-controls', input.id);

        button.addEventListener('click', function () {
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
            button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            button.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            button.setAttribute('title', isHidden ? 'Hide password' : 'Show password');
        });

        wrapper.appendChild(button);
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const confirmModalElement = document.getElementById('globalConfirmModal');
    const confirmTitle = document.getElementById('globalConfirmModalLabel');
    const confirmMessage = document.getElementById('globalConfirmModalMessage');
    const confirmButton = document.getElementById('globalConfirmModalButton');
    const confirmModal = confirmModalElement ? new bootstrap.Modal(confirmModalElement) : null;
    let pendingAction = null;

    window.showConfirmModal = function (options) {
        if (!confirmModal || !confirmTitle || !confirmMessage || !confirmButton) {
            if (typeof options.onConfirm === 'function') {
                options.onConfirm();
            }
            return;
        }

        confirmTitle.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + (options.title || 'Confirm Action');
        confirmMessage.textContent = options.message || 'Are you sure you want to continue?';
        confirmButton.textContent = options.confirmText || 'Confirm';
        confirmButton.className = 'btn fw-bold ' + (options.confirmClass || 'btn-danger');
        pendingAction = options.onConfirm || null;
        confirmModal.show();
    };

    confirmButton?.addEventListener('click', function () {
        const action = pendingAction;
        pendingAction = null;
        confirmModal?.hide();

        if (typeof action === 'function') {
            action();
        }
    });

    document.querySelectorAll('.confirm-action-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.confirmed === '1') {
                return;
            }

            event.preventDefault();
            const submitter = event.submitter;
            window.showConfirmModal({
                title: form.dataset.confirmTitle || 'Confirm Action',
                message: form.dataset.confirmMessage || 'Are you sure you want to continue?',
                confirmText: form.dataset.confirmButton || 'Confirm',
                onConfirm: function () {
                    if (submitter && submitter.name) {
                        const hiddenSubmitter = document.createElement('input');
                        hiddenSubmitter.type = 'hidden';
                        hiddenSubmitter.name = submitter.name;
                        hiddenSubmitter.value = submitter.value;
                        form.appendChild(hiddenSubmitter);
                    }

                    form.dataset.confirmed = '1';
                    form.submit();
                }
            });
        });
    });
});
</script>
</body>
</html>
