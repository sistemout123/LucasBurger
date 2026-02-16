<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContagemInventario extends Model
{
    use HasFactory;

    protected $table = 'contagens_inventario';

    protected $fillable = [
        'campanha_id',
        'ingredient_id',
        'local_id',
        'lote_id',
        'qtd_esperada',
        'qtd_contada',
        'discrepancia',
        'auditor_id',
        'contado_em',
        'foi_conciliado',
    ];

    protected $casts = [
        'contado_em' => 'datetime',
        'foi_conciliado' => 'boolean',
    ];

    public function campanha()
    {
        return $this->belongsTo(CampanhaInventario::class, 'campanha_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function local()
    {
        return $this->belongsTo(LocalEstoque::class, 'local_id');
    }

    public function auditor()
    {
        return $this->belongsTo(\App\Models\User::class, 'auditor_id');
    }
}
