<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class CreateTestUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create test user
        User::updateOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'Test User',
                'email' => 'user@gmail.com',
                'password' => Hash::make('1111'),
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        User::where('email', 'user@gmail.com')->delete();
    }
}
