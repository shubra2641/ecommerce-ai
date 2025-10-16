<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTranslationsToResources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            'brands',
            'products',
            'posts',
            'post_tags',
            'banners',
            'categories',
            'post_categories',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                    if (!Schema::hasColumn($tableBlueprint->getTable(), 'translations')) {
                        $tableBlueprint->json('translations')->nullable();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = [
            'brands',
            'products',
            'posts',
            'post_tags',
            'banners',
            'categories',
            'post_categories',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                    if (Schema::hasColumn($tableBlueprint->getTable(), 'translations')) {
                        $tableBlueprint->dropColumn('translations');
                    }
                });
            }
        }
    }
}