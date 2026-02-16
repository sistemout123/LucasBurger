<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Almoxarifado extends Model
{
    use HasFactory;

    protected $table = 'almoxarifados';

    protected $fillable = ['codigo', 'nome', 'endereco', 'esta_ativo'];

    protected $casts = [
        'esta_ativo' => 'boolean',
    ];

    public function locais()
    {
        return $this->hasMany(LocalEstoque::class, 'almoxarifado_id');
    }
}
