<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LivroRazaoEstoque extends Model
{
    use HasFactory;

    protected $table = 'livro_razao_estoque';

    protected $fillable = [
        'transacao_id',
        'ingredient_id',
        'local_id',
        'lote_id',
        'status_estoque',
        'qtd_anterior',
        'qtd_alteracao',
        'qtd_atual',
    ];

    public function transacao()
    {
        return $this->belongsTo(TransacaoEstoque::class, 'transacao_id');
    }

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
