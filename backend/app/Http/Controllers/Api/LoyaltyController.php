<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyMission;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyTier;
use App\Models\Referral;
use App\Models\User;
use App\Models\UserMission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoyaltyController extends Controller
{
    /**
     * Get user's loyalty dashboard
     */
    public function dashboard()
    {
        $user = auth()->user();
        $user->load('loyaltyTier');

        $currentTier = $user->loyaltyTier;
        $nextTier = LoyaltyTier::where('min_points', '>', $user->loyalty_points)
            ->orderBy('min_points')
            ->first();

        $pointsToNextTier = $nextTier ? $nextTier->min_points - $user->loyalty_points : 0;

        return response()->json([
            'success' => true,
            'points' => $user->loyalty_points,
            'current_tier' => $currentTier,
            'next_tier' => $nextTier,
            'points_to_next_tier' => $pointsToNextTier,
            'progress_percentage' => $this->calculateTierProgress($user, $currentTier, $nextTier),
            'referral_code' => $user->referral_code ?? $user->generateReferralCode(),
            'total_referrals' => $user->referrals()->count(),
        ]);
    }

    /**
     * Get all loyalty tiers
     */
    public function tiers()
    {
        $tiers = LoyaltyTier::active()->ordered()->get();

        return response()->json([
            'success' => true,
            'tiers' => $tiers,
        ]);
    }

    /**
     * Get loyalty points history
     */
    public function history(Request $request)
    {
        $user = auth()->user();

        $history = LoyaltyPoint::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    /**
     * Get available missions
     */
    public function missions()
    {
        $user = auth()->user();

        $missions = LoyaltyMission::active()->get();

        // Get user's progress for each mission
        $missionsWithProgress = $missions->map(function ($mission) use ($user) {
            $userMission = UserMission::where('user_id', $user->id)
                ->where('loyalty_mission_id', $mission->id)
                ->first();

            return [
                'mission' => $mission,
                'progress' => $userMission ? [
                    'current_count' => $userMission->current_count,
                    'is_completed' => $userMission->is_completed,
                    'completed_at' => $userMission->completed_at,
                    'progress_percentage' => $userMission->getProgress(),
                ] : [
                    'current_count' => 0,
                    'is_completed' => false,
                    'completed_at' => null,
                    'progress_percentage' => 0,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'missions' => $missionsWithProgress,
        ]);
    }

    /**
     * Apply referral code
     */
    public function applyReferralCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'referral_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        // Check if user already has a referrer
        if ($user->referredBy) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà utilisé un code de parrainage',
            ], 400);
        }

        // Find referrer
        $referrer = User::where('referral_code', $request->referral_code)->first();

        if (!$referrer) {
            return response()->json([
                'success' => false,
                'message' => 'Code de parrainage invalide',
            ], 404);
        }

        // Cannot refer yourself
        if ($referrer->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas utiliser votre propre code de parrainage',
            ], 400);
        }

        // Create referral
        $referral = Referral::create([
            'referrer_id' => $referrer->id,
            'referred_id' => $user->id,
            'referral_code' => $request->referral_code,
        ]);

        // Award initial points (will be claimed when referred makes first purchase)
        $user->addLoyaltyPoints(50, 'Bienvenue via parrainage');

        return response()->json([
            'success' => true,
            'message' => 'Code de parrainage appliqué avec succès! Vous avez reçu 50 points.',
            'referral' => $referral,
        ]);
    }

    /**
     * Get referral statistics
     */
    public function referralStats()
    {
        $user = auth()->user();

        $referrals = Referral::where('referrer_id', $user->id)
            ->with('referred')
            ->get();

        return response()->json([
            'success' => true,
            'total_referrals' => $referrals->count(),
            'claimed_referrals' => $referrals->where('is_claimed', true)->count(),
            'total_points_earned' => $referrals->sum('points_earned'),
            'referrals' => $referrals,
        ]);
    }

    /**
     * Redeem loyalty points for discount
     */
    public function redeemPoints(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'points' => 'required|integer|min:100', // Minimum 100 points to redeem
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        if ($user->loyalty_points < $request->points) {
            return response()->json([
                'success' => false,
                'message' => 'Points insuffisants',
            ], 400);
        }

        // Convert points to discount (e.g., 100 points = 10 TND)
        $discountAmount = $request->points / 10;

        // Deduct points
        if (!$user->deductLoyaltyPoints($request->points, 'Échange contre remise')) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de l\'échange de points',
            ], 500);
        }

        // TODO: Generate coupon code for the discount
        $couponCode = 'LOYALTY-' . strtoupper(uniqid());

        return response()->json([
            'success' => true,
            'message' => 'Points échangés avec succès',
            'discount_amount' => $discountAmount,
            'coupon_code' => $couponCode,
            'remaining_points' => $user->fresh()->loyalty_points,
        ]);
    }

    /**
     * Calculate progress percentage between tiers
     */
    private function calculateTierProgress($user, $currentTier, $nextTier): int
    {
        if (!$currentTier || !$nextTier) {
            return 100;
        }

        $rangeStart = $currentTier->min_points;
        $rangeEnd = $nextTier->min_points;
        $currentPoints = $user->loyalty_points;

        $progress = (($currentPoints - $rangeStart) / ($rangeEnd - $rangeStart)) * 100;

        return min(100, max(0, (int) $progress));
    }
}
