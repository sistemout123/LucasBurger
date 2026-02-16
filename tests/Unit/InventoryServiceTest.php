<?php

namespace Tests\Unit;

use App\Models\Ingredient;
use App\Models\LivroRazaoEstoque;
use App\Models\LocalEstoque;
use App\Models\Almoxarifado;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\ReceitaIngrediente;
use App\Models\SaldoEstoque;
use App\Models\TipoTransacao;
use App\Models\UnitOfMeasure;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $service;
    private User $user;
    private LocalEstoque $local;
    private Ingredient $ingredient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new InventoryService();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $un = UnitOfMeasure::create(['name' => 'Gramas', 'acronym' => 'g']);

        $almox = Almoxarifado::create([
            'codigo' => 'A01',
            'nome' => 'Almoxarifado Teste',
            'esta_ativo' => true,
        ]);

        $this->local = LocalEstoque::create([
            'almoxarifado_id' => $almox->id,
            'codigo' => 'L01',
            'nome' => 'Local Teste',
            'tipo' => 'SECO',
            'esta_ativo' => true,
        ]);

        $this->ingredient = Ingredient::create([
            'name' => 'Bacon',
            'tipo' => 'COMPRADO',
            'unit_of_measure_id' => $un->id,
            'unit_cost' => 3.20,
            'current_stock' => 0,
            'min_stock' => 10,
        ]);

        // Seed tipos_transacao for the test
        $tiposData = [
            ['id' => 'ENTRADA_COMPRA', 'descricao' => 'Entrada por compra', 'tipo_impacto' => '+'],
            ['id' => 'ENTRADA_PRODUCAO', 'descricao' => 'Entrada por produção', 'tipo_impacto' => '+'],
            ['id' => 'SAIDA_PRODUCAO', 'descricao' => 'Saída para produção', 'tipo_impacto' => '-'],
            ['id' => 'SAIDA_VENDA', 'descricao' => 'Saída por venda', 'tipo_impacto' => '-'],
            ['id' => 'TRANSFERENCIA', 'descricao' => 'Transferência', 'tipo_impacto' => 'N'],
            ['id' => 'AJUSTE_INVENTARIO', 'descricao' => 'Ajuste inventário', 'tipo_impacto' => 'N'],
        ];
        foreach ($tiposData as $tipo) {
            TipoTransacao::create($tipo);
        }
    }

    // ── registrarEntrada ──
    public function test_registrar_entrada_creates_transaction_and_updates_balance(): void
    {
        $transacao = $this->service->registrarEntrada(
            ingredientId: $this->ingredient->id,
            localId: $this->local->id,
            quantidade: 100,
            userId: $this->user->id,
            docReferencia: 'NF-001'
        );

        $this->assertNotNull($transacao);
        $this->assertEquals('ENTRADA_COMPRA', $transacao->tipo_id);
        $this->assertEquals('NF-001', $transacao->doc_referencia);

        $saldo = SaldoEstoque::where('ingredient_id', $this->ingredient->id)
            ->where('local_id', $this->local->id)
            ->first();

        $this->assertNotNull($saldo);
        $this->assertEqualsWithDelta(100.0, (float) $saldo->quantidade, 0.01);

        // Verify livro razao
        $razao = LivroRazaoEstoque::where('transacao_id', $transacao->id)->first();
        $this->assertEqualsWithDelta(0.0, (float) $razao->qtd_anterior, 0.01);
        $this->assertEqualsWithDelta(100.0, (float) $razao->qtd_alteracao, 0.01);
        $this->assertEqualsWithDelta(100.0, (float) $razao->qtd_atual, 0.01);
    }

    // ── registrarSaida ──
    public function test_registrar_saida_decrements_balance(): void
    {
        $this->service->registrarEntrada($this->ingredient->id, $this->local->id, 200, $this->user->id);

        $tx = $this->service->registrarSaida(
            ingredientId: $this->ingredient->id,
            localId: $this->local->id,
            quantidade: 50,
            userId: $this->user->id
        );

        $this->assertEquals('SAIDA_PRODUCAO', $tx->tipo_id);
        $this->assertEqualsWithDelta(150.0, $this->service->calcularSaldo($this->ingredient->id, $this->local->id), 0.01);
    }

    // ── registrarVenda ──
    public function test_registrar_venda_consumes_from_ficha_tecnica(): void
    {
        $un = UnitOfMeasure::first();
        $pao = Ingredient::create(['name' => 'Pão', 'tipo' => 'COMPRADO', 'unit_of_measure_id' => $un->id, 'unit_cost' => 1, 'current_stock' => 0, 'min_stock' => 0]);
        $queijo = Ingredient::create(['name' => 'Queijo', 'tipo' => 'COMPRADO', 'unit_of_measure_id' => $un->id, 'unit_cost' => 0.80, 'current_stock' => 0, 'min_stock' => 0]);

        $produto = Product::create(['name' => 'Hambúrguer', 'price' => 20.00]);
        ProductIngredient::create(['product_id' => $produto->id, 'ingredient_id' => $pao->id, 'quantity' => 1]);
        ProductIngredient::create(['product_id' => $produto->id, 'ingredient_id' => $queijo->id, 'quantity' => 2]);

        // Stock up
        $this->service->registrarEntrada($pao->id, $this->local->id, 50, $this->user->id);
        $this->service->registrarEntrada($queijo->id, $this->local->id, 100, $this->user->id);

        // Sell 3 burgers
        $tx = $this->service->registrarVenda($produto, 3, $this->local->id, $this->user->id);

        $this->assertEquals('SAIDA_VENDA', $tx->tipo_id);
        $this->assertEqualsWithDelta(47.0, $this->service->calcularSaldo($pao->id, $this->local->id), 0.01); // 50 - 3*1
        $this->assertEqualsWithDelta(94.0, $this->service->calcularSaldo($queijo->id, $this->local->id), 0.01); // 100 - 3*2
    }

    // ── transferir ──
    public function test_transferir_moves_stock_between_locations(): void
    {
        $almox = Almoxarifado::first();
        $local2 = LocalEstoque::create(['almoxarifado_id' => $almox->id, 'codigo' => 'L02', 'nome' => 'Local 2', 'tipo' => 'REFRIGERADO', 'esta_ativo' => true]);

        $this->service->registrarEntrada($this->ingredient->id, $this->local->id, 100, $this->user->id);

        $tx = $this->service->transferir(
            ingredientId: $this->ingredient->id,
            localOrigemId: $this->local->id,
            localDestinoId: $local2->id,
            quantidade: 30,
            userId: $this->user->id
        );

        $this->assertEquals('TRANSFERENCIA', $tx->tipo_id);
        $this->assertEqualsWithDelta(70.0, $this->service->calcularSaldo($this->ingredient->id, $this->local->id), 0.01);
        $this->assertEqualsWithDelta(30.0, $this->service->calcularSaldo($this->ingredient->id, $local2->id), 0.01);
    }

    // ── ajustarInventario ──
    public function test_ajustar_inventario(): void
    {
        $this->service->registrarEntrada($this->ingredient->id, $this->local->id, 100, $this->user->id);

        // Adjust downward by -15
        $tx = $this->service->ajustarInventario(
            ingredientId: $this->ingredient->id,
            localId: $this->local->id,
            quantidadeAjuste: -15,
            userId: $this->user->id
        );

        $this->assertEquals('AJUSTE_INVENTARIO', $tx->tipo_id);
        $this->assertEqualsWithDelta(85.0, $this->service->calcularSaldo($this->ingredient->id, $this->local->id), 0.01);
    }

    // ── calcularSaldo ──
    public function test_calcular_saldo_summed_across_locations(): void
    {
        $almox = Almoxarifado::first();
        $local2 = LocalEstoque::create(['almoxarifado_id' => $almox->id, 'codigo' => 'L02', 'nome' => 'Local 2', 'tipo' => 'REFRIGERADO', 'esta_ativo' => true]);

        $this->service->registrarEntrada($this->ingredient->id, $this->local->id, 100, $this->user->id);
        $this->service->registrarEntrada($this->ingredient->id, $local2->id, 50, $this->user->id);

        $this->assertEqualsWithDelta(150.0, $this->service->calcularSaldo($this->ingredient->id), 0.01);
        $this->assertEqualsWithDelta(100.0, $this->service->calcularSaldo($this->ingredient->id, $this->local->id), 0.01);
    }

    // ── produzirPreparacao ──
    public function test_produzir_preparacao_consumes_and_produces(): void
    {
        $un = UnitOfMeasure::first();
        $base = Ingredient::create(['name' => 'Maionese Base', 'tipo' => 'COMPRADO', 'unit_of_measure_id' => $un->id, 'unit_cost' => 0.05, 'current_stock' => 0, 'min_stock' => 0]);
        $alho = Ingredient::create(['name' => 'Alho', 'tipo' => 'COMPRADO', 'unit_of_measure_id' => $un->id, 'unit_cost' => 0.15, 'current_stock' => 0, 'min_stock' => 0]);

        $prep = Ingredient::create(['name' => 'Maionese Temperada', 'tipo' => 'PREPARACAO', 'unit_of_measure_id' => $un->id, 'unit_cost' => 0, 'current_stock' => 0, 'min_stock' => 0, 'rendimento' => 520]);

        ReceitaIngrediente::create(['ingrediente_pai_id' => $prep->id, 'ingrediente_filho_id' => $base->id, 'quantidade' => 500]);
        ReceitaIngrediente::create(['ingrediente_pai_id' => $prep->id, 'ingrediente_filho_id' => $alho->id, 'quantidade' => 20]);

        // Stock up bases
        $this->service->registrarEntrada($base->id, $this->local->id, 2000, $this->user->id);
        $this->service->registrarEntrada($alho->id, $this->local->id, 200, $this->user->id);

        // Produce 520 units (1 batch)
        $tx = $this->service->produzirPreparacao(
            ingredientId: $prep->id,
            localId: $this->local->id,
            quantidade: 520,
            userId: $this->user->id
        );

        // Base consumed: 500 * (520/520) = 500
        $this->assertEqualsWithDelta(1500.0, $this->service->calcularSaldo($base->id, $this->local->id), 0.01);
        // Alho consumed: 20 * (520/520) = 20
        $this->assertEqualsWithDelta(180.0, $this->service->calcularSaldo($alho->id, $this->local->id), 0.01);
        // Prep produced
        $this->assertEqualsWithDelta(520.0, $this->service->calcularSaldo($prep->id, $this->local->id), 0.01);

        // Verify cost was updated
        $prep->refresh();
        $this->assertGreaterThan(0, (float) $prep->unit_cost);
    }

    public function test_produzir_preparacao_fails_for_comprado(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->produzirPreparacao(
            ingredientId: $this->ingredient->id,  // COMPRADO
            localId: $this->local->id,
            quantidade: 10,
            userId: $this->user->id
        );
    }

    public function test_produzir_preparacao_fails_without_recipe(): void
    {
        $un = UnitOfMeasure::first();
        $prep = Ingredient::create(['name' => 'Empty Prep', 'tipo' => 'PREPARACAO', 'unit_of_measure_id' => $un->id, 'unit_cost' => 0, 'current_stock' => 0, 'min_stock' => 0, 'rendimento' => 100]);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->produzirPreparacao(
            ingredientId: $prep->id,
            localId: $this->local->id,
            quantidade: 50,
            userId: $this->user->id
        );
    }
}
