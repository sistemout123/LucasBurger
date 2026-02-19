<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        return response()->json(Order::with(['items.product', 'table'])->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'nullable|exists:tables,id',
            'ticket_number' => 'nullable|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        $order = $this->orderService->createOrder(
            $validated['table_id'] ?? null,
            $validated['ticket_number'] ?? null,
            $validated['items']
        );

        return response()->json($order->load('items'), 201);
    }

    public function show(Order $order)
    {
        return response()->json($order->load(['items.product', 'table', 'payments']));
    }
}
