<?php

namespace App\Actions;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\AsaasService;

class CreatePayment
{
    public function __construct(private AsaasService $asaas) {}

    public function execute(Order $order): Payment
    {
        $existing = $order->payments()->where('provider', 'asaas')->where('status', PaymentStatus::PENDING)->latest()->first();
        if ($existing?->provider_payment_id) {
            return $existing;
        }
        $charge = $this->asaas->createCharge($order);

        return Payment::updateOrCreate(['provider' => 'asaas', 'provider_payment_id' => $charge['id']], [
            'order_id' => $order->id, 'method' => 'hosted', 'amount' => $order->total,
            'status' => PaymentStatus::PENDING, 'payment_url' => $charge['invoiceUrl'] ?? null,
        ]);
    }
}
