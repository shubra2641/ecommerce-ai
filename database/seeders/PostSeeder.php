<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $posts = [
            [
                'title' => 'The Future of AI',
                'slug' => 'the-future-of-ai',
                'summary' => 'Exploring the advancements in artificial intelligence',
                'description' => 'This article discusses the latest trends and future possibilities in AI technology.',
                'status' => 'active',
                'post_cat_id' => 1, // Technology
                'photo' => 'ai.jpg',
            ],
            [
                'title' => 'Healthy Living Tips',
                'slug' => 'healthy-living-tips',
                'summary' => 'Simple tips for a healthier lifestyle',
                'description' => 'Learn about daily habits that can improve your overall health and well-being.',
                'status' => 'active',
                'post_cat_id' => 2, // Lifestyle
                'photo' => 'health.jpg',
            ],
            [
                'title' => 'Online Learning Platforms',
                'slug' => 'online-learning-platforms',
                'summary' => 'Best platforms for online education',
                'description' => 'A review of popular online learning platforms and their features.',
                'status' => 'active',
                'post_cat_id' => 3, // Education
                'photo' => 'learning.jpg',
            ],
        ];

        foreach ($posts as $post) {
            Post::create($post);
        }
    }
}