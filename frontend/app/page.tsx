'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { Product, Category } from '@/types';
import { productService, categoryService } from '@/lib/api';
import { FiShoppingCart, FiStar, FiTrendingUp } from 'react-icons/fi';

export default function HomePage() {
  const [featuredProducts, setFeaturedProducts] = useState<Product[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [productsData, categoriesData] = await Promise.all([
          productService.getFeaturedProducts(),
          categoryService.getCategories(),
        ]);
        setFeaturedProducts(productsData.data || []);
        setCategories(categoriesData.data || []);
      } catch (error) {
        console.error('Error fetching data:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
          <p className="mt-4 text-gray-600">Chargement...</p>
        </div>
      </div>
    );
  }

  return (
    <div>
      {/* Hero Section */}
      <section className="bg-gradient-to-r from-orange-500 to-orange-600 text-white py-20">
        <div className="container mx-auto px-4">
          <div className="max-w-3xl">
            <h1 className="text-5xl font-bold mb-4">
              Bienvenue sur ICHRI Tunisia
            </h1>
            <p className="text-xl mb-8">
              Découvrez des millions de produits de vendeurs de confiance en Tunisie
            </p>
            <div className="flex space-x-4">
              <Link
                href="/products"
                className="bg-white text-orange-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition"
              >
                Voir tous les produits
              </Link>
              <Link
                href="/vendor/register"
                className="border-2 border-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-orange-600 transition"
              >
                Devenir vendeur
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* Categories Section */}
      <section className="py-16 bg-gray-50">
        <div className="container mx-auto px-4">
          <h2 className="text-3xl font-bold mb-8">Catégories populaires</h2>
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
            {categories.slice(0, 10).map((category) => (
              <Link
                key={category.id}
                href={`/categories/${category.id}`}
                className="bg-white rounded-lg p-6 text-center hover:shadow-lg transition group"
              >
                <div className="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-orange-200 transition">
                  <FiTrendingUp className="text-orange-500" size={32} />
                </div>
                <h3 className="font-semibold text-gray-900">{category.name}</h3>
                {category.products_count && (
                  <p className="text-sm text-gray-500 mt-1">
                    {category.products_count} produits
                  </p>
                )}
              </Link>
            ))}
          </div>
        </div>
      </section>

      {/* Featured Products */}
      <section className="py-16">
        <div className="container mx-auto px-4">
          <div className="flex items-center justify-between mb-8">
            <h2 className="text-3xl font-bold">Produits mis en avant</h2>
            <Link
              href="/products?featured=true"
              className="text-orange-500 hover:text-orange-600 font-medium"
            >
              Voir tout →
            </Link>
          </div>

          {featuredProducts.length === 0 ? (
            <p className="text-gray-500 text-center py-12">
              Aucun produit mis en avant pour le moment
            </p>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {featuredProducts.map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>
          )}
        </div>
      </section>

      {/* Features Section */}
      <section className="py-16 bg-gray-50">
        <div className="container mx-auto px-4">
          <div className="grid md:grid-cols-3 gap-8">
            <div className="text-center">
              <div className="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <FiShoppingCart className="text-orange-500" size={32} />
              </div>
              <h3 className="font-semibold text-xl mb-2">Livraison rapide</h3>
              <p className="text-gray-600">
                Recevez vos produits partout en Tunisie
              </p>
            </div>
            <div className="text-center">
              <div className="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <FiStar className="text-orange-500" size={32} />
              </div>
              <h3 className="font-semibold text-xl mb-2">Qualité garantie</h3>
              <p className="text-gray-600">
                Produits vérifiés par nos équipes
              </p>
            </div>
            <div className="text-center">
              <div className="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <FiTrendingUp className="text-orange-500" size={32} />
              </div>
              <h3 className="font-semibold text-xl mb-2">Meilleurs prix</h3>
              <p className="text-gray-600">
                Comparez les prix de milliers de vendeurs
              </p>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}

function ProductCard({ product }: { product: Product }) {
  const hasDiscount = product.sale_price && product.sale_price < product.price;
  const discountPercent = hasDiscount
    ? Math.round(((product.price - product.sale_price!) / product.price) * 100)
    : 0;

  return (
    <Link href={`/products/${product.id}`} className="group">
      <div className="bg-white rounded-lg overflow-hidden shadow hover:shadow-lg transition">
        <div className="relative aspect-square">
          {product.images && product.images.length > 0 ? (
            <img
              src={product.images[0].image_url}
              alt={product.name}
              className="w-full h-full object-cover group-hover:scale-105 transition"
            />
          ) : (
            <div className="w-full h-full bg-gray-200 flex items-center justify-center">
              <FiShoppingCart className="text-gray-400" size={48} />
            </div>
          )}
          {hasDiscount && (
            <span className="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-sm font-semibold">
              -{discountPercent}%
            </span>
          )}
        </div>
        <div className="p-4">
          <h3 className="font-semibold text-gray-900 mb-2 line-clamp-2">
            {product.name}
          </h3>
          <div className="flex items-center mb-2">
            <div className="flex items-center">
              <FiStar className="text-yellow-400 fill-current" size={16} />
              <span className="ml-1 text-sm text-gray-600">
                {product.rating.toFixed(1)}
              </span>
            </div>
            <span className="mx-2 text-gray-300">•</span>
            <span className="text-sm text-gray-600">
              {product.total_reviews} avis
            </span>
          </div>
          <div className="flex items-baseline space-x-2">
            <span className="text-xl font-bold text-orange-500">
              {(hasDiscount ? product.sale_price : product.price).toFixed(2)} TND
            </span>
            {hasDiscount && (
              <span className="text-sm text-gray-500 line-through">
                {product.price.toFixed(2)} TND
              </span>
            )}
          </div>
          {product.stock_status === 'out_of_stock' && (
            <p className="text-red-500 text-sm mt-2">Rupture de stock</p>
          )}
        </div>
      </div>
    </Link>
  );
}
