<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('receita_ingredientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingrediente_pai_id')->constrained('ingredients')->cascadeOnDelete();
            $table->foreignId('ingrediente_filho_id')->constrained('ingredients')->cascadeOnDelete();
            $table->decimal('quantidade', 12, 4);
            $table->timestamps();

            $table->unique(['ingrediente_pai_id', 'ingrediente_filho_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receita_ingredientes');
    }
};
