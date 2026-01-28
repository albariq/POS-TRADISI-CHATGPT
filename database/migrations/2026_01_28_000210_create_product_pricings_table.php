<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_pricing_extra_id')->constrained('product_pricing_extras')->cascadeOnDelete();
            $table->unsignedInteger('grams');
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->decimal('margin', 12, 2)->nullable();
            $table->timestamps();

            $table->unique(['product_pricing_extra_id', 'grams']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_pricings');
    }
};
