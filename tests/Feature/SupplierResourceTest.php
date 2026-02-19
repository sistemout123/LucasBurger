<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Filament\Resources\Suppliers\Pages\CreateSupplier;

class SupplierResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user to bypass authentication middleware
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function it_can_render_supplier_list_page()
    {
        $this->get(ListSuppliers::getUrl())->assertSuccessful();
    }

    /** @test */
    public function it_can_render_create_supplier_page()
    {
        $this->get(CreateSupplier::getUrl())->assertSuccessful();
    }

    /** @test */
    public function it_can_create_a_supplier()
    {
        Livewire::test(CreateSupplier::class)
            ->fillForm([
                'name' => 'Novo Fornecedor',
                'cnpj_cpf' => '00.000.000/0001-00',
                'phone' => '11988887777',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Novo Fornecedor',
            'cnpj_cpf' => '00.000.000/0001-00',
        ]);
    }

    /** @test */
    public function it_validates_required_name()
    {
        Livewire::test(CreateSupplier::class)
            ->fillForm([
                'name' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }
}
