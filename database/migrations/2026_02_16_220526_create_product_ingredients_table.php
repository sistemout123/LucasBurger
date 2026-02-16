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
        Schema::create('product_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained();
            $table->decimal('quantity', 12, 4); // Quanto vai na receita crua/limpa

            // Fatores de Precisão
            $table->decimal('correction_factor', 8, 3)->default(1.000); // Peso Bruto / Peso Líquido
            $table->decimal('cooking_factor', 8, 3)->default(1.000);    // Peso Cozido / Peso Cru

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_ingredients');
    }
};
