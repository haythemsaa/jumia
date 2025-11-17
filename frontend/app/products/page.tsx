'use client';

import { useEffect, useState } from 'react';
import { useSearchParams } from 'next/navigation';
import Link from 'next/link';
import { Product } from '@/types';
import { productService } from '@/lib/api';
import { FiShoppingCart, FiStar } from 'react-icons/fi';

export default function ProductsPage() {
  const searchParams = useSearchParams();
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [pagination, setPagination] = useState<any>(null);

  useEffect(() => {
    const fetchProducts = async () => {
      try {
        setLoading(true);
        const filters: any = {};

        const search = searchParams.get('search');
        const category = searchParams.get('category_id');
        const featured = searchParams.get('featured');

        if (search) filters.search = search;
        if (category) filters.category_id = category;
        if (featured) filters.featured = true;

        const data = await productService.getProducts(filters);
        setProducts(data.data || []);
        setPagination(data.meta);
      } catch (error) {
        console.error('Error fetching products:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchProducts();
  }, [searchParams]);

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
          <p className="mt-4 text-gray-600">Chargement des produits...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-3xl font-bold mb-8">
        {searchParams.get('search')
          ? `Résultats pour "${searchParams.get('search')}"`
          : 'Tous les produits'}
      </h1>

      {products.length === 0 ? (
        <div className="text-center py-12">
          <p className="text-gray-500 text-lg">Aucun produit trouvé</p>
        </div>
      ) : (
        <>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {products.map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>

          {pagination && pagination.last_page > 1 && (
            <div className="flex justify-center mt-8 space-x-2">
              {Array.from({ length: pagination.last_page }, (_, i) => i + 1).map(
                (page) => (
                  <button
                    key={page}
                    className={`px-4 py-2 rounded ${
                      page === pagination.current_page
                        ? 'bg-orange-500 text-white'
                        : 'bg-gray-200 hover:bg-gray-300'
                    }`}
                  >
                    {page}
                  </button>
                )
              )}
            </div>
          )}
        </>
      )}
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
