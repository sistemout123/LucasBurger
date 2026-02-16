<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaldoEstoque extends Model
{
    use HasFactory;

    protected $table = 'saldos_estoque';

    protected $fillable = [
        'ingredient_id',
        'local_id',
        'lote_id',
        'status_estoque',
        'quantidade',
        'ultima_movimentacao_em',
    ];

    protected $casts = [
        'ultima_movimentacao_em' => 'datetime',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function local()
    {
        return $this->belongsTo(LocalEstoque::class, 'local_id');
    }

    public function lote()
    {
        return $this->belongsTo(LoteIngrediente::class, 'lote_id');
    }
}
