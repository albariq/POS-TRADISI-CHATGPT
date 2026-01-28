<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('grams');
            $table->unsignedInteger('packaging_cost');
            $table->decimal('markup', 6, 3);
            $table->timestamps();

            $table->unique(['outlet_id', 'grams']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_settings');
    }
};
