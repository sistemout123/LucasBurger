<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->string('tipo', 20)->default('COMPRADO')->after('name'); // COMPRADO | PREPARACAO
            $table->decimal('rendimento', 12, 4)->nullable()->after('min_stock'); // Qty que a receita produz
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'rendimento']);
        });
    }
};
