<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Payments\AsaasPaymentRedirect;
use App\Services\Payments\AsaasService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderPaymentRedirectController
{
    use AuthorizesRequests;

    public function __invoke(Order $order, AsaasPaymentRedirect $paymentRedirect): RedirectResponse
    {
        $this->authorize('view', $order);

        try {
            return $paymentRedirect->for($order, $this->successUrl($order));
        } catch (Throwable $exception) {
            Log::warning('Não foi possível iniciar pagamento no Asaas.', ['order_id' => $order->id, 'exception' => $exception::class]);

            return redirect()->route('orders.show', $order)
                ->with('payment_error', AsaasService::clientMessage($exception));
        }
    }

    private function successUrl(Order $order): ?string
    {
        $baseUrl = rtrim((string) config('services.asaas.success_url'), '/');

        return filled($baseUrl) ? $baseUrl.'/pedidos/'.$order->id.'?payment_returned=1' : null;
    }
}
