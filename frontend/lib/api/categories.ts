import api from './axios';

export const categoryService = {
  getCategories: async () => {
    const response = await api.get('/categories');
    return response.data;
  },

  getCategory: async (id: number) => {
    const response = await api.get(`/categories/${id}`);
    return response.data;
  },

  getCategoryProducts: async (id: number, page: number = 1) => {
    const response = await api.get(`/categories/${id}/products`, { params: { page } });
    return response.data;
  },
};
