<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->boolean('enabled')->default(false);
            $table->string('mode')->default('sandbox');
            $table->text('credentials')->nullable();
            // Offline gateway fields
            $table->text('transfer_details')->nullable(); // instructions, bank account details, etc.
            $table->boolean('require_proof')->default(false); // whether this gateway requires user to upload transfer proof at checkout
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_gateways');
    }
};
