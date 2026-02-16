<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\LivroRazaoEstoque;
use App\Models\Product;
use App\Models\SaldoEstoque;
use App\Models\TransacaoEstoque;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Registrar uma entrada de ingrediente no estoque.
     */
    public function registrarEntrada(
        int $ingredientId,
        int $localId,
        float $quantidade,
        int $userId,
        ?string $docReferencia = null,
        ?int $loteId = null,
        ?string $notas = null
    ): TransacaoEstoque {
        return DB::transaction(function () use ($ingredientId, $localId, $quantidade, $userId, $docReferencia, $loteId, $notas) {
            $transacao = TransacaoEstoque::create([
                'tipo_id' => 'ENTRADA_COMPRA',
                'doc_referencia' => $docReferencia,
                'solicitado_por' => $userId,
                'notas' => $notas,
            ]);

            $this->criarLinhaRazao($transacao->id, $ingredientId, $localId, $quantidade, $loteId);

            return $transacao;
        });
    }

    /**
     * Registrar uma saída manual de ingrediente do estoque.
     */
    public function registrarSaida(
        int $ingredientId,
        int $localId,
        float $quantidade,
        int $userId,
        string $tipoId = 'SAIDA_PRODUCAO',
        ?string $docReferencia = null,
        ?int $loteId = null,
        ?string $notas = null
    ): TransacaoEstoque {
        return DB::transaction(function () use ($ingredientId, $localId, $quantidade, $userId, $tipoId, $docReferencia, $loteId, $notas) {
            $transacao = TransacaoEstoque::create([
                'tipo_id' => $tipoId,
                'doc_referencia' => $docReferencia,
                'solicitado_por' => $userId,
                'notas' => $notas,
            ]);

            $this->criarLinhaRazao($transacao->id, $ingredientId, $localId, -abs($quantidade), $loteId);

            return $transacao;
        });
    }

    /**
     * Registrar a venda de um produto, consumindo ingredientes conforme a ficha técnica.
     */
    public function registrarVenda(
        Product $produto,
        int $quantidade,
        int $localId,
        int $userId,
        ?string $docReferencia = null,
        ?string $notas = null
    ): TransacaoEstoque {
        return DB::transaction(function () use ($produto, $quantidade, $localId, $userId, $docReferencia, $notas) {
            $transacao = TransacaoEstoque::create([
                'tipo_id' => 'SAIDA_VENDA',
                'product_id' => $produto->id,
                'quantidade_produtos' => $quantidade,
                'doc_referencia' => $docReferencia,
                'solicitado_por' => $userId,
                'notas' => $notas ?? "Venda de {$quantidade}x {$produto->name}",
            ]);

            // Busca a ficha técnica do produto
            $fichaIngredientes = $produto->productIngredients()->with('ingredient')->get();

            foreach ($fichaIngredientes as $ficha) {
                // quantidade_receita × quantidade_vendida
                $qtdConsumo = $ficha->quantity * $quantidade;

                $this->criarLinhaRazao(
                    $transacao->id,
                    $ficha->ingredient_id,
                    $localId,
                    -abs($qtdConsumo)
                );
            }

            return $transacao;
        });
    }

    /**
     * Transferir ingrediente de um local para outro.
     */
    public function transferir(
        int $ingredientId,
        int $localOrigemId,
        int $localDestinoId,
        float $quantidade,
        int $userId,
        ?int $loteId = null,
        ?string $notas = null
    ): TransacaoEstoque {
        return DB::transaction(function () use ($ingredientId, $localOrigemId, $localDestinoId, $quantidade, $userId, $loteId, $notas) {
            $transacao = TransacaoEstoque::create([
                'tipo_id' => 'TRANSFERENCIA',
                'solicitado_por' => $userId,
                'notas' => $notas,
            ]);

            // Saída do local de origem
            $this->criarLinhaRazao($transacao->id, $ingredientId, $localOrigemId, -abs($quantidade), $loteId);

            // Entrada no local de destino
            $this->criarLinhaRazao($transacao->id, $ingredientId, $localDestinoId, abs($quantidade), $loteId);

            return $transacao;
        });
    }

    /**
     * Ajustar estoque após inventário/auditoria.
     */
    public function ajustarInventario(
        int $ingredientId,
        int $localId,
        float $quantidadeAjuste,
        int $userId,
        ?int $loteId = null,
        ?string $notas = null
    ): TransacaoEstoque {
        return DB::transaction(function () use ($ingredientId, $localId, $quantidadeAjuste, $userId, $loteId, $notas) {
            $transacao = TransacaoEstoque::create([
                'tipo_id' => 'AJUSTE_INVENTARIO',
                'solicitado_por' => $userId,
                'notas' => $notas ?? 'Ajuste de inventário',
            ]);

            $this->criarLinhaRazao($transacao->id, $ingredientId, $localId, $quantidadeAjuste, $loteId);

            return $transacao;
        });
    }

    /**
     * Consultar saldo consolidado de um ingrediente (em todos os locais).
     */
    public function calcularSaldo(int $ingredientId, ?int $localId = null): float
    {
        $query = SaldoEstoque::where('ingredient_id', $ingredientId)
            ->where('status_estoque', 'DISPONIVEL');

        if ($localId) {
            $query->where('local_id', $localId);
        }

        return (float) $query->sum('quantidade');
    }

    /**
     * Criar uma linha no livro razão e atualizar o saldo.
     */
    private function criarLinhaRazao(
        int $transacaoId,
        int $ingredientId,
        int $localId,
        float $qtdAlteracao,
        ?int $loteId = null,
        string $statusEstoque = 'DISPONIVEL'
    ): LivroRazaoEstoque {
        // Buscar saldo atual
        $saldo = SaldoEstoque::firstOrCreate(
            [
                'ingredient_id' => $ingredientId,
                'local_id' => $localId,
                'lote_id' => $loteId,
                'status_estoque' => $statusEstoque,
            ],
            [
                'quantidade' => 0,
                'ultima_movimentacao_em' => now(),
            ]
        );

        $qtdAnterior = $saldo->quantidade;
        $qtdAtual = $qtdAnterior + $qtdAlteracao;

        // Criar linha no razão
        $linha = LivroRazaoEstoque::create([
            'transacao_id' => $transacaoId,
            'ingredient_id' => $ingredientId,
            'local_id' => $localId,
            'lote_id' => $loteId,
            'status_estoque' => $statusEstoque,
            'qtd_anterior' => $qtdAnterior,
            'qtd_alteracao' => $qtdAlteracao,
            'qtd_atual' => $qtdAtual,
        ]);

        // Atualizar saldo
        $saldo->update([
            'quantidade' => $qtdAtual,
            'ultima_movimentacao_em' => now(),
        ]);

        return $linha;
    }

    /**
     * Produzir uma preparação (semi-acabado), consumindo ingredientes base
     * conforme a receita e gerando entrada do item produzido.
     *
     * @param int    $ingredientId  ID da preparação a produzir
     * @param int    $localId       Local de estoque (origem dos insumos e destino da preparação)
     * @param float  $quantidade    Quantidade de unidades a produzir
     * @param int    $userId        Usuário solicitante
     * @param string|null $notas    Observações
     */
    public function produzirPreparacao(
        int $ingredientId,
        int $localId,
        float $quantidade,
        int $userId,
        ?string $notas = null
    ): TransacaoEstoque {
        return DB::transaction(function () use ($ingredientId, $localId, $quantidade, $userId, $notas) {
            $preparacao = Ingredient::with('receita.filho')->findOrFail($ingredientId);

            if (!$preparacao->isPreparacao()) {
                throw new \InvalidArgumentException("Ingrediente [{$preparacao->name}] não é uma preparação.");
            }

            if ($preparacao->receita->isEmpty()) {
                throw new \InvalidArgumentException("A preparação [{$preparacao->name}] não possui receita cadastrada.");
            }

            $rendimento = $preparacao->rendimento ?: 1;
            $multiplicador = $quantidade / $rendimento;

            $transacao = TransacaoEstoque::create([
                'tipo_id' => 'SAIDA_PRODUCAO',
                'solicitado_por' => $userId,
                'notas' => $notas ?? "Produção de {$quantidade}x {$preparacao->name}",
            ]);

            // Consumir cada ingrediente base
            foreach ($preparacao->receita as $item) {
                $qtdConsumo = $item->quantidade * $multiplicador;
                $this->criarLinhaRazao(
                    $transacao->id,
                    $item->ingrediente_filho_id,
                    $localId,
                    -abs($qtdConsumo)
                );
            }

            // Registrar entrada da preparação produzida
            $transacaoEntrada = TransacaoEstoque::create([
                'tipo_id' => 'ENTRADA_PRODUCAO',
                'solicitado_por' => $userId,
                'notas' => "Entrada via produção: {$quantidade}x {$preparacao->name}",
            ]);

            $this->criarLinhaRazao(
                $transacaoEntrada->id,
                $ingredientId,
                $localId,
                abs($quantidade)
            );

            // Atualizar custo unitário com base na receita
            $preparacao->update([
                'unit_cost' => $preparacao->custo_unitario_producao,
            ]);

            return $transacao;
        });
    }
}
