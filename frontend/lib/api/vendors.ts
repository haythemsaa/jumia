import api from './axios';

export interface BecomeVendorData {
  shop_name: string;
  shop_description: string;
  business_email: string;
  business_phone: string;
  business_address: string;
}

export const vendorService = {
  becomeVendor: async (data: BecomeVendorData) => {
    const response = await api.post('/vendors/register', data);
    return response.data;
  },

  getDashboard: async () => {
    const response = await api.get('/vendors/dashboard');
    return response.data;
  },

  getVendorProducts: async (page: number = 1) => {
    const response = await api.get('/vendors/products', { params: { page } });
    return response.data;
  },

  getVendorOrders: async (page: number = 1, status?: string) => {
    const response = await api.get('/vendors/orders', { params: { page, status } });
    return response.data;
  },

  updateOrderStatus: async (orderId: number, status: string) => {
    const response = await api.put(`/vendors/orders/${orderId}/status`, { status });
    return response.data;
  },
};
