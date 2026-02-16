<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lotes_ingrediente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->onDelete('cascade');
            $table->string('numero_lote', 100);
            $table->date('data_fabricacao')->nullable();
            $table->date('data_validade')->nullable();
            $table->string('fornecedor', 255)->nullable();
            $table->timestamps();

            $table->unique(['ingredient_id', 'numero_lote']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes_ingrediente');
    }
};
