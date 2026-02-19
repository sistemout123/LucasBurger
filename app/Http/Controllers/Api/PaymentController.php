<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function store(Request $request, Order $order)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string',
            'transaction_id' => 'nullable|string',
        ]);

        $payment = $this->paymentService->processPayment(
            $order,
            $validated['amount'],
            $validated['method'],
            $validated['transaction_id'] ?? null
        );

        return response()->json($payment, 201);
    }
}
