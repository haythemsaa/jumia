/**
 * ICHRI Tunisia Mobile - Helper Functions
 */

import { APP_CONFIG } from '../config/api';

export const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-TN', {
    style: 'decimal',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price) + ' ' + APP_CONFIG.CURRENCY;
};

export const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

export const truncateText = (text, maxLength) => {
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + '...';
};

export const calculateDiscount = (originalPrice, discountedPrice) => {
  if (!originalPrice || !discountedPrice) return 0;
  return Math.round(((originalPrice - discountedPrice) / originalPrice) * 100);
};

export const validateEmail = (email) => {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
};

export const validatePhone = (phone) => {
  const regex = /^(\+216)?[0-9]{8}$/;
  return regex.test(phone);
};

export const getImageUrl = (imagePath) => {
  if (!imagePath) return null;
  if (imagePath.startsWith('http')) return imagePath;
  return `${API_CONFIG.BASE_URL.replace('/api', '')}/storage/${imagePath}`;
};

export const getStatusColor = (status) => {
  const colors = {
    pending: '#FFA000',
    confirmed: '#2196F3',
    shipped: '#9C27B0',
    delivered: '#4CAF50',
    cancelled: '#F44336',
  };
  return colors[status] || '#757575';
};

export const debounce = (func, wait) => {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
};

export default {
  formatPrice,
  formatDate,
  truncateText,
  calculateDiscount,
  validateEmail,
  validatePhone,
  getImageUrl,
  getStatusColor,
  debounce
};
