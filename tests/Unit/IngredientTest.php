<?php

namespace Tests\Unit;

use App\Models\Ingredient;
use App\Models\ReceitaIngrediente;
use App\Models\UnitOfMeasure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientTest extends TestCase
{
    use RefreshDatabase;

    private function createUnit(): UnitOfMeasure
    {
        return UnitOfMeasure::create(['name' => 'Mililitro', 'acronym' => 'ml']);
    }

    public function test_create_comprado_ingredient(): void
    {
        $un = $this->createUnit();
        $ingredient = Ingredient::create([
            'name' => 'Maionese Base',
            'tipo' => Ingredient::TIPO_COMPRADO,
            'unit_of_measure_id' => $un->id,
            'unit_cost' => 0.05,
            'current_stock' => 100,
            'min_stock' => 20,
        ]);

        $this->assertDatabaseHas('ingredients', ['name' => 'Maionese Base']);
        $this->assertFalse($ingredient->isPreparacao());
        $this->assertEquals('COMPRADO', $ingredient->tipo);
    }

    public function test_create_preparacao_ingredient(): void
    {
        $un = $this->createUnit();
        $prep = Ingredient::create([
            'name' => 'Maionese Temperada',
            'tipo' => Ingredient::TIPO_PREPARACAO,
            'unit_of_measure_id' => $un->id,
            'unit_cost' => 0,
            'current_stock' => 0,
            'min_stock' => 50,
            'rendimento' => 530,
        ]);

        $this->assertTrue($prep->isPreparacao());
        $this->assertEquals(530.0, (float) $prep->rendimento);
    }

    public function test_custo_producao_is_calculated_from_recipe(): void
    {
        $un = $this->createUnit();

        $maioBase = Ingredient::create(['name' => 'Maionese Base', 'tipo' => 'COMPRADO', 'unit_of_measure_id' => $un->id, 'unit_cost' => 0.05, 'current_stock' => 0, 'min_stock' => 0]);
        $alho = Ingredient::create(['name' => 'Alho', 'tipo' => 'COMPRADO', 'unit_of_measure_id' => $un->id, 'unit_cost' => 0.15, 'current_stock' => 0, 'min_stock' => 0]);
        $limao = Ingredient::create(['name' => 'Limão', 'tipo' => 'COMPRADO', 'unit_of_measure_id' => $un->id, 'unit_cost' => 0.08, 'current_stock' => 0, 'min_stock' => 0]);

        $prep = Ingredient::create(['name' => 'Maionese Temperada', 'tipo' => 'PREPARACAO', 'unit_of_measure_id' => $un->id, 'unit_cost' => 0, 'current_stock' => 0, 'min_stock' => 0, 'rendimento' => 530]);

        ReceitaIngrediente::create(['ingrediente_pai_id' => $prep->id, 'ingrediente_filho_id' => $maioBase->id, 'quantidade' => 500]);
        ReceitaIngrediente::create(['ingrediente_pai_id' => $prep->id, 'ingrediente_filho_id' => $alho->id, 'quantidade' => 20]);
        ReceitaIngrediente::create(['ingrediente_pai_id' => $prep->id, 'ingrediente_filho_id' => $limao->id, 'quantidade' => 10]);

        // custo_producao = (500 * 0.05) + (20 * 0.15) + (10 * 0.08) = 25 + 3 + 0.80 = 28.80
        $this->assertEqualsWithDelta(28.80, $prep->custo_producao, 0.01);

        // custo_unitario_producao = 28.80 / 530 = 0.0543
        $this->assertEqualsWithDelta(0.0543, $prep->custo_unitario_producao, 0.001);
    }

    public function test_custo_producao_for_comprado_returns_unit_cost(): void
    {
        $un = $this->createUnit();
        $ing = Ingredient::create(['name' => 'Bacon', 'tipo' => 'COMPRADO', 'unit_of_measure_id' => $un->id, 'unit_cost' => 3.20, 'current_stock' => 0, 'min_stock' => 0]);

        $this->assertEqualsWithDelta(3.20, $ing->custo_producao, 0.01);
        $this->assertEqualsWithDelta(3.20, $ing->custo_unitario_producao, 0.01);
    }

    public function test_receita_relationship(): void
    {
        $un = $this->createUnit();
        $prep = Ingredient::create(['name' => 'Prep', 'tipo' => 'PREPARACAO', 'unit_of_measure_id' => $un->id, 'unit_cost' => 0, 'current_stock' => 0, 'min_stock' => 0, 'rendimento' => 100]);
        $child = Ingredient::create(['name' => 'Child', 'tipo' => 'COMPRADO', 'unit_of_measure_id' => $un->id, 'unit_cost' => 1, 'current_stock' => 0, 'min_stock' => 0]);

        ReceitaIngrediente::create(['ingrediente_pai_id' => $prep->id, 'ingrediente_filho_id' => $child->id, 'quantidade' => 50]);

        $this->assertCount(1, $prep->receita);
        $this->assertEquals('Child', $prep->receita->first()->filho->name);

        $this->assertCount(1, $child->usadoEmPreparacoes);
    }

    public function test_unit_of_measure_relationship(): void
    {
        $un = UnitOfMeasure::create(['name' => 'Gramas', 'acronym' => 'g']);
        $ing = Ingredient::create(['name' => 'Test', 'unit_of_measure_id' => $un->id, 'unit_cost' => 1, 'current_stock' => 0, 'min_stock' => 0]);

        $this->assertEquals('Gramas', $ing->unitOfMeasure->name);
        $this->assertEquals('g', $ing->unitOfMeasure->acronym);
    }
}
