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
        // Shipping Zones (Tunis, Sfax, etc.)
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('governorates'); // List of Tunisian governorates
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Shipping Methods (Standard, Express, etc.)
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('carrier')->nullable(); // Aramex, DHL, etc.
            $table->decimal('base_cost', 10, 2)->default(0);
            $table->decimal('cost_per_kg', 10, 2)->default(0);
            $table->decimal('free_shipping_threshold', 10, 2)->nullable();
            $table->integer('estimated_days_min')->nullable();
            $table->integer('estimated_days_max')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Shipping Rates (Zone + Method)
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained()->onDelete('cascade');
            $table->foreignId('shipping_method_id')->constrained()->onDelete('cascade');
            $table->decimal('rate', 10, 2);
            $table->decimal('additional_rate_per_kg', 10, 2)->default(0);
            $table->decimal('free_shipping_threshold', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['shipping_zone_id', 'shipping_method_id']);
            $table->index(['shipping_zone_id', 'is_active']);
        });

        // Update orders table to include shipping
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'shipping_method_id')) {
                $table->foreignId('shipping_method_id')->nullable()->constrained();
            }
            if (!Schema::hasColumn('orders', 'shipping_zone_id')) {
                $table->foreignId('shipping_zone_id')->nullable()->constrained();
            }
            if (!Schema::hasColumn('orders', 'shipping_cost')) {
                $table->decimal('shipping_cost', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('orders', 'governorate')) {
                $table->string('governorate')->nullable();
            }
            if (!Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shipping_method_id']);
            $table->dropForeign(['shipping_zone_id']);
            $table->dropColumn(['shipping_method_id', 'shipping_zone_id', 'shipping_cost', 'governorate', 'tracking_number']);
        });

        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipping_zones');
    }
};
