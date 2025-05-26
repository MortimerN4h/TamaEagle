// Register page specific functionality
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    // Toggle password visibility
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                this.textContent = 'Show';
            }
        });
    });

    // Form validation
    const form = document.querySelector('.register-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const username = document.getElementById('username');
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm-password');
            
            // Check if all fields are filled
            if (!username.value.trim()) {
                e.preventDefault();
                alert('Please enter a username');
                username.focus();
                return;
            }
            
            if (!email.value.trim()) {
                e.preventDefault();
                alert('Please enter an email');
                email.focus();
                return;
            }
            
            if (!password.value.trim()) {
                e.preventDefault();
                alert('Please enter a password');
                password.focus();
                return;
            }
            
            if (!confirmPassword.value.trim()) {
                e.preventDefault();
                alert('Please confirm your password');
                confirmPassword.focus();
                return;
            }
            
            // Check password match
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match');
                confirmPassword.focus();
                return;
            }
            
            // Check password length
            if (password.value.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long');
                password.focus();
                return;
            }
            
            // Check email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                email.focus();
                return;
            }
        });
    }

    // Real-time password confirmation validation
    const confirmPassword = document.getElementById('confirm-password');
    const password = document.getElementById('password');
    
    if (confirmPassword && password) {
        confirmPassword.addEventListener('input', function() {
            if (this.value && password.value && this.value !== password.value) {
                this.style.borderColor = '#ff4444';
            } else {
                this.style.borderColor = '';
            }
        });
    }
});
