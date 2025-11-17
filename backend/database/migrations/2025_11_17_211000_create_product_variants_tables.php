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
        // Product Attributes (e.g., Color, Size, Material)
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Color", "Size"
            $table->string('slug')->unique();
            $table->string('type')->default('select'); // select, radio, color
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });

        // Attribute Values (e.g., Red, Blue, Small, Large)
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_attribute_id')->constrained()->onDelete('cascade');
            $table->string('value');
            $table->string('color_code')->nullable(); // For color attributes
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('product_attribute_id');
        });

        // Product Variants (specific combinations)
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('barcode')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->json('attributes'); // {size_id: 1, color_id: 2}
            $table->string('image')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('product_id');
            $table->index('sku');
        });

        // Product Attributes Association
        Schema::create('product_attribute_associations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_attribute_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['product_id', 'product_attribute_id'], 'product_attribute_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_associations');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('product_attributes');
    }
};
