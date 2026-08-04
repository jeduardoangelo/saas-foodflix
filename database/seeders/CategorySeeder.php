<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed the fixed MVP product categories.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['name' => 'Lanches', 'slug' => 'lanches', 'display_order' => 1],
            ['name' => 'Pizzas', 'slug' => 'pizzas', 'display_order' => 2],
            ['name' => 'Pastéis', 'slug' => 'pasteis', 'display_order' => 3],
            ['name' => 'Bebidas', 'slug' => 'bebidas', 'display_order' => 4],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
