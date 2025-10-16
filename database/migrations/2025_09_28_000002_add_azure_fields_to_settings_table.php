<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAzureFieldsToSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('azure_endpoint')->nullable()->after('ai_temperature');
            $table->string('azure_deployment')->nullable()->after('azure_endpoint');
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['azure_endpoint','azure_deployment']);
        });
    }
}
