<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFontSettingsToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            // Font Settings
            $table->string('frontend_font_family')->default('Arial, sans-serif');
            $table->string('frontend_font_size')->default('14px');
            $table->string('frontend_font_weight')->default('normal');
            $table->string('backend_font_family')->default('Arial, sans-serif');
            $table->string('backend_font_size')->default('14px');
            $table->string('backend_font_weight')->default('normal');
            $table->boolean('use_google_fonts')->default(false);
            $table->string('google_fonts_url')->nullable();
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
            $table->dropColumn([
                'frontend_font_family',
                'frontend_font_size',
                'frontend_font_weight',
                'backend_font_family',
                'backend_font_size',
                'backend_font_weight',
                'use_google_fonts',
                'google_fonts_url'
            ]);
        });
    }
}