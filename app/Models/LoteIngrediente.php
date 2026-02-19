<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoteIngrediente extends Model
{
    use HasFactory;

    protected $table = 'lotes_ingrediente';

    protected $fillable = [
        'ingredient_id',
        'numero_lote',
        'data_fabricacao',
        'data_validade',
        'supplier_id',
    ];

    protected $casts = [
        'data_fabricacao' => 'date',
        'data_validade' => 'date',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
