<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Table;
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create a new order for a table or independent ticket.
     */
    public function createOrder(?int $tableId, ?string $ticketNumber, array $items): Order
    {
        return DB::transaction(function () use ($tableId, $ticketNumber, $items) {
            $totalAmount = 0;

            $order = Order::create([
                'table_id' => $tableId,
                'ticket_number' => $ticketNumber,
                'status' => OrderStatus::OPEN->value,
                'total_amount' => 0, // Calculated later
            ]);

            if ($tableId) {
                Table::where('id', $tableId)->update(['status' => TableStatus::OCCUPIED->value]);
            }

            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $subtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            $order->update(['total_amount' => $totalAmount]);

            return $order;
        });
    }

    /**
     * Move order items to Kitchen
     */
    public function sendToKitchen(Order $order): void
    {
        $order->update(['status' => OrderStatus::PREPARING->value]);
        $order->items()->update(['status' => 'cooking']);
        // Here we could trigger a websocket event to the kitchen screen
    }
}
