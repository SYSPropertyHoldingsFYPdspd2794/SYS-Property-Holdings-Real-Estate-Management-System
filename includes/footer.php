<?php
if (!isset($root_prefix)) {
    $current_folder = basename(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')));
    $root_prefix = in_array($current_folder, ['admin', 'customer', 'staff'], true) ? '../' : '';
}
?>
<footer class="bg-dark text-light py-5 mt-5 border-top border-secondary border-opacity-25" style="background-color: #111418 !important;">
    <div class="container py-3">
        <div class="row g-4">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <h5 class="text-uppercase fw-bold mb-3 text-white tracking-wider"><i class="fas fa-hotel text-warning me-2"></i>SYS Property</h5>
                <p class="text-muted small lh-lg mb-4">SYS Property Holdings is a premier integrated real estate community developer in Malaysia. We engineer digital O2O solutions to simplify first-time homebuyer journeys, unlocking transparent housing data across multiple states.</p>
                <div class="d-flex gap-3 social-links">
                    <a href="#" class="btn btn-sm btn-outline-secondary rounded-circle text-white" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-secondary rounded-circle text-white" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-secondary rounded-circle text-white" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-secondary rounded-circle text-white" title="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <div class="col-md-4 col-lg-2 mb-4 mb-md-0">
                <h6 class="text-uppercase fw-bold mb-3 text-warning tracking-wider small">Portfolios</h6>
                <ul class="list-unstyled footer-menu-links font-monospace small">
                    <li class="mb-2"><a href="<?php echo htmlspecialchars($root_prefix . 'properties.php'); ?>" class="text-muted text-decoration-none d-block py-1"><i class="fas fa-chevron-right me-1 text-secondary"></i> Market Houses</a></li>
                    <li class="mb-2"><a href="<?php echo htmlspecialchars($root_prefix . 'properties.php?filter_type=AFFORDABLE'); ?>" class="text-muted text-decoration-none d-block py-1"><i class="fas fa-chevron-right me-1 text-secondary"></i> Affordable Units</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none d-block py-1"><i class="fas fa-chevron-right me-1 text-secondary"></i> Landed Terrace</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none d-block py-1"><i class="fas fa-chevron-right me-1 text-secondary"></i> Luxury Bungalows</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none d-block py-1"><i class="fas fa-chevron-right me-1 text-secondary"></i> Commercial Hubs</a></li>
                </ul>
            </div>

            <div class="col-md-4 col-lg-3 mb-4 mb-md-0">
                <h6 class="text-uppercase fw-bold mb-3 text-warning tracking-wider small">Governance & Links</h6>
                <ul class="list-unstyled footer-menu-links font-monospace small">
                    <li class="mb-2"><a href="<?php echo htmlspecialchars($root_prefix . 'about_us.php'); ?>" class="text-muted text-decoration-none d-block py-1"><i class="fas fa-shield-alt me-1 text-secondary"></i> Corporate Identity</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none d-block py-1"><i class="fas fa-calculator me-1 text-secondary"></i> Loan Estimator Matrix</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none d-block py-1"><i class="fas fa-user-shield me-1 text-secondary"></i> PDPA Privacy 2010</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none d-block py-1"><i class="fas fa-file-contract me-1 text-secondary"></i> Terms of Use</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none d-block py-1"><i class="fas fa-gavel me-1 text-secondary"></i> HDA Regulation Policy</a></li>
                </ul>
            </div>

            <div class="col-md-4 col-lg-3">
                <h6 class="text-uppercase fw-bold mb-3 text-warning tracking-wider small">HQ Contact Center</h6>
                <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt text-danger me-2 fa-fw"></i> SYS Tower HQ, Menara Iskandar, Johor Bahru, Malaysia</p>
                <p class="text-muted small mb-2"><i class="fas fa-phone text-success me-2 fa-fw"></i> +60 7-123 4567</p>
                <p class="text-muted small mb-3"><i class="fas fa-envelope text-info me-2 fa-fw"></i> solutions@sysproperty.com.my</p>
                <div class="p-3 bg-secondary bg-opacity-10 border border-secondary border-opacity-25 rounded-3">
                    <small class="text-white d-block fw-bold mb-1"><i class="far fa-clock text-warning me-1"></i> Customer Desk Hours</small>
                    <small class="text-muted d-block font-monospace">Mon — Fri: 09:00 AM - 06:00 PM</small>
                    <small class="text-muted d-block font-monospace">Sat — Sun: 10:00 AM - 04:00 PM</small>
                </div>
            </div>
        </div>
        
        <hr class="bg-secondary opacity-25 my-4">
        
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 text-muted small">
            <div>&copy; <?php echo date('Y'); ?> SYS Property Holdings Group. All Rights Reserved.</div>
            <div class="font-monospace text-uppercase" style="font-size: 10px; letter-spacing: 1px;">CIDB Grade G7 Corporate Registry / M&E Compliant</div>
        </div>
    </div>
</footer>

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
</body>
</html>
