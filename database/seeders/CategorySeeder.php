<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'title' => 'Electronics',
                'slug' => 'electronics',
                'summary' => 'Electronic devices and gadgets',
                'status' => 'active',
                'is_parent' => 1,
                'parent_id' => null,
            ],
            [
                'title' => 'Clothing',
                'slug' => 'clothing',
                'summary' => 'Fashion and apparel',
                'status' => 'active',
                'is_parent' => 1,
                'parent_id' => null,
            ],
            [
                'title' => 'Books',
                'slug' => 'books',
                'summary' => 'Books and literature',
                'status' => 'active',
                'is_parent' => 1,
                'parent_id' => null,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}