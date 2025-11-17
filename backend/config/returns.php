<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Return Window
    |--------------------------------------------------------------------------
    |
    | Number of days after delivery within which a return can be requested.
    |
    */

    'window_days' => env('RETURN_WINDOW_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | Return Types
    |--------------------------------------------------------------------------
    |
    | Available return types and their settings.
    |
    */

    'types' => [
        'return' => [
            'enabled' => true,
            'label' => 'Retour',
            'description' => 'Retourner le produit pour un remboursement',
        ],
        'exchange' => [
            'enabled' => true,
            'label' => 'Échange',
            'description' => 'Échanger le produit contre un autre',
        ],
        'refund' => [
            'enabled' => true,
            'label' => 'Remboursement',
            'description' => 'Obtenir un remboursement sans retour physique',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Return Reasons
    |--------------------------------------------------------------------------
    */

    'reasons' => [
        'defective' => 'Produit défectueux',
        'wrong_item' => 'Mauvais article reçu',
        'not_as_described' => 'Non conforme à la description',
        'damaged' => 'Produit endommagé',
        'changed_mind' => 'Changement d\'avis',
        'other' => 'Autre raison',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Approval
    |--------------------------------------------------------------------------
    |
    | Automatically approve returns for specific reasons.
    |
    */

    'auto_approve_reasons' => [
        'defective',
        'wrong_item',
        'damaged',
    ],

    /*
    |--------------------------------------------------------------------------
    | Return Processing
    |--------------------------------------------------------------------------
    */

    'max_images' => env('RETURN_MAX_IMAGES', 5),

    'require_images' => env('RETURN_REQUIRE_IMAGES', true),

    'refund_processing_days' => env('REFUND_PROCESSING_DAYS', 5),
];
