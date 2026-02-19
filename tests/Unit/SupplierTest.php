<?php

namespace Tests\Unit;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_supplier()
    {
        $supplier = Supplier::create([
            'name' => 'Fornecedor Teste',
            'cnpj_cpf' => '12.345.678/0001-90',
            'email' => 'teste@fornecedor.com',
            'phone' => '11999999999',
            'contact_name' => 'João',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Fornecedor Teste',
            'email' => 'teste@fornecedor.com',
        ]);

        $this->assertTrue($supplier->is_active); // Default should be true
    }

    /** @test */
    public function it_can_update_a_supplier()
    {
        $supplier = Supplier::create(['name' => 'Old Name']);

        $supplier->update(['name' => 'New Name']);

        $this->assertEquals('New Name', $supplier->fresh()->name);
    }
}
