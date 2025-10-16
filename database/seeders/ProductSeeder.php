<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [
            [
                'title' => 'Smartphone',
                'slug' => 'smartphone',
                'summary' => 'Latest smartphone with advanced features',
                'description' => 'A high-end smartphone with excellent camera and performance.',
                'price' => 599.99,
                'discount' => 0.0,
                'stock' => 50,
                'status' => 'active',
                'condition' => 'new',
                'is_featured' => false,
                'cat_id' => 1, // Electronics
                'photo' => 'smartphone.jpg',
            ],
            [
                'title' => 'T-Shirt',
                'slug' => 't-shirt',
                'summary' => 'Comfortable cotton t-shirt',
                'description' => 'Soft and comfortable t-shirt made from 100% cotton.',
                'price' => 19.99,
                'discount' => 0.0,
                'stock' => 100,
                'status' => 'active',
                'condition' => 'new',
                'is_featured' => false,
                'cat_id' => 2, // Clothing
                'photo' => 'tshirt.jpg',
            ],
            [
                'title' => 'Programming Book',
                'slug' => 'programming-book',
                'summary' => 'Learn programming with this comprehensive guide',
                'description' => 'A detailed book covering various programming languages and concepts.',
                'price' => 49.99,
                'discount' => 0.0,
                'stock' => 30,
                'status' => 'active',
                'condition' => 'new',
                'is_featured' => false,
                'cat_id' => 3, // Books
                'photo' => 'book.jpg',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}