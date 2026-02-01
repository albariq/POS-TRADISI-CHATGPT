<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laba_rugi_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('jenis', 20);
            $table->string('kategori', 100);
            $table->string('deskripsi', 255)->nullable();
            $table->decimal('nominal', 15, 2);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['outlet_id', 'tanggal']);
            $table->index(['outlet_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laba_rugi_entries');
    }
};
