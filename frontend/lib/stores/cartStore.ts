import { create } from 'zustand';
import { Cart, CartItem } from '@/types';
import { cartService } from '@/lib/api';
import toast from 'react-hot-toast';

interface CartState {
  cart: Cart | null;
  isLoading: boolean;
  fetchCart: () => Promise<void>;
  addToCart: (productId: number, quantity: number, variantId?: number) => Promise<void>;
  updateQuantity: (itemId: number, quantity: number) => Promise<void>;
  removeItem: (itemId: number) => Promise<void>;
  clearCart: () => Promise<void>;
  getCartTotal: () => number;
  getCartItemsCount: () => number;
}

export const useCartStore = create<CartState>((set, get) => ({
  cart: null,
  isLoading: false,

  fetchCart: async () => {
    try {
      set({ isLoading: true });
      const cart = await cartService.getCart();
      set({ cart, isLoading: false });
    } catch (error) {
      set({ isLoading: false });
      console.error('Failed to fetch cart:', error);
    }
  },

  addToCart: async (productId, quantity, variantId) => {
    try {
      await cartService.addToCart({
        product_id: productId,
        quantity,
        product_variant_id: variantId,
      });
      await get().fetchCart();
      toast.success('Produit ajouté au panier');
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Erreur lors de l\'ajout au panier');
      throw error;
    }
  },

  updateQuantity: async (itemId, quantity) => {
    try {
      await cartService.updateCartItem(itemId, quantity);
      await get().fetchCart();
      toast.success('Quantité mise à jour');
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Erreur lors de la mise à jour');
      throw error;
    }
  },

  removeItem: async (itemId) => {
    try {
      await cartService.removeFromCart(itemId);
      await get().fetchCart();
      toast.success('Produit retiré du panier');
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Erreur lors de la suppression');
      throw error;
    }
  },

  clearCart: async () => {
    try {
      await cartService.clearCart();
      set({ cart: null });
      toast.success('Panier vidé');
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Erreur lors du vidage du panier');
      throw error;
    }
  },

  getCartTotal: () => {
    const { cart } = get();
    if (!cart?.items) return 0;
    return cart.items.reduce((total, item) => total + item.price * item.quantity, 0);
  },

  getCartItemsCount: () => {
    const { cart } = get();
    if (!cart?.items) return 0;
    return cart.items.reduce((count, item) => count + item.quantity, 0);
  },
}));
