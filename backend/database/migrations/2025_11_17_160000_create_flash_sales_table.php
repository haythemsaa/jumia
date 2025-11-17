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
        Schema::create('flash_sales', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_active')->default(true);
            $table->integer('max_quantity')->nullable(); // Total quantity limit for the sale
            $table->integer('sold_quantity')->default(0);
            $table->json('eligible_tiers')->nullable(); // Which loyalty tiers can access (null = all)
            $table->timestamps();

            $table->index(['starts_at', 'ends_at', 'is_active']);
        });

        Schema::create('flash_sale_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flash_sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('discount_percentage', 5, 2); // e.g., 50.00 for 50% off
            $table->decimal('flash_price', 10, 2); // Final price during flash sale
            $table->integer('stock_quantity'); // Limited stock for flash sale
            $table->integer('sold_quantity')->default(0);
            $table->integer('max_per_customer')->default(1); // Max quantity per customer
            $table->timestamps();

            $table->unique(['flash_sale_id', 'product_id']);
            $table->index('flash_sale_id');
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['percentage', 'fixed']); // Percentage off or fixed amount
            $table->decimal('value', 10, 2); // Discount value
            $table->decimal('min_purchase', 10, 2)->nullable(); // Minimum purchase amount
            $table->decimal('max_discount', 10, 2)->nullable(); // Maximum discount amount (for percentage)
            $table->integer('usage_limit')->nullable(); // Total usage limit
            $table->integer('usage_per_user')->default(1); // How many times one user can use it
            $table->integer('used_count')->default(0);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('applies_to', ['all', 'category', 'product', 'vendor'])->default('all');
            $table->json('applicable_ids')->nullable(); // IDs of categories/products/vendors
            $table->timestamps();

            $table->index('code');
            $table->index(['is_active', 'valid_from', 'valid_until']);
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->decimal('discount_amount', 10, 2);
            $table->timestamps();

            $table->index(['coupon_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('flash_sale_products');
        Schema::dropIfExists('flash_sales');
    }
};
