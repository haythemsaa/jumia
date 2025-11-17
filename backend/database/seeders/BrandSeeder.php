<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            // Electronics
            ['name' => 'Samsung', 'slug' => 'samsung'],
            ['name' => 'Apple', 'slug' => 'apple'],
            ['name' => 'Xiaomi', 'slug' => 'xiaomi'],
            ['name' => 'Huawei', 'slug' => 'huawei'],
            ['name' => 'Oppo', 'slug' => 'oppo'],
            ['name' => 'Realme', 'slug' => 'realme'],
            ['name' => 'HP', 'slug' => 'hp'],
            ['name' => 'Dell', 'slug' => 'dell'],
            ['name' => 'Lenovo', 'slug' => 'lenovo'],
            ['name' => 'Asus', 'slug' => 'asus'],

            // Fashion
            ['name' => 'Zara', 'slug' => 'zara'],
            ['name' => 'H&M', 'slug' => 'h-m'],
            ['name' => 'Nike', 'slug' => 'nike'],
            ['name' => 'Adidas', 'slug' => 'adidas'],
            ['name' => 'Puma', 'slug' => 'puma'],

            // Home
            ['name' => 'LG', 'slug' => 'lg'],
            ['name' => 'Bosch', 'slug' => 'bosch'],
            ['name' => 'Whirlpool', 'slug' => 'whirlpool'],

            // Beauty
            ['name' => 'L\'Oréal', 'slug' => 'loreal'],
            ['name' => 'Nivea', 'slug' => 'nivea'],
            ['name' => 'Garnier', 'slug' => 'garnier'],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}
