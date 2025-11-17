<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Electronics
            [
                'name' => 'Électronique',
                'slug' => 'electronique',
                'description' => 'Smartphones, ordinateurs, tablettes et accessoires',
                'order' => 1,
                'children' => [
                    ['name' => 'Smartphones', 'slug' => 'smartphones'],
                    ['name' => 'Ordinateurs', 'slug' => 'ordinateurs'],
                    ['name' => 'Tablettes', 'slug' => 'tablettes'],
                    ['name' => 'Accessoires', 'slug' => 'accessoires-electronique'],
                ]
            ],
            // Fashion
            [
                'name' => 'Mode',
                'slug' => 'mode',
                'description' => 'Vêtements, chaussures et accessoires',
                'order' => 2,
                'children' => [
                    ['name' => 'Femmes', 'slug' => 'femmes'],
                    ['name' => 'Hommes', 'slug' => 'hommes'],
                    ['name' => 'Enfants', 'slug' => 'enfants'],
                    ['name' => 'Chaussures', 'slug' => 'chaussures'],
                ]
            ],
            // Home & Living
            [
                'name' => 'Maison & Décoration',
                'slug' => 'maison-decoration',
                'description' => 'Meubles, décoration et électroménager',
                'order' => 3,
                'children' => [
                    ['name' => 'Meubles', 'slug' => 'meubles'],
                    ['name' => 'Décoration', 'slug' => 'decoration'],
                    ['name' => 'Électroménager', 'slug' => 'electromenager'],
                    ['name' => 'Cuisine', 'slug' => 'cuisine'],
                ]
            ],
            // Beauty & Health
            [
                'name' => 'Beauté & Santé',
                'slug' => 'beaute-sante',
                'description' => 'Produits de beauté et soins',
                'order' => 4,
                'children' => [
                    ['name' => 'Maquillage', 'slug' => 'maquillage'],
                    ['name' => 'Soins', 'slug' => 'soins'],
                    ['name' => 'Parfums', 'slug' => 'parfums'],
                    ['name' => 'Santé', 'slug' => 'sante'],
                ]
            ],
            // Sports & Fitness
            [
                'name' => 'Sports & Fitness',
                'slug' => 'sports-fitness',
                'description' => 'Équipements sportifs et fitness',
                'order' => 5,
                'children' => [
                    ['name' => 'Vêtements Sport', 'slug' => 'vetements-sport'],
                    ['name' => 'Équipement', 'slug' => 'equipement-sport'],
                    ['name' => 'Fitness', 'slug' => 'fitness'],
                ]
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $category = Category::create($categoryData);

            foreach ($children as $childData) {
                Category::create(array_merge($childData, [
                    'parent_id' => $category->id,
                    'order' => 0,
                ]));
            }
        }
    }
}
