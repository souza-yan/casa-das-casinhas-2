<?php

namespace App\Services\Payments;

use App\Actions\CreatePayment;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class AsaasPaymentRedirect
{
    public function __construct(private CreatePayment $createPayment) {}

    public function for(Order $order, ?string $successUrl = null): RedirectResponse
    {
        if ($order->status !== OrderStatus::PENDING_PAYMENT) {
            throw new RuntimeException('Este pedido não está mais aguardando pagamento.');
        }

        $payment = $this->createPayment->execute($order, $successUrl);
        $url = $payment->payment_url;

        if (! $this->isAllowedAsaasUrl($url)) {
            throw new RuntimeException('O Asaas não retornou um link de pagamento válido.');
        }

        return redirect()->away($url);
    }

    private function isAllowedAsaasUrl(?string $url): bool
    {
        $host = strtolower((string) parse_url((string) $url, PHP_URL_HOST));
        $scheme = strtolower((string) parse_url((string) $url, PHP_URL_SCHEME));

        return $scheme === 'https' && filled($host) && in_array($host, config('services.asaas.allowed_payment_hosts', []), true);
    }
}
