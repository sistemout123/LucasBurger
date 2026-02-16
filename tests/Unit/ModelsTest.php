<?php

namespace Tests\Unit;

use App\Models\Almoxarifado;
use App\Models\Ingredient;
use App\Models\LocalEstoque;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\SaldoEstoque;
use App\Models\UnitOfMeasure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelsTest extends TestCase
{
    use RefreshDatabase;

    // ── UnitOfMeasure ──
    public function test_unit_of_measure_creation(): void
    {
        $un = UnitOfMeasure::create(['name' => 'Quilograma', 'acronym' => 'kg']);
        $this->assertDatabaseHas('unit_of_measures', ['acronym' => 'kg']);
        $this->assertEquals('kg', $un->acronym);
    }

    // ── Almoxarifado ──
    public function test_almoxarifado_creation_and_locals_relationship(): void
    {
        $almox = Almoxarifado::create(['codigo' => 'A01', 'nome' => 'Almox Central', 'esta_ativo' => true]);
        $local = LocalEstoque::create([
            'almoxarifado_id' => $almox->id,
            'codigo' => 'GEL-01',
            'nome' => 'Geladeira',
            'tipo' => 'REFRIGERADO',
            'esta_ativo' => true,
        ]);

        $this->assertDatabaseHas('almoxarifados', ['codigo' => 'A01']);
        $this->assertCount(1, $almox->locais);
        $this->assertEquals('Almox Central', $local->almoxarifado->nome);
    }

    // ── Product ──
    public function test_product_creation(): void
    {
        $product = Product::create(['name' => 'Hambúrguer', 'price' => 28.90]);

        $this->assertDatabaseHas('products', ['name' => 'Hambúrguer']);
        $this->assertEqualsWithDelta(28.90, (float) $product->price, 0.01);
    }

    // ── ProductIngredient (ficha técnica) ──
    public function test_product_ingredient_relationship(): void
    {
        $un = UnitOfMeasure::create(['name' => 'Unidade', 'acronym' => 'un']);
        $pao = Ingredient::create(['name' => 'Pão', 'unit_of_measure_id' => $un->id, 'unit_cost' => 1.20, 'current_stock' => 0, 'min_stock' => 0]);
        $carne = Ingredient::create(['name' => 'Carne', 'unit_of_measure_id' => $un->id, 'unit_cost' => 5.50, 'current_stock' => 0, 'min_stock' => 0]);

        $product = Product::create(['name' => 'Burger', 'price' => 25]);

        ProductIngredient::create(['product_id' => $product->id, 'ingredient_id' => $pao->id, 'quantity' => 1]);
        ProductIngredient::create(['product_id' => $product->id, 'ingredient_id' => $carne->id, 'quantity' => 150]);

        $this->assertCount(2, $product->productIngredients);
        $this->assertCount(1, $pao->products);
    }

    // ── SaldoEstoque ──
    public function test_saldo_estoque_creation(): void
    {
        $un = UnitOfMeasure::create(['name' => 'g', 'acronym' => 'g']);
        $ingredient = Ingredient::create(['name' => 'Bacon', 'unit_of_measure_id' => $un->id, 'unit_cost' => 3, 'current_stock' => 0, 'min_stock' => 0]);
        $almox = Almoxarifado::create(['codigo' => 'A01', 'nome' => 'Almox', 'esta_ativo' => true]);
        $local = LocalEstoque::create(['almoxarifado_id' => $almox->id, 'codigo' => 'L01', 'nome' => 'Local', 'tipo' => 'SECO', 'esta_ativo' => true]);

        $saldo = SaldoEstoque::create([
            'ingredient_id' => $ingredient->id,
            'local_id' => $local->id,
            'status_estoque' => 'DISPONIVEL',
            'quantidade' => 500,
            'ultima_movimentacao_em' => now(),
        ]);

        $this->assertDatabaseHas('saldos_estoque', ['quantidade' => 500]);
        $this->assertEquals('Bacon', $saldo->ingredient->name);
        $this->assertEquals('Local', $saldo->local->nome);
    }

    // ── LocalEstoque types ──
    public function test_local_estoque_types(): void
    {
        $almox = Almoxarifado::create(['codigo' => 'A01', 'nome' => 'Test', 'esta_ativo' => true]);

        $tipos = ['REFRIGERADO', 'CONGELADO', 'SECO', 'AMBIENTE'];
        foreach ($tipos as $tipo) {
            $local = LocalEstoque::create([
                'almoxarifado_id' => $almox->id,
                'codigo' => "L-{$tipo}",
                'nome' => "Local {$tipo}",
                'tipo' => $tipo,
                'esta_ativo' => true,
            ]);
            $this->assertEquals($tipo, $local->tipo);
        }
    }
}
