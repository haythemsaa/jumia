/**
 * ICHRI Tunisia - API Client
 * Handles all API communications
 */

class APIClient {
    constructor() {
        this.baseURL = CONFIG.API_URL;
        this.timeout = CONFIG.API_TIMEOUT;
        this.token = this.getToken();
        this.lang = this.getLang();
    }

    /**
     * Get auth token from localStorage
     */
    getToken() {
        return localStorage.getItem(CONFIG.STORAGE_KEYS.TOKEN);
    }

    /**
     * Set auth token
     */
    setToken(token) {
        localStorage.setItem(CONFIG.STORAGE_KEYS.TOKEN, token);
        this.token = token;
    }

    /**
     * Remove auth token
     */
    removeToken() {
        localStorage.removeItem(CONFIG.STORAGE_KEYS.TOKEN);
        this.token = null;
    }

    /**
     * Get current language
     */
    getLang() {
        return localStorage.getItem(CONFIG.STORAGE_KEYS.LANG) || CONFIG.DEFAULT_LANG;
    }

    /**
     * Set language
     */
    setLang(lang) {
        localStorage.setItem(CONFIG.STORAGE_KEYS.LANG, lang);
        this.lang = lang;
        document.documentElement.lang = lang;
        if (lang === 'ar') {
            document.documentElement.dir = 'rtl';
        } else {
            document.documentElement.dir = 'ltr';
        }
    }

    /**
     * Build request headers
     */
    getHeaders() {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Locale': this.lang
        };

        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        return headers;
    }

    /**
     * Generic HTTP request
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            ...options,
            headers: {
                ...this.getHeaders(),
                ...options.headers
            }
        };

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), this.timeout);

            const response = await fetch(url, {
                ...config,
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Une erreur est survenue');
            }

            return data;
        } catch (error) {
            if (error.name === 'AbortError') {
                throw new Error('Délai d\'attente dépassé');
            }
            throw error;
        }
    }

    /**
     * GET request
     */
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;

        return this.request(url, {
            method: 'GET'
        });
    }

    /**
     * POST request
     */
    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    /**
     * PUT request
     */
    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    /**
     * DELETE request
     */
    async delete(endpoint) {
        return this.request(endpoint, {
            method: 'DELETE'
        });
    }

    /**
     * Upload file
     */
    async upload(endpoint, file, additionalData = {}) {
        const formData = new FormData();
        formData.append('file', file);

        Object.keys(additionalData).forEach(key => {
            formData.append(key, additionalData[key]);
        });

        const headers = {
            'Authorization': `Bearer ${this.token}`,
            'X-Locale': this.lang
        };

        return this.request(endpoint, {
            method: 'POST',
            headers,
            body: formData
        });
    }

    // ========== Authentication API ==========

    async register(data) {
        return this.post('/register', data);
    }

    async login(credentials) {
        const response = await this.post('/login', credentials);
        if (response.token) {
            this.setToken(response.token);
            localStorage.setItem(CONFIG.STORAGE_KEYS.USER, JSON.stringify(response.user));
        }
        return response;
    }

    async logout() {
        await this.post('/logout');
        this.removeToken();
        localStorage.removeItem(CONFIG.STORAGE_KEYS.USER);
    }

    async getUser() {
        return this.get('/user');
    }

    async updateProfile(data) {
        return this.put('/user', data);
    }

    // ========== Products API ==========

    async getProducts(params = {}) {
        return this.get('/products', params);
    }

    async getProduct(id) {
        return this.get(`/products/${id}`);
    }

    async getFeaturedProducts() {
        return this.get('/products/featured');
    }

    async getTrendingProducts() {
        return this.get('/products/trending');
    }

    async getRelatedProducts(id) {
        return this.get(`/products/${id}/related`);
    }

    async getSimilarProducts(id) {
        return this.get(`/products/${id}/similar`);
    }

    async getFrequentlyBoughtTogether(id) {
        return this.get(`/products/${id}/frequently-bought-together`);
    }

    async searchProducts(query, filters = {}) {
        return this.get('/products', { search: query, ...filters });
    }

    // ========== Categories API ==========

    async getCategories() {
        return this.get('/categories');
    }

    async getCategory(id) {
        return this.get(`/categories/${id}`);
    }

    // ========== Cart API ==========

    async getCart() {
        return this.get('/cart');
    }

    async addToCart(productId, quantity = 1, variantId = null) {
        return this.post('/cart/add', { product_id: productId, quantity, variant_id: variantId });
    }

    async updateCartItem(itemId, quantity) {
        return this.put(`/cart/items/${itemId}`, { quantity });
    }

    async removeFromCart(itemId) {
        return this.delete(`/cart/items/${itemId}`);
    }

    async clearCart() {
        return this.delete('/cart');
    }

    // ========== Wishlist API ==========

    async getWishlist() {
        return this.get('/wishlist');
    }

    async addToWishlist(productId) {
        return this.post('/wishlist', { product_id: productId });
    }

    async removeFromWishlist(productId) {
        return this.delete(`/wishlist/${productId}`);
    }

    async checkWishlist(productId) {
        return this.get(`/wishlist/check/${productId}`);
    }

    async clearWishlist() {
        return this.delete('/wishlist');
    }

    async moveWishlistToCart() {
        return this.post('/wishlist/move-to-cart');
    }

    // ========== Orders API ==========

    async createOrder(data) {
        return this.post('/orders', data);
    }

    async getOrders(params = {}) {
        return this.get('/orders', params);
    }

    async getOrder(id) {
        return this.get(`/orders/${id}`);
    }

    async cancelOrder(id) {
        return this.post(`/orders/${id}/cancel`);
    }

    async trackOrder(id) {
        return this.get(`/orders/${id}/track`);
    }

    // ========== Reviews API ==========

    async getProductReviews(productId, params = {}) {
        return this.get(`/products/${productId}/reviews`, params);
    }

    async submitReview(data) {
        return this.post('/reviews', data);
    }

    async deleteReview(id) {
        return this.delete(`/reviews/${id}`);
    }

    // ========== Flash Sales API ==========

    async getFlashSales() {
        return this.get('/flash-sales');
    }

    async getUpcomingFlashSales() {
        return this.get('/flash-sales/upcoming');
    }

    async getFlashSale(id) {
        return this.get(`/flash-sales/${id}`);
    }

    async checkFlashSaleEligibility(flashSaleProductId) {
        return this.get(`/flash-sales/check-eligibility/${flashSaleProductId}`);
    }

    // ========== Loyalty API ==========

    async getLoyaltyDashboard() {
        return this.get('/loyalty/dashboard');
    }

    async getLoyaltyMissions() {
        return this.get('/loyalty/missions');
    }

    async applyReferralCode(code) {
        return this.post('/loyalty/referral/apply', { code });
    }

    async redeemPoints(points, type) {
        return this.post('/loyalty/redeem', { points, type });
    }

    async getReferralStats() {
        return this.get('/loyalty/referral/stats');
    }

    // ========== Recommendations API ==========

    async getPersonalizedRecommendations() {
        return this.get('/recommendations');
    }

    // ========== Newsletter API ==========

    async subscribeNewsletter(email, name = null) {
        return this.post('/newsletter/subscribe', { email, name });
    }

    async unsubscribeNewsletter(token) {
        return this.get(`/newsletter/unsubscribe/${token}`);
    }

    async checkNewsletterStatus(email) {
        return this.post('/newsletter/status', { email });
    }

    // ========== Vendors API ==========

    async getVendors(params = {}) {
        return this.get('/vendors', params);
    }

    async getVendor(id) {
        return this.get(`/vendors/${id}`);
    }

    // ========== Dashboard API ==========

    async getAdminDashboard(period = '30days') {
        return this.get('/admin/dashboard', { period });
    }

    async getVendorDashboard(period = '30days') {
        return this.get('/vendor/dashboard', { period });
    }
}

// Create global API client instance
window.API = new APIClient();
