export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  role: 'client' | 'vendor' | 'admin';
  avatar?: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface Vendor {
  id: number;
  user_id: number;
  shop_name: string;
  shop_description?: string;
  shop_logo?: string;
  business_email: string;
  business_phone: string;
  business_address: string;
  commission_rate: number;
  status: 'pending' | 'approved' | 'rejected' | 'suspended';
  total_sales: number;
  rating: number;
  created_at: string;
  updated_at: string;
  user?: User;
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string;
  image?: string;
  parent_id?: number;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  parent?: Category;
  children?: Category[];
  products_count?: number;
}

export interface Brand {
  id: number;
  name: string;
  slug: string;
  logo?: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface Product {
  id: number;
  vendor_id: number;
  category_id: number;
  brand_id?: number;
  name: string;
  slug: string;
  description?: string;
  short_description?: string;
  price: number;
  sale_price?: number;
  cost_price?: number;
  sku: string;
  barcode?: string;
  stock_quantity: number;
  stock_status: 'in_stock' | 'out_of_stock' | 'on_backorder';
  has_variants: boolean;
  is_featured: boolean;
  status: 'draft' | 'pending' | 'published' | 'rejected';
  views: number;
  rating: number;
  total_reviews: number;
  total_sales: number;
  created_at: string;
  updated_at: string;
  vendor?: Vendor;
  category?: Category;
  brand?: Brand;
  images?: ProductImage[];
  variants?: ProductVariant[];
}

export interface ProductImage {
  id: number;
  product_id: number;
  image_url: string;
  is_primary: boolean;
  sort_order: number;
  created_at: string;
  updated_at: string;
}

export interface ProductVariant {
  id: number;
  product_id: number;
  sku: string;
  name: string;
  price: number;
  sale_price?: number;
  stock_quantity: number;
  stock_status: 'in_stock' | 'out_of_stock' | 'on_backorder';
  image?: string;
  created_at: string;
  updated_at: string;
}

export interface Cart {
  id: number;
  user_id?: number;
  session_id?: string;
  created_at: string;
  updated_at: string;
  items?: CartItem[];
}

export interface CartItem {
  id: number;
  cart_id: number;
  product_id: number;
  product_variant_id?: number;
  quantity: number;
  price: number;
  created_at: string;
  updated_at: string;
  product?: Product;
  variant?: ProductVariant;
}

export interface Order {
  id: number;
  user_id: number;
  order_number: string;
  status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled' | 'refunded';
  subtotal: number;
  shipping_cost: number;
  discount: number;
  tax: number;
  total: number;
  payment_status: 'pending' | 'paid' | 'failed' | 'refunded';
  payment_method: 'cash' | 'card' | 'bank_transfer';
  shipping_method_id: number;
  address_id: number;
  notes?: string;
  created_at: string;
  updated_at: string;
  user?: User;
  items?: OrderItem[];
  shipping_method?: ShippingMethod;
  address?: Address;
}

export interface OrderItem {
  id: number;
  order_id: number;
  product_id: number;
  product_variant_id?: number;
  vendor_id: number;
  quantity: number;
  price: number;
  vendor_commission: number;
  status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled';
  created_at: string;
  updated_at: string;
  product?: Product;
  variant?: ProductVariant;
  vendor?: Vendor;
}

export interface Address {
  id: number;
  user_id: number;
  name: string;
  phone: string;
  address_line1: string;
  address_line2?: string;
  city: string;
  state: string;
  postal_code: string;
  country: string;
  is_default: boolean;
  created_at: string;
  updated_at: string;
}

export interface ShippingMethod {
  id: number;
  name: string;
  description?: string;
  price: number;
  estimated_days: string;
  is_active: boolean;
  regions?: string;
  created_at: string;
  updated_at: string;
}

export interface Coupon {
  id: number;
  code: string;
  type: 'percentage' | 'fixed';
  value: number;
  min_purchase_amount?: number;
  max_discount_amount?: number;
  usage_limit?: number;
  used_count: number;
  valid_from?: string;
  valid_until?: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}
