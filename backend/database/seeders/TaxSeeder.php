<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaxClass;
use App\Models\CommissionConfig;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        // Create Tunisian TVA rates
        $taxClasses = [
            [
                'name' => 'Taux Normal (19%)',
                'rate' => 19.00,
                'description' => 'Taux standard de TVA en Tunisie',
                'is_active' => true,
            ],
            [
                'name' => 'Taux Réduit (13%)',
                'rate' => 13.00,
                'description' => 'Taux réduit pour certains produits et services',
                'is_active' => true,
            ],
            [
                'name' => 'Taux Réduit (7%)',
                'rate' => 7.00,
                'description' => 'Taux très réduit pour produits de première nécessité',
                'is_active' => true,
            ],
            [
                'name' => 'Exonéré (0%)',
                'rate' => 0.00,
                'description' => 'Produits exonérés de TVA',
                'is_active' => true,
            ],
        ];

        foreach ($taxClasses as $taxClass) {
            TaxClass::create($taxClass);
        }

        // Create default commission config (10% for all vendors)
        CommissionConfig::create([
            'category_id' => null,
            'vendor_id' => null,
            'commission_rate' => 10.00,
            'min_commission' => 1.00,
            'is_active' => true,
        ]);

        // Electronics - higher commission (15%)
        CommissionConfig::create([
            'category_id' => 1, // Assuming electronics category ID is 1
            'vendor_id' => null,
            'commission_rate' => 15.00,
            'min_commission' => 2.00,
            'is_active' => true,
        ]);

        // Fashion - standard commission (12%)
        CommissionConfig::create([
            'category_id' => 2,
            'vendor_id' => null,
            'commission_rate' => 12.00,
            'min_commission' => 1.50,
            'is_active' => true,
        ]);
    }
}
