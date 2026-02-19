<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use Illuminate\Support\Facades\DB;
use App\Services\InventoryService;

class PaymentService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Records a payment and checks if the order is fully paid to trigger operations.
     */
    public function processPayment(Order $order, float $amount, string $method, ?string $transactionId = null): Payment
    {
        return DB::transaction(function () use ($order, $amount, $method, $transactionId) {

            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $amount,
                'method' => $method,
                'transaction_id' => $transactionId,
            ]);

            $totalPaid = $order->payments()->sum('amount');

            // If fully paid, close order and deduct inventory
            if ($totalPaid >= $order->total_amount && $order->status !== OrderStatus::PAID->value) {
                $order->update(['status' => OrderStatus::PAID->value]);

                if ($order->table_id) {
                    Table::where('id', $order->table_id)->update(['status' => TableStatus::FREE->value]);
                }

                $this->deductInventoryForOrder($order);
            }

            return $payment;
        });
    }

    /**
     * Triggered AFTER payment.
     * Decreases the Ingredient quantity based on the Recipe.
     */
    protected function deductInventoryForOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = $item->product;
            if (!$product)
                continue;

            // Load all ingredients necessary to make this product
            foreach ($product->ingredients as $recipeIngredient) {
                $totalAmountToDeduct = $recipeIngredient->pivot->quantity * $item->quantity;

                // Record the inventory movement - using existing registrarSaida
                $this->inventoryService->registrarSaida(
                    (int) $recipeIngredient->id,
                    (int) (\App\Models\LocalEstoque::first()?->id ?? 1),
                    (float) $totalAmountToDeduct,
                    1, // system userId
                    'SAIDA_VENDA',
                    null,
                    null,
                    "Venda PDV (Pedido #{$order->id})"
                );
            }
        }
    }
}
