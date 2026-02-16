<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceitaIngrediente extends Model
{
    protected $table = 'receita_ingredientes';

    protected $fillable = [
        'ingrediente_pai_id',
        'ingrediente_filho_id',
        'quantidade',
    ];

    protected $casts = [
        'quantidade' => 'decimal:4',
    ];

    public function pai()
    {
        return $this->belongsTo(Ingredient::class, 'ingrediente_pai_id');
    }

    public function filho()
    {
        return $this->belongsTo(Ingredient::class, 'ingrediente_filho_id');
    }
}
