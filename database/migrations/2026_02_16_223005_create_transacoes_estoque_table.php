<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transacoes_estoque', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_id', 30);
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->unsignedInteger('quantidade_produtos')->nullable();
            $table->string('doc_referencia', 100)->nullable();
            $table->foreignId('solicitado_por')->constrained('users');
            $table->foreignId('autorizado_por')->nullable()->constrained('users');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->foreign('tipo_id')->references('id')->on('tipos_transacao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transacoes_estoque');
    }
};
