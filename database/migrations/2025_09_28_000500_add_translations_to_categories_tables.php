<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTranslationsToCategoriesTables extends Migration
{
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'translations')) {
                $table->json('translations')->nullable()->after('summary');
            }
        });

        Schema::table('post_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('post_categories', 'translations')) {
                $table->json('translations')->nullable()->after('slug');
            }
        });
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'translations')) {
                $table->dropColumn('translations');
            }
        });

        Schema::table('post_categories', function (Blueprint $table) {
            if (Schema::hasColumn('post_categories', 'translations')) {
                $table->dropColumn('translations');
            }
        });
    }
}
