/**
 * ICHRI Tunisia - Authentication JavaScript
 * Login and registration functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if already logged in
    if (API.getToken() && !window.location.pathname.includes('register')) {
        const redirectUrl = HELPERS.getQueryParam('redirect') || 'dashboard.html';
        window.location.href = redirectUrl;
    }

    // Setup login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        setupLoginForm(loginForm);
    }

    // Setup register form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        setupRegisterForm(registerForm);
    }
});

/**
 * Setup Login Form
 */
function setupLoginForm(form) {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const rememberMe = document.getElementById('rememberMe').checked;

        // Validate
        if (!HELPERS.validateEmail(email)) {
            HELPERS.showToast('Email invalide', 'danger');
            return;
        }

        if (password.length < 6) {
            HELPERS.showToast('Le mot de passe doit contenir au moins 6 caractères', 'danger');
            return;
        }

        // Submit
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Connexion...';

            const response = await API.login(email, password);

            if (response && response.token) {
                // Store token and user
                API.setToken(response.token);
                API.setUser(response.user);

                HELPERS.showToast('Connexion réussie !', 'success');

                // Redirect
                const redirectUrl = HELPERS.getQueryParam('redirect') || 'dashboard.html';
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 1000);
            } else {
                throw new Error('Réponse invalide du serveur');
            }
        } catch (error) {
            console.error('Login error:', error);
            HELPERS.showToast(error.message || 'Email ou mot de passe incorrect', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

/**
 * Setup Register Form
 */
function setupRegisterForm(form) {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const password = document.getElementById('password').value;
        const passwordConfirmation = document.getElementById('passwordConfirmation').value;
        const isVendor = document.getElementById('isVendor').checked;
        const acceptTerms = document.getElementById('acceptTerms').checked;

        // Validate
        if (!firstName || !lastName) {
            HELPERS.showToast('Veuillez entrer votre nom complet', 'danger');
            return;
        }

        if (!HELPERS.validateEmail(email)) {
            HELPERS.showToast('Email invalide', 'danger');
            return;
        }

        if (!phone) {
            HELPERS.showToast('Veuillez entrer votre numéro de téléphone', 'danger');
            return;
        }

        if (password.length < 8) {
            HELPERS.showToast('Le mot de passe doit contenir au moins 8 caractères', 'danger');
            return;
        }

        if (password !== passwordConfirmation) {
            HELPERS.showToast('Les mots de passe ne correspondent pas', 'danger');
            return;
        }

        if (!acceptTerms) {
            HELPERS.showToast('Veuillez accepter les conditions d\'utilisation', 'danger');
            return;
        }

        // Submit
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Inscription...';

            const userData = {
                first_name: firstName,
                last_name: lastName,
                email: email,
                phone: phone,
                password: password,
                password_confirmation: passwordConfirmation,
                role: isVendor ? 'vendor' : 'customer'
            };

            const response = await API.register(userData);

            if (response && response.token) {
                // Store token and user
                API.setToken(response.token);
                API.setUser(response.user);

                HELPERS.showToast('Inscription réussie ! Bienvenue sur ICHRI Tunisia', 'success');

                // Redirect
                setTimeout(() => {
                    if (isVendor) {
                        window.location.href = 'vendor/dashboard.html';
                    } else {
                        window.location.href = 'dashboard.html';
                    }
                }, 1500);
            } else {
                throw new Error('Réponse invalide du serveur');
            }
        } catch (error) {
            console.error('Register error:', error);
            HELPERS.showToast(error.message || 'Erreur lors de l\'inscription', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

/**
 * Toggle Password Visibility
 */
function togglePassword(fieldId = 'password') {
    const passwordInput = document.getElementById(fieldId);
    const toggleIcon = document.getElementById(fieldId === 'password' ? 'toggleIcon' : `toggleIcon${fieldId.charAt(0).toUpperCase() + fieldId.slice(1)}`);

    if (!passwordInput) return;

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        if (toggleIcon) {
            toggleIcon.classList.remove('bi-eye');
            toggleIcon.classList.add('bi-eye-slash');
        }
    } else {
        passwordInput.type = 'password';
        if (toggleIcon) {
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
        }
    }
}

/**
 * Social Login
 */
function socialLogin(provider) {
    HELPERS.showToast(`Connexion via ${provider} en cours de développement`, 'info');
    // In production, this would redirect to OAuth provider
    // window.location.href = `${CONFIG.API_URL}/auth/${provider}`;
}

// Export functions for global use
window.togglePassword = togglePassword;
window.socialLogin = socialLogin;
