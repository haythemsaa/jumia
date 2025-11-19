<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        // Utilisateurs de démonstration
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'ICHRI',
            'email' => 'admin@ichri.tn',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+216 70 123 456',
            'email_verified_at' => now(),
        ]);

        $client = User::create([
            'first_name' => 'Mohamed',
            'last_name' => 'Ben Ahmed',
            'email' => 'client@ichri.tn',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '+216 98 765 432',
            'email_verified_at' => now(),
        ]);

        $vendor = User::create([
            'first_name' => 'Fatma',
            'last_name' => 'Trabelsi',
            'email' => 'vendor@ichri.tn',
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'phone' => '+216 22 333 444',
            'email_verified_at' => now(),
        ]);

        // Catégories
        $categories = [
            ['name' => 'Électronique', 'slug' => 'electronique', 'icon' => 'laptop'],
            ['name' => 'Mode', 'slug' => 'mode', 'icon' => 'tshirt'],
            ['name' => 'Maison & Jardin', 'slug' => 'maison-jardin', 'icon' => 'home'],
            ['name' => 'Sport & Loisirs', 'slug' => 'sport-loisirs', 'icon' => 'football'],
            ['name' => 'Beauté & Santé', 'slug' => 'beaute-sante', 'icon' => 'heart'],
            ['name' => 'Jouets & Enfants', 'slug' => 'jouets-enfants', 'icon' => 'gift'],
            ['name' => 'Livres', 'slug' => 'livres', 'icon' => 'book'],
            ['name' => 'Alimentation', 'slug' => 'alimentation', 'icon' => 'utensils'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // Produits réalistes avec prix tunisiens
        $products = [
            // Électronique
            [
                'name' => 'Smartphone Samsung Galaxy A54',
                'slug' => 'samsung-galaxy-a54',
                'description' => 'Smartphone 5G avec écran Super AMOLED 6.4", 128GB stockage, caméra 50MP. Parfait pour photos et vidéos de qualité professionnelle.',
                'price' => 1299.00,
                'compare_price' => 1499.00,
                'stock' => 25,
                'category_id' => 1,
                'user_id' => $vendor->id,
                'sku' => 'SAMS-A54-128',
            ],
            [
                'name' => 'Laptop HP Pavilion 15',
                'slug' => 'hp-pavilion-15',
                'description' => 'Ordinateur portable Intel Core i5, 8GB RAM, 512GB SSD. Idéal pour travail et études.',
                'price' => 2399.00,
                'compare_price' => 2799.00,
                'stock' => 15,
                'category_id' => 1,
                'user_id' => $vendor->id,
                'sku' => 'HP-PAV-15-I5',
            ],
            [
                'name' => 'Écouteurs Bluetooth JBL',
                'slug' => 'ecouteurs-jbl-bluetooth',
                'description' => 'Écouteurs sans fil avec réduction de bruit active, autonomie 30h.',
                'price' => 249.00,
                'compare_price' => 349.00,
                'stock' => 50,
                'category_id' => 1,
                'user_id' => $vendor->id,
                'sku' => 'JBL-BT-30H',
            ],

            // Mode
            [
                'name' => 'Djellaba Tunisienne Homme',
                'slug' => 'djellaba-tunisienne-homme',
                'description' => 'Djellaba traditionnelle en coton de qualité supérieure. Confortable et élégante.',
                'price' => 89.00,
                'compare_price' => 129.00,
                'stock' => 30,
                'category_id' => 2,
                'user_id' => $vendor->id,
                'sku' => 'DJEL-H-COT',
            ],
            [
                'name' => 'Robe Tunisienne Femme',
                'slug' => 'robe-tunisienne-femme',
                'description' => 'Robe moderne avec broderies traditionnelles tunisiennes. Parfaite pour occasions spéciales.',
                'price' => 159.00,
                'compare_price' => 199.00,
                'stock' => 20,
                'category_id' => 2,
                'user_id' => $vendor->id,
                'sku' => 'ROBE-F-TUN',
            ],
            [
                'name' => 'Baskets Nike Air',
                'slug' => 'baskets-nike-air',
                'description' => 'Chaussures de sport confortables, parfaites pour running et gym.',
                'price' => 329.00,
                'compare_price' => 449.00,
                'stock' => 40,
                'category_id' => 2,
                'user_id' => $vendor->id,
                'sku' => 'NIKE-AIR-42',
            ],

            // Maison
            [
                'name' => 'Cafetière Italienne',
                'slug' => 'cafetiere-italienne',
                'description' => 'Cafetière traditionnelle en aluminium, 6 tasses. Pour un café authentique.',
                'price' => 45.00,
                'compare_price' => 65.00,
                'stock' => 60,
                'category_id' => 3,
                'user_id' => $vendor->id,
                'sku' => 'CAF-IT-6T',
            ],
            [
                'name' => 'Tapis Berbère Artisanal',
                'slug' => 'tapis-berbere-artisanal',
                'description' => 'Tapis fait main par artisans tunisiens, motifs traditionnels, laine pure.',
                'price' => 599.00,
                'compare_price' => 799.00,
                'stock' => 8,
                'category_id' => 3,
                'user_id' => $vendor->id,
                'sku' => 'TAP-BER-200',
            ],

            // Sport
            [
                'name' => 'Ballon de Football',
                'slug' => 'ballon-football',
                'description' => 'Ballon officiel taille 5, cuir synthétique de qualité.',
                'price' => 79.00,
                'compare_price' => 99.00,
                'stock' => 45,
                'category_id' => 4,
                'user_id' => $vendor->id,
                'sku' => 'BALL-FOOT-5',
            ],
            [
                'name' => 'Tapis de Yoga Premium',
                'slug' => 'tapis-yoga-premium',
                'description' => 'Tapis antidérapant 6mm, parfait pour yoga et pilates.',
                'price' => 129.00,
                'compare_price' => 179.00,
                'stock' => 30,
                'category_id' => 4,
                'user_id' => $vendor->id,
                'sku' => 'YOGA-MAT-6',
            ],

            // Beauté
            [
                'name' => 'Huile d\'Argan Bio Tunisienne',
                'slug' => 'huile-argan-bio',
                'description' => 'Huile d\'argan 100% pure et bio, produite en Tunisie. Pour cheveux et peau.',
                'price' => 89.00,
                'compare_price' => 119.00,
                'stock' => 70,
                'category_id' => 5,
                'user_id' => $vendor->id,
                'sku' => 'ARGAN-100-50',
            ],
            [
                'name' => 'Savon Noir Traditionnel',
                'slug' => 'savon-noir-traditionnel',
                'description' => 'Savon noir artisanal à l\'huile d\'olive. Pour hammam et gommage.',
                'price' => 35.00,
                'compare_price' => 49.00,
                'stock' => 100,
                'category_id' => 5,
                'user_id' => $vendor->id,
                'sku' => 'SAV-NOIR-200',
            ],

            // Livres
            [
                'name' => 'Histoire de la Tunisie',
                'slug' => 'histoire-tunisie',
                'description' => 'Livre complet sur l\'histoire tunisienne, de l\'antiquité à nos jours.',
                'price' => 49.00,
                'compare_price' => 69.00,
                'stock' => 25,
                'category_id' => 7,
                'user_id' => $vendor->id,
                'sku' => 'BOOK-HIST-TN',
            ],

            // Alimentation
            [
                'name' => 'Dattes Deglet Nour - 1kg',
                'slug' => 'dattes-deglet-nour-1kg',
                'description' => 'Dattes premium Deglet Nour de Tozeur, sélection qualité export.',
                'price' => 29.00,
                'compare_price' => 39.00,
                'stock' => 150,
                'category_id' => 8,
                'user_id' => $vendor->id,
                'sku' => 'DAT-DN-1KG',
            ],
            [
                'name' => 'Huile d\'Olive Extra Vierge - 1L',
                'slug' => 'huile-olive-extra-vierge',
                'description' => 'Huile d\'olive tunisienne première pression à froid, médaille d\'or.',
                'price' => 45.00,
                'compare_price' => 59.00,
                'stock' => 80,
                'category_id' => 8,
                'user_id' => $vendor->id,
                'sku' => 'OLV-EV-1L',
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        echo "✅ Données de démonstration créées avec succès!\n";
        echo "   - " . User::count() . " utilisateurs\n";
        echo "   - " . Category::count() . " catégories\n";
        echo "   - " . Product::count() . " produits\n";
    }
}
