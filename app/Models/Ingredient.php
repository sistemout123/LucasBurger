<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tipo',
        'unit_of_measure_id',
        'unit_cost',
        'current_stock',
        'min_stock',
        'estoque_maximo',
        'controle_lote',
        'rendimento',
    ];

    protected $casts = [
        'controle_lote' => 'boolean',
        'rendimento' => 'decimal:4',
        'unit_cost' => 'decimal:2',
    ];

    // ── Tipos ──
    const TIPO_COMPRADO = 'COMPRADO';
    const TIPO_PREPARACAO = 'PREPARACAO';

    public function isPreparacao(): bool
    {
        return $this->tipo === self::TIPO_PREPARACAO;
    }

    // ── Relacionamentos ──
    public function unitOfMeasure()
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_ingredients')
            ->withPivot(['quantity', 'correction_factor', 'cooking_factor'])
            ->withTimestamps();
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function lotes()
    {
        return $this->hasMany(LoteIngrediente::class);
    }

    public function saldos()
    {
        return $this->hasMany(SaldoEstoque::class);
    }

    public function livroRazao()
    {
        return $this->hasMany(LivroRazaoEstoque::class);
    }

    // ── Receita (para PREPARACAO) ──
    public function receita()
    {
        return $this->hasMany(ReceitaIngrediente::class, 'ingrediente_pai_id');
    }

    public function usadoEmPreparacoes()
    {
        return $this->hasMany(ReceitaIngrediente::class, 'ingrediente_filho_id');
    }

    // ── Custo de Produção (para PREPARACAO) ──
    public function getCustoProducaoAttribute(): float
    {
        if (!$this->isPreparacao()) {
            return (float) $this->unit_cost;
        }

        $custoTotal = 0;
        foreach ($this->receita()->with('filho')->get() as $item) {
            $custoTotal += ($item->filho->unit_cost ?? 0) * $item->quantidade;
        }

        return $custoTotal;
    }

    public function getCustoUnitarioProducaoAttribute(): float
    {
        if (!$this->isPreparacao() || !$this->rendimento || $this->rendimento == 0) {
            return (float) $this->unit_cost;
        }

        return $this->custo_producao / $this->rendimento;
    }
}
