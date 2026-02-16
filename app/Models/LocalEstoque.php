<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LocalEstoque extends Model
{
    use HasFactory;

    protected $table = 'locais_estoque';

    protected $fillable = ['almoxarifado_id', 'codigo', 'nome', 'tipo', 'esta_ativo'];

    protected $casts = [
        'esta_ativo' => 'boolean',
    ];

    public function almoxarifado()
    {
        return $this->belongsTo(Almoxarifado::class);
    }

    public function saldos()
    {
        return $this->hasMany(SaldoEstoque::class, 'local_id');
    }
}
