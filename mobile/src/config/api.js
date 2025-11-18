/**
 * ICHRI Tunisia Mobile - API Configuration
 */

export const API_CONFIG = {
  BASE_URL: 'http://localhost:8000/api',
  TIMEOUT: 30000,

  // Endpoints
  ENDPOINTS: {
    // Auth
    LOGIN: '/auth/login',
    REGISTER: '/auth/register',
    LOGOUT: '/auth/logout',
    REFRESH_TOKEN: '/auth/refresh',

    // Products
    PRODUCTS: '/products',
    PRODUCT_DETAIL: '/products/:id',
    CATEGORIES: '/categories',

    // Cart
    CART: '/cart',
    ADD_TO_CART: '/cart/add',
    UPDATE_CART: '/cart/update/:id',
    REMOVE_FROM_CART: '/cart/remove/:id',

    // Orders
    ORDERS: '/orders',
    ORDER_DETAIL: '/orders/:id',
    CREATE_ORDER: '/orders/create',

    // Wishlist
    WISHLIST: '/wishlist',
    ADD_TO_WISHLIST: '/wishlist/add',

    // User
    PROFILE: '/user/profile',
    UPDATE_PROFILE: '/user/profile',

    // Reviews
    REVIEWS: '/products/:id/reviews',
    CREATE_REVIEW: '/products/:id/reviews',
  }
};

export const APP_CONFIG = {
  APP_NAME: 'ICHRI Tunisia',
  DEFAULT_LANGUAGE: 'fr',
  SUPPORTED_LANGUAGES: ['fr', 'ar', 'en'],
  CURRENCY: 'TND',
  ITEMS_PER_PAGE: 10,
};

export const STORAGE_KEYS = {
  AUTH_TOKEN: '@ichri_auth_token',
  USER_DATA: '@ichri_user_data',
  CART: '@ichri_cart',
  WISHLIST: '@ichri_wishlist',
  LANGUAGE: '@ichri_language',
};

export default { API_CONFIG, APP_CONFIG, STORAGE_KEYS };
