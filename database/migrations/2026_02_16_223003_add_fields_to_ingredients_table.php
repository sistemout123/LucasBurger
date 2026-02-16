<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->boolean('controle_lote')->default(false)->after('min_stock');
            $table->decimal('estoque_maximo', 12, 4)->default(0)->after('min_stock');
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn(['controle_lote', 'estoque_maximo']);
        });
    }
};
