<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposTransacaoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['id' => 'ENTRADA_COMPRA', 'descricao' => 'Entrada por compra de ingredientes', 'tipo_impacto' => '+'],
            ['id' => 'ENTRADA_PRODUCAO', 'descricao' => 'Entrada por produção de preparação interna', 'tipo_impacto' => '+'],
            ['id' => 'SAIDA_PRODUCAO', 'descricao' => 'Saída para produção (uso na cozinha)', 'tipo_impacto' => '-'],
            ['id' => 'SAIDA_VENDA', 'descricao' => 'Baixa automática pela venda de um produto (ficha técnica)', 'tipo_impacto' => '-'],
            ['id' => 'SAIDA_PERDA', 'descricao' => 'Perda, desperdício ou ingrediente vencido', 'tipo_impacto' => '-'],
            ['id' => 'AJUSTE_INVENTARIO', 'descricao' => 'Ajuste proveniente de inventário/auditoria', 'tipo_impacto' => 'N'],
            ['id' => 'TRANSFERENCIA', 'descricao' => 'Transferência entre locais de estoque', 'tipo_impacto' => 'N'],
            ['id' => 'DEVOLUCAO_FORNECEDOR', 'descricao' => 'Devolução de ingrediente ao fornecedor', 'tipo_impacto' => '-'],
        ];

        foreach ($tipos as $tipo) {
            DB::table('tipos_transacao')->updateOrInsert(['id' => $tipo['id']], $tipo);
        }
    }
}
