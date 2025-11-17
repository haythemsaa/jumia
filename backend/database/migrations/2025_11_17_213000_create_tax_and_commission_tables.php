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
        // Tax Classes (TVA rates)
        Schema::create('tax_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Standard Rate, Reduced Rate, Zero Rate
            $table->decimal('rate', 5, 2); // 19%, 7%, 0%
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add tax_class_id to products
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'tax_class_id')) {
                $table->foreignId('tax_class_id')->nullable()->constrained();
            }
        });

        // Commission Configuration
        Schema::create('commission_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->decimal('commission_rate', 5, 2); // Percentage
            $table->decimal('min_commission', 10, 2)->default(0);
            $table->decimal('max_commission', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category_id', 'vendor_id']);
        });

        // Vendor Commissions (per order)
        Schema::create('vendor_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->decimal('order_amount', 10, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->decimal('vendor_earnings', 10, 2);
            $table->string('status')->default('pending'); // pending, approved, paid
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('vendor_id');
            $table->index('order_id');
            $table->index('status');
        });

        // Vendor Payouts
        Schema::create('vendor_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // bank_transfer, check, paypal
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->text('notes')->nullable();
            $table->json('payment_details')->nullable(); // Bank account, etc.
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('vendor_id');
            $table->index('status');
        });

        // Payout Items (which commissions are included)
        Schema::create('payout_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_payout_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_commission_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index('vendor_payout_id');
            $table->index('vendor_commission_id');
        });

        // Add tax and commission fields to orders
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('orders', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax_amount', 'discount_amount']);
        });

        Schema::dropIfExists('payout_items');
        Schema::dropIfExists('vendor_payouts');
        Schema::dropIfExists('vendor_commissions');
        Schema::dropIfExists('commission_configs');

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['tax_class_id']);
            $table->dropColumn('tax_class_id');
        });

        Schema::dropIfExists('tax_classes');
    }
};
