<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('grams_per_unit', 12, 2)->default(0)->after('barcode');
        });

        Schema::table('inventory_stocks', function (Blueprint $table) {
            $table->decimal('qty_grams', 12, 2)->default(0)->after('product_variant_id');
            $table->decimal('min_qty_grams', 12, 2)->default(0)->after('qty_grams');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('qty_grams', 12, 2)->default(0)->after('type');
            $table->decimal('before_qty_grams', 12, 2)->default(0)->after('qty_grams');
            $table->decimal('after_qty_grams', 12, 2)->default(0)->after('before_qty_grams');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('grams_per_unit', 12, 2)->default(0)->after('qty');
            $table->decimal('grams_total', 12, 2)->default(0)->after('grams_per_unit');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['grams_total', 'grams_per_unit']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['after_qty_grams', 'before_qty_grams', 'qty_grams']);
        });

        Schema::table('inventory_stocks', function (Blueprint $table) {
            $table->dropColumn(['min_qty_grams', 'qty_grams']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('grams_per_unit');
        });
    }
};
