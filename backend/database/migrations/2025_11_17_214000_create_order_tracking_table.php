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
        // Order Status History
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('status');
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->boolean('notify_customer')->default(true);
            $table->timestamp('created_at');

            $table->index('order_id');
            $table->index('status');
        });

        // Order Tracking Events
        Schema::create('order_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('event_type'); // picked_up, in_transit, out_for_delivery, delivered
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('event_at');
            $table->timestamps();

            $table->index('order_id');
            $table->index('event_type');
        });

        // Newsletter Subscriptions
        Schema::create('newsletter_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('status')->default('subscribed'); // subscribed, unsubscribed
            $table->string('token')->unique();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->index('email');
            $table->index('status');
        });

        // Newsletter Campaigns
        Schema::create('newsletter_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->text('content');
            $table->string('status')->default('draft'); // draft, scheduled, sending, sent
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('sent_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->timestamps();

            $table->index('status');
        });

        // Product Views (for recommendations)
        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable();
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->timestamp('viewed_at');

            $table->index('product_id');
            $table->index('user_id');
            $table->index('viewed_at');
        });

        // Product Recommendations
        Schema::create('product_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('recommended_product_id')->constrained('products')->onDelete('cascade');
            $table->string('type'); // frequently_bought_together, similar, related
            $table->integer('weight')->default(1);
            $table->timestamps();

            $table->index(['product_id', 'type']);
            $table->unique(['product_id', 'recommended_product_id', 'type'], 'product_recommendation_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_recommendations');
        Schema::dropIfExists('product_views');
        Schema::dropIfExists('newsletter_campaigns');
        Schema::dropIfExists('newsletter_subscriptions');
        Schema::dropIfExists('order_tracking_events');
        Schema::dropIfExists('order_status_histories');
    }
};
