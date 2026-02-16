<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('livro_razao_estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transacao_id')->constrained('transacoes_estoque')->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained('ingredients');
            $table->foreignId('local_id')->constrained('locais_estoque');
            $table->foreignId('lote_id')->nullable()->constrained('lotes_ingrediente')->nullOnDelete();
            $table->string('status_estoque', 30)->default('DISPONIVEL');
            $table->decimal('qtd_anterior', 18, 4);
            $table->decimal('qtd_alteracao', 18, 4);
            $table->decimal('qtd_atual', 18, 4);
            $table->timestamps();

            $table->index(['ingredient_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livro_razao_estoque');
    }
};
