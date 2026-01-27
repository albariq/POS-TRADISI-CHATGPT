<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('cost_price_snapshot', 12, 2)->default(0)->after('unit_price');
            $table->decimal('cogs_total', 12, 2)->default(0)->after('cost_price_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['cost_price_snapshot', 'cogs_total']);
        });
    }
};
