<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangePaymentMethodToString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Use raw statement to avoid requiring doctrine/dbal
        // Execute only for MySQL connections to keep tests using sqlite compatible
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY `payment_method` VARCHAR(100) NOT NULL DEFAULT 'cod'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to the original enum (may fail if values incompatible)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY `payment_method` ENUM('cod','paypal') NOT NULL DEFAULT 'cod'");
        }
    }
}
