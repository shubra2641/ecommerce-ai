<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixProductVariantsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                if (!Schema::hasColumn('product_variants', 'size')) {
                    $table->string('size')->nullable()->after('product_id');
                }
                if (!Schema::hasColumn('product_variants', 'color')) {
                    $table->string('color')->nullable()->after('size');
                }
                if (!Schema::hasColumn('product_variants', 'price')) {
                    $table->decimal('price', 12, 2)->nullable()->after('color');
                }
                if (!Schema::hasColumn('product_variants', 'stock')) {
                    $table->integer('stock')->default(0)->after('price');
                }
                if (!Schema::hasColumn('product_variants', 'sku')) {
                    $table->string('sku')->nullable()->after('stock');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                // don't drop columns in down to avoid accidental data loss in this fix migration
            });
        }
    }
}
