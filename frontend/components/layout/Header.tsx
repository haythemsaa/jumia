'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useState, useEffect } from 'react';
import { FiShoppingCart, FiUser, FiSearch, FiMenu, FiLogOut } from 'react-icons/fi';
import { useAuthStore } from '@/lib/stores/authStore';
import { useCartStore } from '@/lib/stores/cartStore';

export default function Header() {
  const router = useRouter();
  const { user, isAuthenticated, logout } = useAuthStore();
  const { getCartItemsCount } = useCartStore();
  const [searchQuery, setSearchQuery] = useState('');
  const [cartCount, setCartCount] = useState(0);

  useEffect(() => {
    setCartCount(getCartItemsCount());
  }, [getCartItemsCount]);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      router.push(`/products?search=${encodeURIComponent(searchQuery)}`);
    }
  };

  const handleLogout = async () => {
    await logout();
    router.push('/');
  };

  return (
    <header className="bg-white shadow-md sticky top-0 z-50">
      <div className="container mx-auto px-4">
        {/* Top Bar */}
        <div className="flex items-center justify-between py-4">
          {/* Logo */}
          <Link href="/" className="text-3xl font-bold text-orange-500">
            JUMIA
          </Link>

          {/* Search Bar */}
          <form onSubmit={handleSearch} className="flex-1 max-w-2xl mx-8">
            <div className="relative">
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Rechercher des produits..."
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-orange-500"
              />
              <button
                type="submit"
                className="absolute right-2 top-1/2 -translate-y-1/2 text-orange-500 hover:text-orange-600"
              >
                <FiSearch size={20} />
              </button>
            </div>
          </form>

          {/* Actions */}
          <div className="flex items-center space-x-6">
            {isAuthenticated ? (
              <>
                <div className="relative group">
                  <button className="flex items-center space-x-2 hover:text-orange-500">
                    <FiUser size={24} />
                    <span className="hidden md:block">{user?.name}</span>
                  </button>
                  <div className="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden group-hover:block">
                    <Link
                      href="/account/orders"
                      className="block px-4 py-2 hover:bg-gray-100"
                    >
                      Mes commandes
                    </Link>
                    <Link
                      href="/account/profile"
                      className="block px-4 py-2 hover:bg-gray-100"
                    >
                      Mon profil
                    </Link>
                    {user?.role === 'vendor' && (
                      <Link
                        href="/vendor/dashboard"
                        className="block px-4 py-2 hover:bg-gray-100"
                      >
                        Dashboard Vendeur
                      </Link>
                    )}
                    <button
                      onClick={handleLogout}
                      className="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center space-x-2"
                    >
                      <FiLogOut size={16} />
                      <span>Déconnexion</span>
                    </button>
                  </div>
                </div>

                <Link
                  href="/cart"
                  className="relative flex items-center hover:text-orange-500"
                >
                  <FiShoppingCart size={24} />
                  {cartCount > 0 && (
                    <span className="absolute -top-2 -right-2 bg-orange-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                      {cartCount}
                    </span>
                  )}
                </Link>
              </>
            ) : (
              <>
                <Link
                  href="/auth/login"
                  className="text-gray-700 hover:text-orange-500"
                >
                  Connexion
                </Link>
                <Link
                  href="/auth/register"
                  className="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600"
                >
                  S'inscrire
                </Link>
              </>
            )}
          </div>
        </div>

        {/* Navigation */}
        <nav className="border-t border-gray-200 py-3">
          <ul className="flex items-center space-x-8">
            <li>
              <Link href="/" className="text-gray-700 hover:text-orange-500">
                Accueil
              </Link>
            </li>
            <li>
              <Link
                href="/products"
                className="text-gray-700 hover:text-orange-500"
              >
                Tous les produits
              </Link>
            </li>
            <li>
              <Link
                href="/categories"
                className="text-gray-700 hover:text-orange-500"
              >
                Catégories
              </Link>
            </li>
            <li>
              <Link
                href="/vendors"
                className="text-gray-700 hover:text-orange-500"
              >
                Vendeurs
              </Link>
            </li>
            {!isAuthenticated && (
              <li>
                <Link
                  href="/vendor/register"
                  className="text-orange-500 hover:text-orange-600 font-medium"
                >
                  Devenir vendeur
                </Link>
              </li>
            )}
          </ul>
        </nav>
      </div>
    </header>
  );
}
