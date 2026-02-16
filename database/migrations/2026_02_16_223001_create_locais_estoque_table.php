<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('locais_estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('almoxarifado_id')->constrained('almoxarifados')->onDelete('cascade');
            $table->string('codigo', 50);
            $table->string('nome', 100);
            $table->string('tipo', 30)->default('AMBIENTE'); // REFRIGERADO, CONGELADO, SECO, AMBIENTE
            $table->boolean('esta_ativo')->default(true);
            $table->timestamps();

            $table->unique(['almoxarifado_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locais_estoque');
    }
};
