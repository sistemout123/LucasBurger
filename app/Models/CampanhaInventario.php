<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CampanhaInventario extends Model
{
    use HasFactory;

    protected $table = 'campanhas_inventario';

    protected $fillable = ['titulo', 'tipo', 'status', 'encerrado_em'];

    protected $casts = [
        'encerrado_em' => 'datetime',
    ];

    public function contagens()
    {
        return $this->hasMany(ContagemInventario::class, 'campanha_id');
    }
}
