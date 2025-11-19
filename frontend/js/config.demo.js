/**
 * Configuration pour démarrage immédiat avec données de démo
 */

const CONFIG = {
    API_URL: 'http://localhost:8000/api',
    DEFAULT_LANG: 'fr',
    SUPPORTED_LANGS: ['fr', 'ar', 'en'],
    CURRENCY: 'TND',
    PRODUCTS_PER_PAGE: 12,
    ITEMS_PER_PAGE: 10,

    // Données de démo pour test offline
    DEMO_MODE: true,
    
    // Utilisateurs de test
    DEMO_USERS: {
        admin: { email: 'admin@ichri.tn', password: 'password', role: 'admin' },
        client: { email: 'client@ichri.tn', password: 'password', role: 'customer' },
        vendor: { email: 'vendor@ichri.tn', password: 'password', role: 'vendor' }
    },

    // Gouvernorats tunisiens
    GOVERNORATES: [
        'Tunis', 'Ariana', 'Ben Arous', 'Manouba',
        'Nabeul', 'Zaghouan', 'Bizerte',
        'Béja', 'Jendouba', 'Le Kef', 'Siliana',
        'Kairouan', 'Kasserine', 'Sidi Bouzid',
        'Sousse', 'Monastir', 'Mahdia', 'Sfax',
        'Gafsa', 'Tozeur', 'Kebili',
        'Gabès', 'Medenine', 'Tataouine'
    ],

    STORAGE_KEYS: {
        AUTH_TOKEN: '@ichri_token',
        USER_DATA: '@ichri_user',
        CART: '@ichri_cart',
        WISHLIST: '@ichri_wishlist',
        LANGUAGE: '@ichri_lang'
    }
};

const HELPERS = {
    formatPrice(price) {
        return new Intl.NumberFormat('fr-TN', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(price) + ' ' + CONFIG.CURRENCY;
    },

    formatDate(date) {
        return new Date(date).toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    },

    generateStars(rating) {
        const fullStars = Math.floor(rating);
        const halfStar = rating % 1 >= 0.5;
        const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);
        
        let stars = '';
        for (let i = 0; i < fullStars; i++) stars += '<i class="bi bi-star-fill text-warning"></i>';
        if (halfStar) stars += '<i class="bi bi-star-half text-warning"></i>';
        for (let i = 0; i < emptyStars; i++) stars += '<i class="bi bi-star text-warning"></i>';
        
        return stars;
    },

    truncate(text, length) {
        if (!text) return '';
        return text.length > length ? text.substring(0, length) + '...' : text;
    },

    calculateDiscount(oldPrice, newPrice) {
        if (!oldPrice || !newPrice) return 0;
        return Math.round(((oldPrice - newPrice) / oldPrice) * 100);
    },

    getImageUrl(path) {
        if (!path) return 'https://via.placeholder.com/400x300?text=ICHRI+Tunisia';
        if (path.startsWith('http')) return path;
        return CONFIG.API_URL.replace('/api', '') + '/storage/' + path;
    },

    validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    validatePhone(phone) {
        return /^(\+216)?[0-9]{8}$/.test(phone);
    },

    showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'danger' ? 'danger' : 'info'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        setTimeout(() => toast.remove(), 5000);
    },

    showLoading(container) {
        container.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>
        `;
    },

    showError(container, message) {
        container.innerHTML = `
            <div class="alert alert-danger text-center">
                <i class="bi bi-exclamation-triangle me-2"></i> ${message}
            </div>
        `;
    },

    showEmpty(container, message, icon = 'inbox') {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-${icon} display-1 text-muted"></i>
                <p class="text-muted mt-3">${message}</p>
            </div>
        `;
    },

    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            this.showToast('Copié dans le presse-papiers', 'success');
        });
    },

    getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    },

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    document.body.appendChild(container);
    return container;
}

// Export pour utilisation globale
if (typeof window !== 'undefined') {
    window.CONFIG = CONFIG;
    window.HELPERS = HELPERS;
}
