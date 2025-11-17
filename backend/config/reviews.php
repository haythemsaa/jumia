<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Review Moderation
    |--------------------------------------------------------------------------
    |
    | Auto-approve reviews or require manual moderation.
    |
    */

    'auto_approve' => env('REVIEWS_AUTO_APPROVE', false),

    /*
    |--------------------------------------------------------------------------
    | Review Requirements
    |--------------------------------------------------------------------------
    */

    'require_purchase' => env('REVIEWS_REQUIRE_PURCHASE', true),

    'max_images' => env('REVIEWS_MAX_IMAGES', 5),

    'allow_anonymous' => env('REVIEWS_ALLOW_ANONYMOUS', false),

    /*
    |--------------------------------------------------------------------------
    | Verified Purchase Badge
    |--------------------------------------------------------------------------
    |
    | Only show verified badge if order is delivered/completed.
    |
    */

    'verified_purchase_statuses' => [
        'delivered',
        'completed',
    ],

    /*
    |--------------------------------------------------------------------------
    | Review Deletion
    |--------------------------------------------------------------------------
    |
    | Time window (in hours) during which users can delete their reviews.
    |
    */

    'deletion_window_hours' => env('REVIEW_DELETION_WINDOW', 24),

    /*
    |--------------------------------------------------------------------------
    | Vendor Response
    |--------------------------------------------------------------------------
    */

    'allow_vendor_response' => env('ALLOW_VENDOR_RESPONSE', true),

    'vendor_response_time_limit_days' => env('VENDOR_RESPONSE_TIME_LIMIT', 30),

    /*
    |--------------------------------------------------------------------------
    | Rating Display
    |--------------------------------------------------------------------------
    */

    'min_reviews_for_rating' => env('MIN_REVIEWS_FOR_RATING', 1),

    'show_rating_breakdown' => env('SHOW_RATING_BREAKDOWN', true),
];
