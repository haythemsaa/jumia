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
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Bronze, Silver, Gold, Platinum
            $table->string('slug')->unique();
            $table->integer('min_points')->default(0);
            $table->integer('max_points')->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0); // e.g., 5.00 for 5%
            $table->integer('points_multiplier')->default(1); // How many points earned per TND spent
            $table->json('benefits')->nullable(); // Free shipping, priority support, etc.
            $table->string('badge_color')->nullable(); // For UI display
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('loyalty_missions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['daily', 'weekly', 'monthly', 'one_time']);
            $table->enum('action', [
                'order_placed',
                'review_written',
                'product_shared',
                'friend_referred',
                'profile_completed',
                'app_opened',
                'wishlist_created',
            ]);
            $table->integer('target_count')->default(1); // How many times to do the action
            $table->integer('points_reward');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('user_missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('loyalty_mission_id')->constrained()->onDelete('cascade');
            $table->integer('current_count')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'loyalty_mission_id']);
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_id')->constrained('users')->onDelete('cascade');
            $table->string('referral_code')->unique();
            $table->integer('points_earned')->default(0);
            $table->boolean('is_claimed')->default(false);
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();

            $table->index('referral_code');
        });

        // Update users table to add loyalty tier
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('loyalty_tier_id')->nullable()->after('role')->constrained('loyalty_tiers')->onDelete('set null');
            $table->integer('loyalty_points')->default(0)->after('loyalty_tier_id');
            $table->string('referral_code')->unique()->nullable()->after('loyalty_points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['loyalty_tier_id']);
            $table->dropColumn(['loyalty_tier_id', 'loyalty_points', 'referral_code']);
        });

        Schema::dropIfExists('referrals');
        Schema::dropIfExists('user_missions');
        Schema::dropIfExists('loyalty_missions');
        Schema::dropIfExists('loyalty_tiers');
    }
};
