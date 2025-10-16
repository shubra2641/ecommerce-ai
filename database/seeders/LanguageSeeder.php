<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder creates basic languages for the system
     */
    public function run(): void
    {
        // Create English as default language
        Language::create([
            'name' => 'English',
            'code' => 'en',
            'flag' => 'us',
            'direction' => 'ltr',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1
        ]);

        // Create Arabic language
        Language::create([
            'name' => 'Arabic',
            'code' => 'ar',
            'flag' => 'sa',
            'direction' => 'rtl',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 2
        ]);

        // Create French language
        Language::create([
            'name' => 'French',
            'code' => 'fr',
            'flag' => 'fr',
            'direction' => 'ltr',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 3
        ]);

        // Create Spanish language
        Language::create([
            'name' => 'Spanish',
            'code' => 'es',
            'flag' => 'es',
            'direction' => 'ltr',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 4
        ]);
    }
}
