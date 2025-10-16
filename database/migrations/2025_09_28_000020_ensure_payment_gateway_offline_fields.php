<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('payment_gateways')) {
            Schema::table('payment_gateways', function (Blueprint $table) {
                if (!Schema::hasColumn('payment_gateways', 'transfer_details')) {
                    $table->text('transfer_details')->nullable()->after('credentials');
                }
                if (!Schema::hasColumn('payment_gateways', 'require_proof')) {
                    $table->boolean('require_proof')->default(false)->after('transfer_details');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('payment_gateways')) {
            Schema::table('payment_gateways', function (Blueprint $table) {
                if (Schema::hasColumn('payment_gateways', 'require_proof')) {
                    $table->dropColumn('require_proof');
                }
                if (Schema::hasColumn('payment_gateways', 'transfer_details')) {
                    $table->dropColumn('transfer_details');
                }
            });
        }
    }
};
