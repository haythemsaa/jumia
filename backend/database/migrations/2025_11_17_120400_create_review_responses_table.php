<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_review_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->text('response');
            $table->timestamps();

            $table->unique('product_review_id'); // Une seule réponse par avis
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_responses');
    }
};
