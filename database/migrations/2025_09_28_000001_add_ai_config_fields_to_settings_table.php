<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAiConfigFieldsToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('ai_model')->nullable()->after('ai_api_key');
            $table->integer('ai_max_tokens')->nullable()->after('ai_model');
            $table->decimal('ai_temperature', 3, 2)->nullable()->after('ai_max_tokens');
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
            $table->dropColumn(['ai_model','ai_max_tokens','ai_temperature']);
        });
    }
}
