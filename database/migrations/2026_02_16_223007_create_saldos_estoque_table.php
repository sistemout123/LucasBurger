<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('saldos_estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients');
            $table->foreignId('local_id')->constrained('locais_estoque');
            $table->foreignId('lote_id')->nullable()->constrained('lotes_ingrediente')->nullOnDelete();
            $table->string('status_estoque', 30)->default('DISPONIVEL');
            $table->decimal('quantidade', 18, 4)->default(0);
            $table->dateTime('ultima_movimentacao_em')->nullable();
            $table->timestamps();

            $table->unique(['ingredient_id', 'local_id', 'lote_id', 'status_estoque'], 'uk_saldos_dimensao');
            $table->index(['ingredient_id', 'status_estoque']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saldos_estoque');
    }
};
