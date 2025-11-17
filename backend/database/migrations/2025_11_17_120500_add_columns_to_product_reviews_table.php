<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->string('title')->nullable()->after('rating');
            $table->json('images')->nullable()->after('comment');
            $table->boolean('is_verified_purchase')->default(false)->after('images');
            $table->integer('helpful_count')->default(0)->after('is_verified_purchase');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('helpful_count');
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropColumn(['title', 'images', 'is_verified_purchase', 'helpful_count', 'status']);
        });
    }
};
