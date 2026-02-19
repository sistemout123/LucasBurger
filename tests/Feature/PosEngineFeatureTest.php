<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Table;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\ProductIngredient;
use App\Models\LocalEstoque;
use App\Models\SaldoEstoque;
use App\Enums\TableStatus;
use App\Enums\OrderStatus;

class PosEngineFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $almoxarifado;
    protected $local;
    protected $breadIngredient;
    protected $meatIngredient;
    protected $burgerProduct;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup User for foreign key constraints
        $this->user = \App\Models\User::factory()->create(['id' => 1]);

        \Illuminate\Support\Facades\DB::table('tipos_transacao')->insert([
            'id' => 'SAIDA_VENDA',
            'descricao' => 'Saída Venda',
            'tipo_impacto' => '-'
        ]);

        // Setup base inventory data
        $this->almoxarifado = \App\Models\Almoxarifado::create(['nome' => 'Almoxarifado Geral', 'codigo' => 'ALM-001', 'is_active' => true]);
        $this->local = LocalEstoque::create(['nome' => 'Main Kitchen', 'codigo' => 'LOC-001', 'almoxarifado_id' => $this->almoxarifado->id, 'is_active' => true]);

        $this->breadIngredient = Ingredient::create([
            'name' => 'Hamburger Bun',
            'unit_cost' => 0.50,
            'unit_of_measure_id' => \App\Models\UnitOfMeasure::create(['name' => 'Unit', 'acronym' => 'Un'])->id
        ]);

        $this->meatIngredient = Ingredient::create([
            'name' => 'Burger Patty 150g',
            'unit_cost' => 0.05,
            'unit_of_measure_id' => \App\Models\UnitOfMeasure::create(['name' => 'Grams', 'acronym' => 'g'])->id
        ]);

        // Add 10 Buns and 1000g of Meat to Stock
        SaldoEstoque::create([
            'local_id' => $this->local->id,
            'ingredient_id' => $this->breadIngredient->id,
            'quantidade' => 10
        ]);
        SaldoEstoque::create([
            'local_id' => $this->local->id,
            'ingredient_id' => $this->meatIngredient->id,
            'quantidade' => 1000
        ]);

        // Setup Product Recipe
        $this->burgerProduct = Product::create(['name' => 'Classic Burger', 'price' => 25.00]);

        ProductIngredient::create([
            'product_id' => $this->burgerProduct->id,
            'ingredient_id' => $this->breadIngredient->id,
            'quantity' => 1 // 1 bun
        ]);

        ProductIngredient::create([
            'product_id' => $this->burgerProduct->id,
            'ingredient_id' => $this->meatIngredient->id,
            'quantity' => 150 // 150g of meat
        ]);
    }

    public function test_it_creates_order_for_table_and_changes_status()
    {
        $this->withoutExceptionHandling();
        $table = Table::create(['number' => '1', 'status' => TableStatus::FREE->value]);

        $response = $this->postJson('/api/pos/orders', [
            'table_id' => $table->id,
            'items' => [
                [
                    'product_id' => $this->burgerProduct->id,
                    'quantity' => 2,
                    'unit_price' => 25.00
                ]
            ]
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('total_amount', 50)
            ->assertJsonPath('status', 'open');

        $this->assertDatabaseHas('tables', [
            'id' => $table->id,
            'status' => 'occupied'
        ]);
    }

    public function test_payment_completes_order_and_deducts_inventory()
    {
        $this->withoutExceptionHandling();
        $table = Table::create(['number' => '1', 'status' => TableStatus::FREE->value]);

        $orderResponse = $this->postJson('/api/pos/orders', [
            'table_id' => $table->id,
            'items' => [
                [
                    'product_id' => $this->burgerProduct->id,
                    'quantity' => 2, // 2 burgers = 2 buns, 300g meat
                    'unit_price' => 25.00
                ]
            ]
        ]);

        $orderId = $orderResponse->json('id');

        // Stock before payment should NOT change
        $this->assertEquals(10, SaldoEstoque::where('ingredient_id', $this->breadIngredient->id)->first()->quantidade);

        // Pay Order
        $paymentResponse = $this->postJson("/api/pos/orders/{$orderId}/payments", [
            'amount' => 50.00,
            'method' => 'pix'
        ]);

        $paymentResponse->assertStatus(201);

        // Verify Order Paid
        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'paid'
        ]);

        // Verify Table freed
        $this->assertDatabaseHas('tables', [
            'id' => $table->id,
            'status' => 'free'
        ]);

        // Verify Inventory Deducted (Started with 10 buns and 1000g meat)
        $this->assertEquals(8, SaldoEstoque::where('ingredient_id', $this->breadIngredient->id)->first()->quantidade, 'Buns not deducted properly');
        $this->assertEquals(700, SaldoEstoque::where('ingredient_id', $this->meatIngredient->id)->first()->quantidade, 'Meat not deducted properly');

        // Ensure transactions were written
        $this->assertDatabaseHas('transacoes_estoque', [
            'tipo_id' => 'SAIDA_VENDA',
            'solicitado_por' => 1
        ]);

        $this->assertDatabaseHas('livro_razao_estoque', [
            'ingredient_id' => $this->breadIngredient->id,
            'status_estoque' => 'DISPONIVEL',
            'qtd_alteracao' => -2
        ]);
    }
}
