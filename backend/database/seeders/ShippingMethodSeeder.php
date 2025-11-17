<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Livraison Standard',
                'description' => 'Livraison à domicile en 3-7 jours ouvrables',
                'base_cost' => 7.00,
                'cost_per_kg' => 1.50,
                'estimated_days_min' => 3,
                'estimated_days_max' => 7,
                'available_regions' => ['Tunis', 'Ariana', 'Ben Arous', 'Manouba'],
                'is_active' => true,
                'order' => 1,
            ],
            [
                'name' => 'Livraison Express',
                'description' => 'Livraison rapide en 24-48h',
                'base_cost' => 12.00,
                'cost_per_kg' => 2.00,
                'estimated_days_min' => 1,
                'estimated_days_max' => 2,
                'available_regions' => ['Tunis', 'Ariana'],
                'is_active' => true,
                'order' => 2,
            ],
            [
                'name' => 'Poste Tunisienne',
                'description' => 'Livraison par la Poste Tunisienne',
                'base_cost' => 5.00,
                'cost_per_kg' => 1.00,
                'estimated_days_min' => 5,
                'estimated_days_max' => 10,
                'available_regions' => null, // Available everywhere
                'is_active' => true,
                'order' => 3,
            ],
            [
                'name' => 'Aramex',
                'description' => 'Livraison internationale avec Aramex',
                'base_cost' => 15.00,
                'cost_per_kg' => 3.00,
                'estimated_days_min' => 2,
                'estimated_days_max' => 5,
                'available_regions' => ['Tunis', 'Sfax', 'Sousse'],
                'is_active' => true,
                'order' => 4,
            ],
        ];

        foreach ($methods as $method) {
            ShippingMethod::create($method);
        }
    }
}
