<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MigrateLegacySizesToVariants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('products') || !Schema::hasTable('product_variants')) {
            return;
        }

        // For each product that has a non-empty size CSV and no variants, create variant rows
        $products = DB::table('products')
            ->select('id', 'size', 'price', 'stock')
            ->whereNotNull('size')
            ->where('size', '!=', '')
            ->get();

        foreach ($products as $p) {
            $existing = DB::table('product_variants')->where('product_id', $p->id)->exists();
            if ($existing) continue;

            $sizes = array_map('trim', explode(',', $p->size));
            foreach ($sizes as $s) {
                if ($s === '') continue;
                DB::table('product_variants')->insert([
                    'product_id' => $p->id,
                    'size' => $s,
                    'color' => null,
                    'price' => $p->price,
                    'stock' => $p->stock ?? 0,
                    'sku' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // no-op (non-destructive)
    }
}
