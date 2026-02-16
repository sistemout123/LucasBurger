<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained();
            // $table->foreignId('user_id')->constrained(); // Quem realizou a ação - Commented out for now as user_id might need handling if auth not fully set up or we use current auth. Actually tables usually have users.
            $table->foreignId('user_id')->constrained();
            $table->decimal('quantity', 12, 4);
            $table->enum('type', ['in', 'out', 'adjustment', 'waste']); // Entrada, Saída, Ajuste, Perda
            $table->string('reason')->nullable(); // Ex: "Venda Pedido #123" ou "Tomate estragado"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
