<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingZone;
use App\Models\ShippingMethod;
use App\Models\ShippingRate;

class ShippingSeeder extends Seeder
{
    public function run(): void
    {
        // Create Shipping Zones (Tunisian Governorates)
        $zones = [
            [
                'name' => 'Tunis et environs',
                'governorates' => ['Tunis', 'Ariana', 'Ben Arous', 'Manouba'],
            ],
            [
                'name' => 'Nord-Est',
                'governorates' => ['Nabeul', 'Zaghouan', 'Bizerte'],
            ],
            [
                'name' => 'Nord-Ouest',
                'governorates' => ['Béja', 'Jendouba', 'Le Kef', 'Siliana'],
            ],
            [
                'name' => 'Centre-Est',
                'governorates' => ['Sousse', 'Monastir', 'Mahdia', 'Sfax'],
            ],
            [
                'name' => 'Centre-Ouest',
                'governorates' => ['Kairouan', 'Kasserine', 'Sidi Bouzid'],
            ],
            [
                'name' => 'Sud-Est',
                'governorates' => ['Gabès', 'Médenine', 'Tataouine'],
            ],
            [
                'name' => 'Sud-Ouest',
                'governorates' => ['Gafsa', 'Tozeur', 'Kébili'],
            ],
        ];

        foreach ($zones as $zone) {
            ShippingZone::create($zone);
        }

        // Create Shipping Methods
        $methods = [
            [
                'name' => 'Livraison Standard',
                'code' => 'standard',
                'description' => 'Livraison sous 3-5 jours ouvrables',
                'carrier' => 'ICHRI Express',
                'base_cost' => 7.00,
                'cost_per_kg' => 1.50,
                'free_shipping_threshold' => 100.00,
                'estimated_days_min' => 3,
                'estimated_days_max' => 5,
                'sort_order' => 1,
            ],
            [
                'name' => 'Livraison Express',
                'code' => 'express',
                'description' => 'Livraison sous 24-48h',
                'carrier' => 'Aramex',
                'base_cost' => 12.00,
                'cost_per_kg' => 2.50,
                'estimated_days_min' => 1,
                'estimated_days_max' => 2,
                'sort_order' => 2,
            ],
            [
                'name' => 'Retrait en point relais',
                'code' => 'pickup',
                'description' => 'Retrait gratuit dans nos points relais',
                'carrier' => 'ICHRI Relay',
                'base_cost' => 0.00,
                'cost_per_kg' => 0.00,
                'estimated_days_min' => 2,
                'estimated_days_max' => 4,
                'sort_order' => 3,
            ],
        ];

        foreach ($methods as $method) {
            ShippingMethod::create($method);
        }

        // Create Shipping Rates
        $standardMethod = ShippingMethod::where('code', 'standard')->first();
        $expressMethod = ShippingMethod::where('code', 'express')->first();
        $pickupMethod = ShippingMethod::where('code', 'pickup')->first();

        $rates = [
            // Tunis et environs - Standard
            ['shipping_zone_id' => 1, 'shipping_method_id' => $standardMethod->id, 'rate' => 5.00, 'additional_rate_per_kg' => 1.00, 'free_shipping_threshold' => 80.00],
            // Tunis et environs - Express
            ['shipping_zone_id' => 1, 'shipping_method_id' => $expressMethod->id, 'rate' => 10.00, 'additional_rate_per_kg' => 2.00],
            // Tunis et environs - Pickup
            ['shipping_zone_id' => 1, 'shipping_method_id' => $pickupMethod->id, 'rate' => 0.00],

            // Nord-Est - Standard
            ['shipping_zone_id' => 2, 'shipping_method_id' => $standardMethod->id, 'rate' => 7.00, 'additional_rate_per_kg' => 1.50, 'free_shipping_threshold' => 100.00],
            // Nord-Est - Express
            ['shipping_zone_id' => 2, 'shipping_method_id' => $expressMethod->id, 'rate' => 12.00, 'additional_rate_per_kg' => 2.50],

            // Centre-Est - Standard
            ['shipping_zone_id' => 4, 'shipping_method_id' => $standardMethod->id, 'rate' => 8.00, 'additional_rate_per_kg' => 1.50, 'free_shipping_threshold' => 120.00],
            // Centre-Est - Express
            ['shipping_zone_id' => 4, 'shipping_method_id' => $expressMethod->id, 'rate' => 15.00, 'additional_rate_per_kg' => 3.00],

            // Sud (zones 6-7) - Standard
            ['shipping_zone_id' => 6, 'shipping_method_id' => $standardMethod->id, 'rate' => 10.00, 'additional_rate_per_kg' => 2.00, 'free_shipping_threshold' => 150.00],
            ['shipping_zone_id' => 7, 'shipping_method_id' => $standardMethod->id, 'rate' => 10.00, 'additional_rate_per_kg' => 2.00, 'free_shipping_threshold' => 150.00],
        ];

        foreach ($rates as $rate) {
            ShippingRate::create($rate);
        }
    }
}
