'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { useCartStore } from '@/lib/stores/cartStore';
import { useAuthStore } from '@/lib/stores/authStore';
import { FiTrash2, FiMinus, FiPlus, FiShoppingCart } from 'react-icons/fi';

export default function CartPage() {
  const router = useRouter();
  const { cart, fetchCart, updateQuantity, removeItem, getCartTotal, isLoading } =
    useCartStore();
  const { isAuthenticated } = useAuthStore();

  useEffect(() => {
    if (isAuthenticated) {
      fetchCart();
    }
  }, [isAuthenticated, fetchCart]);

  const handleQuantityChange = async (itemId: number, newQuantity: number) => {
    if (newQuantity < 1) return;
    await updateQuantity(itemId, newQuantity);
  };

  const handleRemoveItem = async (itemId: number) => {
    if (confirm('Êtes-vous sûr de vouloir retirer ce produit?')) {
      await removeItem(itemId);
    }
  };

  if (!isAuthenticated) {
    return (
      <div className="container mx-auto px-4 py-16 text-center">
        <FiShoppingCart className="mx-auto text-gray-400" size={64} />
        <h2 className="text-2xl font-bold mt-4 mb-2">
          Connectez-vous pour voir votre panier
        </h2>
        <Link
          href="/auth/login"
          className="inline-block bg-orange-500 text-white px-6 py-3 rounded-lg hover:bg-orange-600 mt-4"
        >
          Se connecter
        </Link>
      </div>
    );
  }

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500"></div>
      </div>
    );
  }

  if (!cart || !cart.items || cart.items.length === 0) {
    return (
      <div className="container mx-auto px-4 py-16 text-center">
        <FiShoppingCart className="mx-auto text-gray-400" size={64} />
        <h2 className="text-2xl font-bold mt-4 mb-2">Votre panier est vide</h2>
        <p className="text-gray-600 mb-6">
          Commencez vos achats dès maintenant!
        </p>
        <Link
          href="/products"
          className="inline-block bg-orange-500 text-white px-6 py-3 rounded-lg hover:bg-orange-600"
        >
          Découvrir les produits
        </Link>
      </div>
    );
  }

  const total = getCartTotal();

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-3xl font-bold mb-8">Mon panier</h1>

      <div className="grid lg:grid-cols-3 gap-8">
        {/* Cart Items */}
        <div className="lg:col-span-2">
          <div className="bg-white rounded-lg shadow">
            {cart.items.map((item) => (
              <div
                key={item.id}
                className="flex items-center gap-4 p-4 border-b last:border-b-0"
              >
                {/* Product Image */}
                <div className="w-24 h-24 flex-shrink-0">
                  {item.product?.images && item.product.images.length > 0 ? (
                    <img
                      src={item.product.images[0].image_url}
                      alt={item.product.name}
                      className="w-full h-full object-cover rounded"
                    />
                  ) : (
                    <div className="w-full h-full bg-gray-200 rounded flex items-center justify-center">
                      <FiShoppingCart className="text-gray-400" size={32} />
                    </div>
                  )}
                </div>

                {/* Product Info */}
                <div className="flex-1">
                  <Link
                    href={`/products/${item.product_id}`}
                    className="font-semibold hover:text-orange-500"
                  >
                    {item.product?.name}
                  </Link>
                  {item.variant && (
                    <p className="text-sm text-gray-600">{item.variant.name}</p>
                  )}
                  <p className="text-orange-500 font-bold mt-1">
                    {item.price.toFixed(2)} TND
                  </p>
                </div>

                {/* Quantity Controls */}
                <div className="flex items-center space-x-2">
                  <button
                    onClick={() => handleQuantityChange(item.id, item.quantity - 1)}
                    className="p-1 rounded hover:bg-gray-100"
                  >
                    <FiMinus size={16} />
                  </button>
                  <span className="w-12 text-center font-semibold">
                    {item.quantity}
                  </span>
                  <button
                    onClick={() => handleQuantityChange(item.id, item.quantity + 1)}
                    className="p-1 rounded hover:bg-gray-100"
                  >
                    <FiPlus size={16} />
                  </button>
                </div>

                {/* Subtotal */}
                <div className="text-right w-24">
                  <p className="font-bold">
                    {(item.price * item.quantity).toFixed(2)} TND
                  </p>
                </div>

                {/* Remove Button */}
                <button
                  onClick={() => handleRemoveItem(item.id)}
                  className="text-red-500 hover:text-red-700 p-2"
                >
                  <FiTrash2 size={20} />
                </button>
              </div>
            ))}
          </div>
        </div>

        {/* Order Summary */}
        <div className="lg:col-span-1">
          <div className="bg-white rounded-lg shadow p-6 sticky top-4">
            <h2 className="text-xl font-bold mb-4">Résumé de la commande</h2>

            <div className="space-y-3 mb-6">
              <div className="flex justify-between">
                <span className="text-gray-600">Sous-total</span>
                <span className="font-semibold">{total.toFixed(2)} TND</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Livraison</span>
                <span className="font-semibold">À calculer</span>
              </div>
              <div className="border-t pt-3 flex justify-between text-lg font-bold">
                <span>Total</span>
                <span className="text-orange-500">{total.toFixed(2)} TND</span>
              </div>
            </div>

            <Link
              href="/checkout"
              className="block w-full bg-orange-500 text-white text-center py-3 rounded-lg font-semibold hover:bg-orange-600 transition"
            >
              Passer la commande
            </Link>

            <Link
              href="/products"
              className="block w-full text-center py-3 text-orange-500 hover:text-orange-600 mt-2"
            >
              Continuer mes achats
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
