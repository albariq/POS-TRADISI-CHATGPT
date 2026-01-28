<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_dll_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->decimal('dll_100', 12, 2)->nullable();
            $table->decimal('dll_200', 12, 2)->nullable();
            $table->decimal('dll_500', 12, 2)->nullable();
            $table->decimal('dll_1000', 12, 2)->nullable();
            $table->timestamps();

            $table->unique('outlet_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_dll_settings');
    }
};
