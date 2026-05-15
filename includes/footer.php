<footer class="bg-dark text-light py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="text-uppercase fw-bold mb-3">SYS Property Holdings</h5>
                <p>Empowering first-time homebuyers in Malaysia through a seamless online-to-offline real estate journey. Discover, apply, and own your dream home today.</p>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="text-uppercase fw-bold mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-light text-decoration-none">Property Catalog</a></li>
                    <li><a href="#" class="text-light text-decoration-none">RMMJ & RSKU Applications</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Loan Calculator</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Privacy Policy (PDPA)</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="text-uppercase fw-bold mb-3">Contact Info</h5>
                <p><i class="fas fa-map-marker-alt me-2"></i> SYS HQ, Johor Bahru, Malaysia</p>
                <p><i class="fas fa-phone me-2"></i> +60 7-123 4567</p>
                <p><i class="fas fa-envelope me-2"></i> hello@sysproperty.com.my</p>
            </div>
        </div>
        <hr class="bg-secondary">
        <div class="text-center small text-muted">
            &copy; <?php echo date('Y'); ?> SYS Property Holdings. All Rights Reserved.
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
