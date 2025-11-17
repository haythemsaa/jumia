import api from './axios';

export interface AddToCartData {
  product_id: number;
  quantity: number;
  product_variant_id?: number;
}

export const cartService = {
  getCart: async () => {
    const response = await api.get('/cart');
    return response.data;
  },

  addToCart: async (data: AddToCartData) => {
    const response = await api.post('/cart', data);
    return response.data;
  },

  updateCartItem: async (itemId: number, quantity: number) => {
    const response = await api.put(`/cart/${itemId}`, { quantity });
    return response.data;
  },

  removeFromCart: async (itemId: number) => {
    const response = await api.delete(`/cart/${itemId}`);
    return response.data;
  },

  clearCart: async () => {
    const response = await api.delete('/cart/clear');
    return response.data;
  },
};
