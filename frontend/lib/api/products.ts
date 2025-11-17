import api from './axios';

export interface ProductFilters {
  search?: string;
  category_id?: number;
  brand_id?: number;
  vendor_id?: number;
  min_price?: number;
  max_price?: number;
  featured?: boolean;
  in_stock?: boolean;
  page?: number;
  per_page?: number;
}

export const productService = {
  getProducts: async (filters?: ProductFilters) => {
    const response = await api.get('/products', { params: filters });
    return response.data;
  },

  getProduct: async (id: number) => {
    const response = await api.get(`/products/${id}`);
    return response.data;
  },

  getFeaturedProducts: async () => {
    const response = await api.get('/products/featured');
    return response.data;
  },

  getRelatedProducts: async (id: number) => {
    const response = await api.get(`/products/${id}/related`);
    return response.data;
  },

  createProduct: async (data: FormData) => {
    const response = await api.post('/products', data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },

  updateProduct: async (id: number, data: FormData) => {
    const response = await api.post(`/products/${id}`, data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },

  deleteProduct: async (id: number) => {
    const response = await api.delete(`/products/${id}`);
    return response.data;
  },
};
