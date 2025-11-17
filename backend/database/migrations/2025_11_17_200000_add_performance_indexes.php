<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index('email');
            $table->index('referral_code');
            $table->index('loyalty_tier_id');
            $table->index(['role', 'created_at']);
        });

        // Products table indexes
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('vendor_id');
            $table->index('status');
            $table->index(['status', 'created_at']);
            $table->index(['category_id', 'status']);
            $table->index(['price', 'status']);
            $table->fullText(['name', 'description']);
        });

        // Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('status');
            $table->index('payment_method');
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        // Order Items table indexes
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('product_id');
            $table->index(['order_id', 'product_id']);
        });

        // Payments table indexes
        Schema::table('payments', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('transaction_id');
            $table->index('payment_method');
            $table->index('status');
            $table->index(['payment_method', 'status']);
        });

        // Reviews table indexes
        Schema::table('reviews', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('user_id');
            $table->index('status');
            $table->index(['product_id', 'status']);
            $table->index(['rating', 'status']);
        });

        // Cart table indexes
        Schema::table('carts', function (Blueprint $table) {
            $table->index('user_id');
        });

        // Cart Items table indexes
        Schema::table('cart_items', function (Blueprint $table) {
            $table->index('cart_id');
            $table->index('product_id');
            $table->index(['cart_id', 'product_id']);
        });

        // Categories table indexes
        Schema::table('categories', function (Blueprint $table) {
            $table->index('slug');
            $table->index('parent_id');
        });

        // Flash Sales table indexes
        Schema::table('flash_sales', function (Blueprint $table) {
            $table->index('status');
            $table->index(['status', 'start_time', 'end_time']);
        });

        // Flash Sale Products table indexes
        Schema::table('flash_sale_products', function (Blueprint $table) {
            $table->index('flash_sale_id');
            $table->index('product_id');
            $table->index(['flash_sale_id', 'product_id']);
        });

        // Loyalty Transactions table indexes
        Schema::table('loyalty_transactions', function (Blueprint $table) {
            $table->index('user_id');
            $table->index(['user_id', 'created_at']);
        });

        // User Missions table indexes
        Schema::table('user_missions', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('loyalty_mission_id');
            $table->index('status');
            $table->index(['user_id', 'status']);
        });

        // Referrals table indexes
        Schema::table('referrals', function (Blueprint $table) {
            $table->index('referrer_id');
            $table->index('referred_id');
            $table->index('code');
            $table->index('status');
        });

        // Conversations table indexes
        Schema::table('conversations', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('vendor_id');
            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index(['vendor_id', 'status']);
        });

        // Messages table indexes
        Schema::table('messages', function (Blueprint $table) {
            $table->index('conversation_id');
            $table->index('sender_id');
            $table->index(['conversation_id', 'created_at']);
        });

        // Notifications table indexes
        Schema::table('notifications', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('type');
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
        });

        // Coupons table indexes
        Schema::table('coupons', function (Blueprint $table) {
            $table->index('code');
            $table->index(['code', 'status']);
            $table->index(['status', 'valid_from', 'valid_until']);
        });

        // Coupon Usages table indexes
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->index('coupon_id');
            $table->index('user_id');
            $table->index('order_id');
            $table->index(['user_id', 'coupon_id']);
        });

        // Returns table indexes (if exists)
        if (Schema::hasTable('returns')) {
            Schema::table('returns', function (Blueprint $table) {
                $table->index('order_id');
                $table->index('user_id');
                $table->index('status');
                $table->index(['status', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Users
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['referral_code']);
            $table->dropIndex(['loyalty_tier_id']);
            $table->dropIndex(['role', 'created_at']);
        });

        // Products
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['vendor_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['category_id', 'status']);
            $table->dropIndex(['price', 'status']);
            $table->dropFullText(['name', 'description']);
        });

        // Orders
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_method']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['status', 'created_at']);
        });

        // Order Items
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['product_id']);
            $table->dropIndex(['order_id', 'product_id']);
        });

        // Payments
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['transaction_id']);
            $table->dropIndex(['payment_method']);
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_method', 'status']);
        });

        // Reviews
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['product_id', 'status']);
            $table->dropIndex(['rating', 'status']);
        });

        // Cart
        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        // Cart Items
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex(['cart_id']);
            $table->dropIndex(['product_id']);
            $table->dropIndex(['cart_id', 'product_id']);
        });

        // Categories
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropIndex(['parent_id']);
        });

        // Flash Sales
        Schema::table('flash_sales', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['status', 'start_time', 'end_time']);
        });

        // Flash Sale Products
        Schema::table('flash_sale_products', function (Blueprint $table) {
            $table->dropIndex(['flash_sale_id']);
            $table->dropIndex(['product_id']);
            $table->dropIndex(['flash_sale_id', 'product_id']);
        });

        // Loyalty Transactions
        Schema::table('loyalty_transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['user_id', 'created_at']);
        });

        // User Missions
        Schema::table('user_missions', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['loyalty_mission_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id', 'status']);
        });

        // Referrals
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropIndex(['referrer_id']);
            $table->dropIndex(['referred_id']);
            $table->dropIndex(['code']);
            $table->dropIndex(['status']);
        });

        // Conversations
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['vendor_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['vendor_id', 'status']);
        });

        // Messages
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['conversation_id']);
            $table->dropIndex(['sender_id']);
            $table->dropIndex(['conversation_id', 'created_at']);
        });

        // Notifications
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['type']);
            $table->dropIndex(['user_id', 'read_at']);
            $table->dropIndex(['user_id', 'created_at']);
        });

        // Coupons
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropIndex(['code']);
            $table->dropIndex(['code', 'status']);
            $table->dropIndex(['status', 'valid_from', 'valid_until']);
        });

        // Coupon Usages
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->dropIndex(['coupon_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['order_id']);
            $table->dropIndex(['user_id', 'coupon_id']);
        });

        // Returns
        if (Schema::hasTable('returns')) {
            Schema::table('returns', function (Blueprint $table) {
                $table->dropIndex(['order_id']);
                $table->dropIndex(['user_id']);
                $table->dropIndex(['status']);
                $table->dropIndex(['status', 'created_at']);
            });
        }
    }
};
