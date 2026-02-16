<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contagens_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campanha_id')->constrained('campanhas_inventario')->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained('ingredients');
            $table->foreignId('local_id')->constrained('locais_estoque');
            $table->foreignId('lote_id')->nullable()->constrained('lotes_ingrediente')->nullOnDelete();
            $table->decimal('qtd_esperada', 18, 4);
            $table->decimal('qtd_contada', 18, 4)->nullable();
            $table->decimal('discrepancia', 18, 4)->nullable();
            $table->foreignId('auditor_id')->nullable()->constrained('users');
            $table->dateTime('contado_em')->nullable();
            $table->boolean('foi_conciliado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contagens_inventario');
    }
};
