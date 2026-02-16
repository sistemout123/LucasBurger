<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransacaoEstoque extends Model
{
    use HasFactory;

    protected $table = 'transacoes_estoque';

    protected $fillable = [
        'tipo_id',
        'product_id',
        'quantidade_produtos',
        'doc_referencia',
        'solicitado_por',
        'autorizado_por',
        'notas',
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoTransacao::class, 'tipo_id');
    }

    public function produto()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(\App\Models\User::class, 'solicitado_por');
    }

    public function autorizador()
    {
        return $this->belongsTo(\App\Models\User::class, 'autorizado_por');
    }

    public function linhasRazao()
    {
        return $this->hasMany(LivroRazaoEstoque::class, 'transacao_id');
    }
}
