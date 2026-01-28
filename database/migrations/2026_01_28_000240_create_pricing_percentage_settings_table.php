<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_percentage_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->decimal('pct_100', 6, 3)->nullable();
            $table->decimal('pct_200', 6, 3)->nullable();
            $table->decimal('pct_500', 6, 3)->nullable();
            $table->decimal('pct_1000', 6, 3)->nullable();
            $table->timestamps();

            $table->unique('outlet_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_percentage_settings');
    }
};
