<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
        });

        DB::statement('ALTER TABLE cash_movements MODIFY shift_id BIGINT UNSIGNED NULL');

        Schema::table('cash_movements', function (Blueprint $table) {
            $table->foreign('shift_id')->references('id')->on('shifts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
        });

        DB::statement('ALTER TABLE cash_movements MODIFY shift_id BIGINT UNSIGNED NOT NULL');

        Schema::table('cash_movements', function (Blueprint $table) {
            $table->foreign('shift_id')->references('id')->on('shifts')->cascadeOnDelete();
        });
    }
};
