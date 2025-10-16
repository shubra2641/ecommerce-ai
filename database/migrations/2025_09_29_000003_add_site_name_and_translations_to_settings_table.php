<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSiteNameAndTranslationsToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'site_name')) {
                $table->string('site_name')->nullable()->after('email');
            }
            if (!Schema::hasColumn('settings', 'translations')) {
                $table->json('translations')->nullable()->after('site_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'translations')) {
                $table->dropColumn('translations');
            }
            if (Schema::hasColumn('settings', 'site_name')) {
                $table->dropColumn('site_name');
            }
        });
    }
}
