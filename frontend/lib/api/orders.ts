import api from './axios';

export interface CreateOrderData {
  address_id: number;
  shipping_method_id: number;
  coupon_code?: string;
  payment_method: 'cash' | 'card' | 'bank_transfer';
  notes?: string;
}

export const orderService = {
  createOrder: async (data: CreateOrderData) => {
    const response = await api.post('/orders', data);
    return response.data;
  },

  getOrders: async (page: number = 1) => {
    const response = await api.get('/orders', { params: { page } });
    return response.data;
  },

  getOrder: async (id: number) => {
    const response = await api.get(`/orders/${id}`);
    return response.data;
  },

  cancelOrder: async (id: number) => {
    const response = await api.post(`/orders/${id}/cancel`);
    return response.data;
  },
};
