document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    });

    // Password strength indicator (for signup page)
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('strength-bar');
    
    if (passwordInput && strengthBar) {
        passwordInput.addEventListener('input', function() {
            const strength = calculatePasswordStrength(this.value);
            updateStrengthBar(strength);
        });
    }

    function calculatePasswordStrength(password) {
        let strength = 0;
        
        // Length check
        if (password.length >= 8) strength += 1;
        if (password.length >= 12) strength += 1;
        
        // Complexity checks
        if (/[A-Z]/.test(password)) strength += 1;
        if (/[0-9]/.test(password)) strength += 1;
        if (/[^A-Za-z0-9]/.test(password)) strength += 1;
        
        return Math.min(strength, 5); // Max strength of 5
    }

    function updateStrengthBar(strength) {
        const colors = ['#ef233c', '#f77f00', '#fcbf49', '#90be6d', '#43aa8b'];
        const width = (strength / 5) * 100;
        strengthBar.style.width = `${width}%`;
        strengthBar.style.backgroundColor = colors[strength - 1];
    }
});