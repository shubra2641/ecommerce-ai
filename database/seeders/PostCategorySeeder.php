<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostCategory;
use Illuminate\Support\Str;

class PostCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $postCategories = [
            [
                'title' => 'Technology',
                'slug' => 'technology',
                'status' => 'active',
            ],
            [
                'title' => 'Lifestyle',
                'slug' => 'lifestyle',
                'status' => 'active',
            ],
            [
                'title' => 'Education',
                'slug' => 'education',
                'status' => 'active',
            ],
        ];

        foreach ($postCategories as $category) {
            PostCategory::create($category);
        }
    }
}