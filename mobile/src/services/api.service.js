/**
 * ICHRI Tunisia Mobile - API Service
 * Handles all HTTP requests to backend
 */

import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { API_CONFIG, STORAGE_KEYS } from '../config/api';

class ApiService {
  constructor() {
    this.client = axios.create({
      baseURL: API_CONFIG.BASE_URL,
      timeout: API_CONFIG.TIMEOUT,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      }
    });

    // Request interceptor
    this.client.interceptors.request.use(
      async (config) => {
        const token = await AsyncStorage.getItem(STORAGE_KEYS.AUTH_TOKEN);
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
      },
      (error) => Promise.reject(error)
    );

    // Response interceptor
    this.client.interceptors.response.use(
      (response) => response.data,
      async (error) => {
        if (error.response?.status === 401) {
          await AsyncStorage.multiRemove([STORAGE_KEYS.AUTH_TOKEN, STORAGE_KEYS.USER_DATA]);
        }
        return Promise.reject(error.response?.data || error.message);
      }
    );
  }

  // Authentication
  async login(email, password) {
    return this.client.post(API_CONFIG.ENDPOINTS.LOGIN, { email, password });
  }

  async register(userData) {
    return this.client.post(API_CONFIG.ENDPOINTS.REGISTER, userData);
  }

  async logout() {
    return this.client.post(API_CONFIG.ENDPOINTS.LOGOUT);
  }

  // Products
  async getProducts(params = {}) {
    return this.client.get(API_CONFIG.ENDPOINTS.PRODUCTS, { params });
  }

  async getProduct(id) {
    return this.client.get(API_CONFIG.ENDPOINTS.PRODUCT_DETAIL.replace(':id', id));
  }

  async getCategories() {
    return this.client.get(API_CONFIG.ENDPOINTS.CATEGORIES);
  }

  // Cart
  async getCart() {
    return this.client.get(API_CONFIG.ENDPOINTS.CART);
  }

  async addToCart(productId, quantity, variantId = null) {
    return this.client.post(API_CONFIG.ENDPOINTS.ADD_TO_CART, {
      product_id: productId,
      quantity,
      variant_id: variantId
    });
  }

  async updateCart(itemId, quantity) {
    return this.client.put(
      API_CONFIG.ENDPOINTS.UPDATE_CART.replace(':id', itemId),
      { quantity }
    );
  }

  async removeFromCart(itemId) {
    return this.client.delete(API_CONFIG.ENDPOINTS.REMOVE_FROM_CART.replace(':id', itemId));
  }

  // Orders
  async getOrders(params = {}) {
    return this.client.get(API_CONFIG.ENDPOINTS.ORDERS, { params });
  }

  async getOrder(id) {
    return this.client.get(API_CONFIG.ENDPOINTS.ORDER_DETAIL.replace(':id', id));
  }

  async createOrder(orderData) {
    return this.client.post(API_CONFIG.ENDPOINTS.CREATE_ORDER, orderData);
  }

  // Wishlist
  async getWishlist() {
    return this.client.get(API_CONFIG.ENDPOINTS.WISHLIST);
  }

  async addToWishlist(productId) {
    return this.client.post(API_CONFIG.ENDPOINTS.ADD_TO_WISHLIST, { product_id: productId });
  }

  // User Profile
  async getProfile() {
    return this.client.get(API_CONFIG.ENDPOINTS.PROFILE);
  }

  async updateProfile(userData) {
    return this.client.put(API_CONFIG.ENDPOINTS.UPDATE_PROFILE, userData);
  }

  // Reviews
  async getReviews(productId) {
    return this.client.get(API_CONFIG.ENDPOINTS.REVIEWS.replace(':id', productId));
  }

  async createReview(productId, reviewData) {
    return this.client.post(
      API_CONFIG.ENDPOINTS.CREATE_REVIEW.replace(':id', productId),
      reviewData
    );
  }
}

export default new ApiService();
