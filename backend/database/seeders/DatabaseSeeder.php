<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed base data
        $this->call([
            CategorySeeder::class,
            BrandSeeder::class,
            ShippingMethodSeeder::class,
        ]);

        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@jumia.tn',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create test client
        User::create([
            'name' => 'Client Test',
            'email' => 'client@example.com',
            'password' => bcrypt('password'),
            'role' => 'client',
            'is_active' => true,
        ]);

        // Create test vendor user
        $vendorUser = User::create([
            'name' => 'Vendeur Test',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
            'is_active' => true,
        ]);

        // Create vendor profile
        \App\Models\Vendor::create([
            'user_id' => $vendorUser->id,
            'shop_name' => 'Boutique Tunisie',
            'slug' => 'boutique-tunisie',
            'description' => 'Votre boutique de confiance pour l\'électronique et la mode',
            'business_email' => 'vendor@example.com',
            'business_phone' => '+216 12 345 678',
            'business_address' => 'Tunis, Tunisia',
            'status' => 'approved',
        ]);
    }
}
