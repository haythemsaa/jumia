<?php

namespace Database\Seeders;

use App\Models\LoyaltyMission;
use App\Models\LoyaltyTier;
use Illuminate\Database\Seeder;

class LoyaltySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Loyalty Tiers
        $tiers = [
            [
                'name' => 'Bronze',
                'slug' => 'bronze',
                'min_points' => 0,
                'max_points' => 999,
                'discount_percentage' => 0,
                'points_multiplier' => 1,
                'benefits' => [
                    'Points sur achats',
                    'Accès aux ventes privées',
                ],
                'badge_color' => '#CD7F32',
                'order' => 1,
            ],
            [
                'name' => 'Silver',
                'slug' => 'silver',
                'min_points' => 1000,
                'max_points' => 4999,
                'discount_percentage' => 5,
                'points_multiplier' => 2,
                'benefits' => [
                    'Points x2 sur achats',
                    '5% de remise sur tous les produits',
                    'Livraison gratuite >50 TND',
                    'Support prioritaire',
                ],
                'badge_color' => '#C0C0C0',
                'order' => 2,
            ],
            [
                'name' => 'Gold',
                'slug' => 'gold',
                'min_points' => 5000,
                'max_points' => 14999,
                'discount_percentage' => 10,
                'points_multiplier' => 3,
                'benefits' => [
                    'Points x3 sur achats',
                    '10% de remise sur tous les produits',
                    'Livraison gratuite',
                    'Accès anticipé aux ventes flash',
                    'Support VIP 24/7',
                    'Cadeaux d\'anniversaire',
                ],
                'badge_color' => '#FFD700',
                'order' => 3,
            ],
            [
                'name' => 'Platinum',
                'slug' => 'platinum',
                'min_points' => 15000,
                'max_points' => null,
                'discount_percentage' => 15,
                'points_multiplier' => 5,
                'benefits' => [
                    'Points x5 sur achats',
                    '15% de remise sur tous les produits',
                    'Livraison express gratuite',
                    'Accès exclusif aux ventes privées',
                    'Concierge personnel',
                    'Invitations événements VIP',
                    'Retours gratuits illimités',
                ],
                'badge_color' => '#E5E4E2',
                'order' => 4,
            ],
        ];

        foreach ($tiers as $tier) {
            LoyaltyTier::create($tier);
        }

        // Create Loyalty Missions
        $missions = [
            [
                'title' => 'Première commande',
                'description' => 'Passez votre première commande',
                'type' => 'one_time',
                'action' => 'order_placed',
                'target_count' => 1,
                'points_reward' => 100,
                'is_active' => true,
            ],
            [
                'title' => 'Acheteur régulier',
                'description' => 'Passez 5 commandes',
                'type' => 'one_time',
                'action' => 'order_placed',
                'target_count' => 5,
                'points_reward' => 500,
                'is_active' => true,
            ],
            [
                'title' => 'Fan d\'ICHRI',
                'description' => 'Passez 20 commandes',
                'type' => 'one_time',
                'action' => 'order_placed',
                'target_count' => 20,
                'points_reward' => 2000,
                'is_active' => true,
            ],
            [
                'title' => 'Critique utile',
                'description' => 'Écrivez 3 avis produits',
                'type' => 'one_time',
                'action' => 'review_written',
                'target_count' => 3,
                'points_reward' => 150,
                'is_active' => true,
            ],
            [
                'title' => 'Expert en avis',
                'description' => 'Écrivez 10 avis produits',
                'type' => 'one_time',
                'action' => 'review_written',
                'target_count' => 10,
                'points_reward' => 500,
                'is_active' => true,
            ],
            [
                'title' => 'Ambassadeur ICHRI',
                'description' => 'Parrainez 3 amis',
                'type' => 'one_time',
                'action' => 'friend_referred',
                'target_count' => 3,
                'points_reward' => 300,
                'is_active' => true,
            ],
            [
                'title' => 'Parrain d\'or',
                'description' => 'Parrainez 10 amis',
                'type' => 'one_time',
                'action' => 'friend_referred',
                'target_count' => 10,
                'points_reward' => 1000,
                'is_active' => true,
            ],
            [
                'title' => 'Profil complet',
                'description' => 'Complétez votre profil à 100%',
                'type' => 'one_time',
                'action' => 'profile_completed',
                'target_count' => 1,
                'points_reward' => 50,
                'is_active' => true,
            ],
            [
                'title' => 'Mission quotidienne',
                'description' => 'Ouvrez l\'application',
                'type' => 'daily',
                'action' => 'app_opened',
                'target_count' => 1,
                'points_reward' => 10,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'title' => 'Partage social',
                'description' => 'Partagez un produit sur les réseaux sociaux',
                'type' => 'weekly',
                'action' => 'product_shared',
                'target_count' => 1,
                'points_reward' => 25,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(3),
                'is_active' => true,
            ],
        ];

        foreach ($missions as $mission) {
            LoyaltyMission::create($mission);
        }
    }
}
