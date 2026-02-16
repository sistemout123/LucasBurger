<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campanhas_inventario', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 255);
            $table->string('tipo', 20)->default('GERAL'); // GERAL, CICLICO
            $table->string('status', 20)->default('ABERTA'); // ABERTA, EM_ANDAMENTO, ENCERRADA
            $table->dateTime('encerrado_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campanhas_inventario');
    }
};
