/**
 * ICHRI Tunisia - Configuration
 * API and App Configuration
 */

const CONFIG = {
    // API Configuration
    API_URL: 'http://localhost:8000/api',
    API_TIMEOUT: 30000,

    // App Configuration
    APP_NAME: 'ICHRI Tunisia',
    APP_VERSION: '2.0.0',
    DEFAULT_LANG: 'fr',
    SUPPORTED_LANGS: ['fr', 'ar', 'en'],

    // Pagination
    PRODUCTS_PER_PAGE: 12,
    REVIEWS_PER_PAGE: 10,
    ORDERS_PER_PAGE: 10,

    // Cache Configuration
    CACHE_DURATION: 5 * 60 * 1000, // 5 minutes

    // Image Placeholders
    PLACEHOLDER_IMAGE: 'images/placeholder.png',
    DEFAULT_AVATAR: 'images/default-avatar.png',

    // Currency
    CURRENCY: 'TND',
    CURRENCY_SYMBOL: 'TND',

    // Payment Methods
    PAYMENT_METHODS: {
        CASH: 'cash',
        EDINAR: 'edinar',
        KONNECT: 'konnect',
        D17: 'd17'
    },

    // Order Status
    ORDER_STATUS: {
        PENDING: 'pending',
        CONFIRMED: 'confirmed',
        SHIPPED: 'shipped',
        DELIVERED: 'delivered',
        CANCELLED: 'cancelled'
    },

    // Review Status
    REVIEW_STATUS: {
        PENDING: 'pending',
        APPROVED: 'approved',
        REJECTED: 'rejected'
    },

    // Local Storage Keys
    STORAGE_KEYS: {
        TOKEN: 'ichri_token',
        USER: 'ichri_user',
        CART: 'ichri_cart',
        WISHLIST: 'ichri_wishlist',
        LANG: 'ichri_lang',
        RECENT_VIEWS: 'ichri_recent_views'
    },

    // Toast Configuration
    TOAST_DURATION: 3000,

    // Animation Duration
    ANIMATION_DURATION: 300,

    // File Upload
    MAX_FILE_SIZE: 5 * 1024 * 1024, // 5MB
    ALLOWED_IMAGE_TYPES: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],

    // Shipping Zones (Tunisian Governorates)
    GOVERNORATES: [
        'Tunis', 'Ariana', 'Ben Arous', 'Manouba',
        'Nabeul', 'Zaghouan', 'Bizerte',
        'Béja', 'Jendouba', 'Le Kef', 'Siliana',
        'Sousse', 'Monastir', 'Mahdia', 'Sfax',
        'Kairouan', 'Kasserine', 'Sidi Bouzid',
        'Gabès', 'Médenine', 'Tataouine',
        'Gafsa', 'Tozeur', 'Kébili'
    ]
};

// Helper Functions
const HELPERS = {
    /**
     * Format price with currency
     */
    formatPrice(price) {
        return new Intl.NumberFormat('fr-TN', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(price) + ' ' + CONFIG.CURRENCY_SYMBOL;
    },

    /**
     * Format date
     */
    formatDate(date) {
        return new Intl.DateTimeFormat('fr-TN', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }).format(new Date(date));
    },

    /**
     * Format datetime
     */
    formatDateTime(date) {
        return new Intl.DateTimeFormat('fr-TN', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(new Date(date));
    },

    /**
     * Truncate text
     */
    truncate(text, length = 100) {
        if (text.length <= length) return text;
        return text.substr(0, length) + '...';
    },

    /**
     * Generate star rating HTML
     */
    generateStars(rating) {
        let stars = '';
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;

        for (let i = 0; i < fullStars; i++) {
            stars += '<i class="bi bi-star-fill text-warning"></i>';
        }

        if (hasHalfStar) {
            stars += '<i class="bi bi-star-half text-warning"></i>';
        }

        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
        for (let i = 0; i < emptyStars; i++) {
            stars += '<i class="bi bi-star text-warning"></i>';
        }

        return stars;
    },

    /**
     * Calculate discount percentage
     */
    calculateDiscount(originalPrice, salePrice) {
        return Math.round(((originalPrice - salePrice) / originalPrice) * 100);
    },

    /**
     * Debounce function
     */
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
    },

    /**
     * Get query parameter
     */
    getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    },

    /**
     * Set query parameter
     */
    setQueryParam(param, value) {
        const url = new URL(window.location);
        url.searchParams.set(param, value);
        window.history.pushState({}, '', url);
    },

    /**
     * Show loading spinner
     */
    showLoading(container) {
        container.innerHTML = `
            <div class="spinner-container">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>
        `;
    },

    /**
     * Show error message
     */
    showError(container, message) {
        container.innerHTML = `
            <div class="alert alert-danger text-center" role="alert">
                <i class="bi bi-exclamation-triangle fs-3 d-block mb-2"></i>
                <p class="mb-0">${message}</p>
            </div>
        `;
    },

    /**
     * Show empty state
     */
    showEmpty(container, message, icon = 'inbox') {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-${icon} fs-1 text-muted d-block mb-3"></i>
                <p class="text-muted fs-5">${message}</p>
            </div>
        `;
    },

    /**
     * Show toast notification
     */
    showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }

        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: CONFIG.TOAST_DURATION
        });
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    },

    /**
     * Validate email
     */
    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    /**
     * Validate phone (Tunisian format)
     */
    validatePhone(phone) {
        const re = /^\+216[0-9]{8}$/;
        return re.test(phone);
    },

    /**
     * Get image URL
     */
    getImageUrl(path) {
        if (!path) return CONFIG.PLACEHOLDER_IMAGE;
        if (path.startsWith('http')) return path;
        return CONFIG.API_URL.replace('/api', '') + '/storage/' + path;
    },

    /**
     * Copy to clipboard
     */
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            this.showToast('Copié dans le presse-papiers', 'success');
        } catch (err) {
            this.showToast('Erreur lors de la copie', 'danger');
        }
    },

    /**
     * Smooth scroll to element
     */
    scrollTo(element, offset = 0) {
        const targetPosition = element.offsetTop - offset;
        window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
        });
    }
};

// Export configuration
window.CONFIG = CONFIG;
window.HELPERS = HELPERS;
