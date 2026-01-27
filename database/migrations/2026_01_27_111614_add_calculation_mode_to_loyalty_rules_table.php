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
        Schema::table('loyalty_rules', function (Blueprint $table) {
            $table->string('calculation_mode', 50)
                ->default('per_amount')
                ->after('earn_rate_points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loyalty_rules', function (Blueprint $table) {
            $table->dropColumn('calculation_mode');
        });
    }
};
