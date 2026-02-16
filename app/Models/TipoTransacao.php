<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoTransacao extends Model
{
    protected $table = 'tipos_transacao';

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id', 'descricao', 'tipo_impacto'];
}
