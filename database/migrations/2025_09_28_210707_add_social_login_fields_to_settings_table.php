<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Google Login Settings
            $table->boolean('google_login_enabled')->default(false);
            $table->string('google_client_id')->nullable();
            $table->string('google_client_secret')->nullable();
            
            // Facebook Login Settings
            $table->boolean('facebook_login_enabled')->default(false);
            $table->string('facebook_client_id')->nullable();
            $table->string('facebook_client_secret')->nullable();
            
            // GitHub Login Settings
            $table->boolean('github_login_enabled')->default(false);
            $table->string('github_client_id')->nullable();
            $table->string('github_client_secret')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'google_login_enabled',
                'google_client_id',
                'google_client_secret',
                'facebook_login_enabled',
                'facebook_client_id',
                'facebook_client_secret',
                'github_login_enabled',
                'github_client_id',
                'github_client_secret'
            ]);
        });
    }
};
