<?php

namespace App\Actions;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\AsaasService;
use RuntimeException;

class CreatePayment
{
    public function __construct(private AsaasService $asaas) {}

    public function execute(Order $order, ?string $successUrl = null): Payment
    {
        $existing = $order->payments()->where('provider', 'asaas')->where('status', PaymentStatus::PENDING)->latest()->first();
        if ($existing?->provider_payment_id) {
            try {
                $charge = $this->asaas->findCharge($existing->provider_payment_id);
            } catch (\Throwable $exception) {
                $this->asaas->logFailure($order, $exception);
                // Sem uma URL local não é seguro tentar criar outra cobrança após uma falha de consulta.
                if (filled($existing->payment_url)) {
                    return $existing;
                }

                throw $exception;
            }

            // Cobranças antigas criadas com uma forma fixa levam o cliente
            // diretamente ao boleto, Pix ou cartão. Substitua-as por uma
            // cobrança flexível para que a fatura apresente todas as opções
            // habilitadas na conta Asaas.
            if (filled($charge['billingType'] ?? null) && ($charge['billingType'] ?? null) !== 'UNDEFINED') {
                $this->asaas->deleteCharge($existing->provider_payment_id);
                $existing->update(['status' => PaymentStatus::FAILED]);
            } elseif (in_array($charge['status'] ?? null, ['PENDING', 'AWAITING_RISK_ANALYSIS'], true) && filled($charge['invoiceUrl'] ?? null)) {
                $existing->update(['payment_url' => $charge['invoiceUrl']]);

                return $existing->refresh();
            } elseif (in_array($charge['status'] ?? null, ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH'], true)) {
                $existing->update(['status' => PaymentStatus::CONFIRMED]);

                throw new RuntimeException('O pagamento já foi recebido e aguarda confirmação pelo webhook.');
            } else {
                $existing->update(['status' => ($charge['status'] ?? null) === 'OVERDUE' ? PaymentStatus::OVERDUE : PaymentStatus::FAILED]);
            }
        }
        $charge = $this->asaas->createCharge($order, $successUrl);

        return Payment::updateOrCreate(['provider' => 'asaas', 'provider_payment_id' => $charge['id']], [
            'order_id' => $order->id, 'method' => 'hosted', 'amount' => $order->total,
            'status' => PaymentStatus::PENDING, 'payment_url' => $charge['invoiceUrl'] ?? null,
        ]);
    }
}
