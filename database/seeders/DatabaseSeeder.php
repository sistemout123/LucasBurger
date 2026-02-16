<?php

namespace Database\Seeders;

use App\Models\Almoxarifado;
use App\Models\Ingredient;
use App\Models\LocalEstoque;
use App\Models\LoteIngrediente;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\ReceitaIngrediente;
use App\Models\SaldoEstoque;
use App\Models\UnitOfMeasure;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin user ──
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Lucas Admin', 'password' => bcrypt('password')]
        );

        // ── Tipos de Transação ──
        $this->call(TiposTransacaoSeeder::class);

        // ── Unidades de Medida ──
        $un = UnitOfMeasure::firstOrCreate(['acronym' => 'un'], ['name' => 'Unidade', 'acronym' => 'un']);
        $g = UnitOfMeasure::firstOrCreate(['acronym' => 'g'], ['name' => 'Gramas', 'acronym' => 'g']);
        $kg = UnitOfMeasure::firstOrCreate(['acronym' => 'kg'], ['name' => 'Quilograma', 'acronym' => 'kg']);
        $ml = UnitOfMeasure::firstOrCreate(['acronym' => 'ml'], ['name' => 'Mililitro', 'acronym' => 'ml']);
        $l = UnitOfMeasure::firstOrCreate(['acronym' => 'L'], ['name' => 'Litro', 'acronym' => 'L']);

        // ── Ingredientes ──
        $ingredientes = [
            ['name' => 'Pão Brioche', 'unit_of_measure_id' => $un->id, 'unit_cost' => 1.20, 'current_stock' => 200, 'min_stock' => 50],
            ['name' => 'Blend Bovino 150g', 'unit_of_measure_id' => $g->id, 'unit_cost' => 5.50, 'current_stock' => 500, 'min_stock' => 100],
            ['name' => 'Queijo Cheddar (fatia)', 'unit_of_measure_id' => $un->id, 'unit_cost' => 0.80, 'current_stock' => 300, 'min_stock' => 80],
            ['name' => 'Queijo Prato (fatia)', 'unit_of_measure_id' => $un->id, 'unit_cost' => 0.70, 'current_stock' => 250, 'min_stock' => 60],
            ['name' => 'Bacon Fatiado', 'unit_of_measure_id' => $g->id, 'unit_cost' => 3.20, 'current_stock' => 400, 'min_stock' => 80],
            ['name' => 'Alface Americana', 'unit_of_measure_id' => $g->id, 'unit_cost' => 0.05, 'current_stock' => 600, 'min_stock' => 100],
            ['name' => 'Tomate (fatia)', 'unit_of_measure_id' => $un->id, 'unit_cost' => 0.15, 'current_stock' => 400, 'min_stock' => 80],
            ['name' => 'Cebola Caramelizada', 'unit_of_measure_id' => $g->id, 'unit_cost' => 0.08, 'current_stock' => 300, 'min_stock' => 50],
            ['name' => 'Molho Especial', 'unit_of_measure_id' => $ml->id, 'unit_cost' => 0.10, 'current_stock' => 500, 'min_stock' => 100],
            ['name' => 'Picles', 'unit_of_measure_id' => $g->id, 'unit_cost' => 0.06, 'current_stock' => 200, 'min_stock' => 40],
            ['name' => 'Ketchup', 'unit_of_measure_id' => $ml->id, 'unit_cost' => 0.03, 'current_stock' => 800, 'min_stock' => 200],
            ['name' => 'Mostarda', 'unit_of_measure_id' => $ml->id, 'unit_cost' => 0.04, 'current_stock' => 600, 'min_stock' => 150],
            ['name' => 'Maionese Artesanal', 'unit_of_measure_id' => $ml->id, 'unit_cost' => 0.12, 'current_stock' => 400, 'min_stock' => 80],
            ['name' => 'Óleo de Canola', 'unit_of_measure_id' => $l->id, 'unit_cost' => 9.90, 'current_stock' => 20, 'min_stock' => 5],
            ['name' => 'Batata Congelada', 'unit_of_measure_id' => $kg->id, 'unit_cost' => 12.00, 'current_stock' => 30, 'min_stock' => 10],
            ['name' => 'Coca-Cola Lata', 'unit_of_measure_id' => $un->id, 'unit_cost' => 2.50, 'current_stock' => 120, 'min_stock' => 48],
            ['name' => 'Ovo', 'unit_of_measure_id' => $un->id, 'unit_cost' => 0.60, 'current_stock' => 150, 'min_stock' => 30],
            ['name' => 'Cebola Roxa (anéis)', 'unit_of_measure_id' => $g->id, 'unit_cost' => 0.07, 'current_stock' => 200, 'min_stock' => 40],
        ];

        $ingredientModels = [];
        foreach ($ingredientes as $data) {
            $ingredientModels[] = Ingredient::firstOrCreate(['name' => $data['name']], $data);
        }

        // ── Ingredientes base adicionais para preparações ──
        $maioBase = Ingredient::firstOrCreate(['name' => 'Maionese Base'], ['name' => 'Maionese Base', 'tipo' => 'COMPRADO', 'unit_of_measure_id' => $ml->id, 'unit_cost' => 0.05, 'current_stock' => 2000, 'min_stock' => 500]);
        $alhoEmPo = Ingredient::firstOrCreate(['name' => 'Alho em Pó'], ['name' => 'Alho em Pó', 'tipo' => 'COMPRADO', 'unit_of_measure_id' => $g->id, 'unit_cost' => 0.15, 'current_stock' => 500, 'min_stock' => 100]);
        $limao = Ingredient::firstOrCreate(['name' => 'Suco de Limão'], ['name' => 'Suco de Limão', 'tipo' => 'COMPRADO', 'unit_of_measure_id' => $ml->id, 'unit_cost' => 0.08, 'current_stock' => 1000, 'min_stock' => 200]);
        $mel = Ingredient::firstOrCreate(['name' => 'Mel'], ['name' => 'Mel', 'tipo' => 'COMPRADO', 'unit_of_measure_id' => $ml->id, 'unit_cost' => 0.20, 'current_stock' => 500, 'min_stock' => 100]);
        $vinagre = Ingredient::firstOrCreate(['name' => 'Vinagre Balsâmico'], ['name' => 'Vinagre Balsâmico', 'tipo' => 'COMPRADO', 'unit_of_measure_id' => $ml->id, 'unit_cost' => 0.12, 'current_stock' => 800, 'min_stock' => 200]);

        // ── Preparações (semi-acabados) ──
        $maioTemp = Ingredient::firstOrCreate(
            ['name' => 'Maionese Temperada'],
            ['name' => 'Maionese Temperada', 'tipo' => 'PREPARACAO', 'unit_of_measure_id' => $ml->id, 'unit_cost' => 0, 'current_stock' => 0, 'min_stock' => 200, 'rendimento' => 530]
        );
        // Receita: 500ml Maionese Base + 20g Alho em Pó + 10ml Limão
        ReceitaIngrediente::firstOrCreate(['ingrediente_pai_id' => $maioTemp->id, 'ingrediente_filho_id' => $maioBase->id], ['ingrediente_pai_id' => $maioTemp->id, 'ingrediente_filho_id' => $maioBase->id, 'quantidade' => 500]);
        ReceitaIngrediente::firstOrCreate(['ingrediente_pai_id' => $maioTemp->id, 'ingrediente_filho_id' => $alhoEmPo->id], ['ingrediente_pai_id' => $maioTemp->id, 'ingrediente_filho_id' => $alhoEmPo->id, 'quantidade' => 20]);
        ReceitaIngrediente::firstOrCreate(['ingrediente_pai_id' => $maioTemp->id, 'ingrediente_filho_id' => $limao->id], ['ingrediente_pai_id' => $maioTemp->id, 'ingrediente_filho_id' => $limao->id, 'quantidade' => 10]);
        // Calcular custo unitário: (500*0.05 + 20*0.15 + 10*0.08) / 530 = R$ 0.0543
        $maioTemp->update(['unit_cost' => round((500 * 0.05 + 20 * 0.15 + 10 * 0.08) / 530, 4)]);

        $molhoBBQ = Ingredient::firstOrCreate(
            ['name' => 'Molho BBQ Artesanal'],
            ['name' => 'Molho BBQ Artesanal', 'tipo' => 'PREPARACAO', 'unit_of_measure_id' => $ml->id, 'unit_cost' => 0, 'current_stock' => 0, 'min_stock' => 200, 'rendimento' => 350]
        );
        // Receita: 200ml Ketchup + 50ml Mel + 50ml Vinagre + 30ml Mostarda + 20g Alho
        ReceitaIngrediente::firstOrCreate(['ingrediente_pai_id' => $molhoBBQ->id, 'ingrediente_filho_id' => $ingredientModels[10]->id], ['ingrediente_pai_id' => $molhoBBQ->id, 'ingrediente_filho_id' => $ingredientModels[10]->id, 'quantidade' => 200]);
        ReceitaIngrediente::firstOrCreate(['ingrediente_pai_id' => $molhoBBQ->id, 'ingrediente_filho_id' => $mel->id], ['ingrediente_pai_id' => $molhoBBQ->id, 'ingrediente_filho_id' => $mel->id, 'quantidade' => 50]);
        ReceitaIngrediente::firstOrCreate(['ingrediente_pai_id' => $molhoBBQ->id, 'ingrediente_filho_id' => $vinagre->id], ['ingrediente_pai_id' => $molhoBBQ->id, 'ingrediente_filho_id' => $vinagre->id, 'quantidade' => 50]);
        ReceitaIngrediente::firstOrCreate(['ingrediente_pai_id' => $molhoBBQ->id, 'ingrediente_filho_id' => $ingredientModels[11]->id], ['ingrediente_pai_id' => $molhoBBQ->id, 'ingrediente_filho_id' => $ingredientModels[11]->id, 'quantidade' => 30]);
        ReceitaIngrediente::firstOrCreate(['ingrediente_pai_id' => $molhoBBQ->id, 'ingrediente_filho_id' => $alhoEmPo->id], ['ingrediente_pai_id' => $molhoBBQ->id, 'ingrediente_filho_id' => $alhoEmPo->id, 'quantidade' => 20]);
        // Custo: (200*0.03 + 50*0.20 + 50*0.12 + 30*0.04 + 20*0.15) / 350 = R$ 0.0726
        $molhoBBQ->update(['unit_cost' => round((200 * 0.03 + 50 * 0.20 + 50 * 0.12 + 30 * 0.04 + 20 * 0.15) / 350, 4)]);

        // ── Produtos ──
        $smash = Product::firstOrCreate(['name' => 'Smash Burger Clássico'], ['name' => 'Smash Burger Clássico', 'price' => 28.90]);
        $bacon = Product::firstOrCreate(['name' => 'Bacon Burger'], ['name' => 'Bacon Burger', 'price' => 32.90]);
        $salada = Product::firstOrCreate(['name' => 'Salada Burger'], ['name' => 'Salada Burger', 'price' => 26.90]);
        $duplo = Product::firstOrCreate(['name' => 'Duplo Cheddar'], ['name' => 'Duplo Cheddar', 'price' => 35.90]);
        $kids = Product::firstOrCreate(['name' => 'Kids Burger'], ['name' => 'Kids Burger', 'price' => 18.90]);
        $fritas = Product::firstOrCreate(['name' => 'Porção de Fritas'], ['name' => 'Porção de Fritas', 'price' => 14.90]);

        // ── Ficha Técnica (product_ingredients) ──
        // Smash Burger: Pão, 2x Blend, 2x Cheddar, Cebola Caramelizada, Molho
        $this->fichaIngrediente($smash, $ingredientModels[0], 1);     // Pão
        $this->fichaIngrediente($smash, $ingredientModels[1], 300);   // 2x 150g Blend
        $this->fichaIngrediente($smash, $ingredientModels[2], 2);     // 2x Cheddar
        $this->fichaIngrediente($smash, $ingredientModels[7], 30);    // Cebola Caramelizada
        $this->fichaIngrediente($smash, $ingredientModels[8], 20);    // Molho Especial

        // Bacon Burger: Pão, Blend, Bacon, Queijo Prato, Maionese
        $this->fichaIngrediente($bacon, $ingredientModels[0], 1);
        $this->fichaIngrediente($bacon, $ingredientModels[1], 150);
        $this->fichaIngrediente($bacon, $ingredientModels[4], 40);
        $this->fichaIngrediente($bacon, $ingredientModels[3], 2);
        $this->fichaIngrediente($bacon, $ingredientModels[12], 15);

        // Salada Burger: Pão, Blend, Alface, Tomate, Cebola Roxa, Molho
        $this->fichaIngrediente($salada, $ingredientModels[0], 1);
        $this->fichaIngrediente($salada, $ingredientModels[1], 150);
        $this->fichaIngrediente($salada, $ingredientModels[5], 30);
        $this->fichaIngrediente($salada, $ingredientModels[6], 2);
        $this->fichaIngrediente($salada, $ingredientModels[17], 15);
        $this->fichaIngrediente($salada, $ingredientModels[8], 15);

        // Kids Burger: Pão, Mini Blend (100g), Queijo Prato, Ketchup
        $this->fichaIngrediente($kids, $ingredientModels[0], 1);
        $this->fichaIngrediente($kids, $ingredientModels[1], 100);
        $this->fichaIngrediente($kids, $ingredientModels[3], 1);
        $this->fichaIngrediente($kids, $ingredientModels[10], 20);

        // Fritas: Batata, Óleo
        $this->fichaIngrediente($fritas, $ingredientModels[14], 0.200);  // 200g
        $this->fichaIngrediente($fritas, $ingredientModels[13], 0.050);  // 50ml óleo

        // ── Almoxarifados e Locais ──
        $almox = Almoxarifado::firstOrCreate(
            ['codigo' => 'LOJA-01'],
            ['codigo' => 'LOJA-01', 'nome' => 'Loja Principal', 'endereco' => 'Rua dos Hambúrgueres, 42', 'esta_ativo' => true]
        );

        $geladeira = LocalEstoque::firstOrCreate(['almoxarifado_id' => $almox->id, 'codigo' => 'GEL-01'], ['almoxarifado_id' => $almox->id, 'codigo' => 'GEL-01', 'nome' => 'Geladeira Principal', 'tipo' => 'REFRIGERADO']);
        $freezer = LocalEstoque::firstOrCreate(['almoxarifado_id' => $almox->id, 'codigo' => 'FRZ-01'], ['almoxarifado_id' => $almox->id, 'codigo' => 'FRZ-01', 'nome' => 'Freezer', 'tipo' => 'CONGELADO']);
        $dispensa = LocalEstoque::firstOrCreate(['almoxarifado_id' => $almox->id, 'codigo' => 'DSP-01'], ['almoxarifado_id' => $almox->id, 'codigo' => 'DSP-01', 'nome' => 'Dispensa Seca', 'tipo' => 'SECO']);
        $balcao = LocalEstoque::firstOrCreate(['almoxarifado_id' => $almox->id, 'codigo' => 'BAL-01'], ['almoxarifado_id' => $almox->id, 'codigo' => 'BAL-01', 'nome' => 'Balcão / Linha de Produção', 'tipo' => 'AMBIENTE']);

        // ── Lotes de Ingredientes (perecíveis) ──
        $loteBlend = LoteIngrediente::firstOrCreate(
            ['ingredient_id' => $ingredientModels[1]->id, 'numero_lote' => 'BLEND-2026-001'],
            ['ingredient_id' => $ingredientModels[1]->id, 'numero_lote' => 'BLEND-2026-001', 'data_fabricacao' => '2026-02-10', 'data_validade' => '2026-02-25', 'fornecedor' => 'Frigorífico Boi Nobre']
        );
        $lotePao = LoteIngrediente::firstOrCreate(
            ['ingredient_id' => $ingredientModels[0]->id, 'numero_lote' => 'PAO-2026-012'],
            ['ingredient_id' => $ingredientModels[0]->id, 'numero_lote' => 'PAO-2026-012', 'data_fabricacao' => '2026-02-14', 'data_validade' => '2026-02-20', 'fornecedor' => 'Padaria Artesanal']
        );
        $loteBacon = LoteIngrediente::firstOrCreate(
            ['ingredient_id' => $ingredientModels[4]->id, 'numero_lote' => 'BCN-2026-003'],
            ['ingredient_id' => $ingredientModels[4]->id, 'numero_lote' => 'BCN-2026-003', 'data_fabricacao' => '2026-02-08', 'data_validade' => '2026-03-08', 'fornecedor' => 'Defumados Premium']
        );

        // ── Saldos de Estoque ──
        $saldos = [
            // Geladeira: perecíveis
            ['ingredient_id' => $ingredientModels[1]->id, 'local_id' => $geladeira->id, 'lote_id' => $loteBlend->id, 'quantidade' => 3000],    // Blend
            ['ingredient_id' => $ingredientModels[2]->id, 'local_id' => $geladeira->id, 'lote_id' => null, 'quantidade' => 300],     // Cheddar
            ['ingredient_id' => $ingredientModels[3]->id, 'local_id' => $geladeira->id, 'lote_id' => null, 'quantidade' => 250],     // Queijo Prato
            ['ingredient_id' => $ingredientModels[4]->id, 'local_id' => $geladeira->id, 'lote_id' => $loteBacon->id, 'quantidade' => 2000],    // Bacon
            ['ingredient_id' => $ingredientModels[5]->id, 'local_id' => $geladeira->id, 'lote_id' => null, 'quantidade' => 600],     // Alface
            ['ingredient_id' => $ingredientModels[6]->id, 'local_id' => $geladeira->id, 'lote_id' => null, 'quantidade' => 400],     // Tomate
            ['ingredient_id' => $ingredientModels[16]->id, 'local_id' => $geladeira->id, 'lote_id' => null, 'quantidade' => 150],     // Ovo

            // Freezer: congelados
            ['ingredient_id' => $ingredientModels[14]->id, 'local_id' => $freezer->id, 'lote_id' => null, 'quantidade' => 30],      // Batata Congelada (kg)

            // Dispensa: secos/líquidos
            ['ingredient_id' => $ingredientModels[0]->id, 'local_id' => $dispensa->id, 'lote_id' => $lotePao->id, 'quantidade' => 200],     // Pão
            ['ingredient_id' => $ingredientModels[8]->id, 'local_id' => $dispensa->id, 'lote_id' => null, 'quantidade' => 500],     // Molho Especial
            ['ingredient_id' => $ingredientModels[9]->id, 'local_id' => $dispensa->id, 'lote_id' => null, 'quantidade' => 200],     // Picles
            ['ingredient_id' => $ingredientModels[10]->id, 'local_id' => $dispensa->id, 'lote_id' => null, 'quantidade' => 800],     // Ketchup
            ['ingredient_id' => $ingredientModels[11]->id, 'local_id' => $dispensa->id, 'lote_id' => null, 'quantidade' => 600],     // Mostarda
            ['ingredient_id' => $ingredientModels[12]->id, 'local_id' => $dispensa->id, 'lote_id' => null, 'quantidade' => 400],     // Maionese
            ['ingredient_id' => $ingredientModels[13]->id, 'local_id' => $dispensa->id, 'lote_id' => null, 'quantidade' => 20],      // Óleo (litros)
            ['ingredient_id' => $ingredientModels[15]->id, 'local_id' => $dispensa->id, 'lote_id' => null, 'quantidade' => 120],     // Coca-Cola

            // Balcão: cebola e produção
            ['ingredient_id' => $ingredientModels[7]->id, 'local_id' => $balcao->id, 'lote_id' => null, 'quantidade' => 300],     // Cebola Caramelizada
            ['ingredient_id' => $ingredientModels[17]->id, 'local_id' => $balcao->id, 'lote_id' => null, 'quantidade' => 200],     // Cebola Roxa
        ];

        foreach ($saldos as $s) {
            SaldoEstoque::firstOrCreate(
                [
                    'ingredient_id' => $s['ingredient_id'],
                    'local_id' => $s['local_id'],
                    'lote_id' => $s['lote_id'],
                    'status_estoque' => 'DISPONIVEL',
                ],
                [
                    'ingredient_id' => $s['ingredient_id'],
                    'local_id' => $s['local_id'],
                    'lote_id' => $s['lote_id'],
                    'status_estoque' => 'DISPONIVEL',
                    'quantidade' => $s['quantidade'],
                    'ultima_movimentacao_em' => now(),
                ]
            );
        }
    }

    private function fichaIngrediente(Product $product, Ingredient $ingredient, float $quantity): void
    {
        ProductIngredient::firstOrCreate(
            ['product_id' => $product->id, 'ingredient_id' => $ingredient->id],
            ['product_id' => $product->id, 'ingredient_id' => $ingredient->id, 'quantity' => $quantity]
        );
    }
}
