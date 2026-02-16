<?php

namespace Tests\Feature;

use App\Models\Almoxarifado;
use App\Models\Ingredient;
use App\Models\LocalEstoque;
use App\Models\Product;
use App\Models\UnitOfMeasure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_ingredients_list_page(): void
    {
        $un = UnitOfMeasure::create(['name' => 'Gramas', 'acronym' => 'g']);
        Ingredient::create(['name' => 'Bacon', 'unit_of_measure_id' => $un->id, 'unit_cost' => 3.20, 'current_stock' => 100, 'min_stock' => 10]);

        $response = $this->actingAs($this->user)->get('/admin/ingredients');
        $response->assertStatus(200);
        $response->assertSee('Bacon');
    }

    public function test_products_list_page(): void
    {
        Product::create(['name' => 'Smash Burger', 'price' => 28.90]);

        $response = $this->actingAs($this->user)->get('/admin/products');
        $response->assertStatus(200);
        $response->assertSee('Smash Burger');
    }

    public function test_recebimento_page_accessible(): void
    {
        // Create required data
        $almox = Almoxarifado::create(['codigo' => 'A01', 'nome' => 'Almox', 'esta_ativo' => true]);
        LocalEstoque::create(['almoxarifado_id' => $almox->id, 'codigo' => 'L01', 'nome' => 'Local 1', 'tipo' => 'SECO', 'esta_ativo' => true]);

        $response = $this->actingAs($this->user)->get('/admin/recebimento');
        $response->assertStatus(200);
        $response->assertSee('Receber Mercadoria');
    }
}
