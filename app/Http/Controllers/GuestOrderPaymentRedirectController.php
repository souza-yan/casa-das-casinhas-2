<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\GuestOrderAccessService;
use App\Services\Payments\AsaasPaymentRedirect;
use App\Services\Payments\AsaasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class GuestOrderPaymentRedirectController
{
    public function __invoke(Request $request, Order $order, GuestOrderAccessService $access, AsaasPaymentRedirect $paymentRedirect): RedirectResponse
    {
        $token = (string) $request->query('token');
        abort_unless($order->user_id === null && $access->allowsToken($order, $token), 404);

        try {
            return $paymentRedirect->for($order, $this->successUrl($order, $token));
        } catch (Throwable $exception) {
            Log::warning('Não foi possível iniciar pagamento de convidado no Asaas.', ['order_id' => $order->id, 'exception' => $exception::class]);

            return redirect()->route('guest.orders.show', ['order' => $order, 'token' => $token])
                ->with('payment_error', AsaasService::clientMessage($exception));
        }
    }

    private function successUrl(Order $order, string $token): ?string
    {
        $baseUrl = rtrim((string) config('services.asaas.success_url'), '/');

        return filled($baseUrl)
            ? $baseUrl.'/acompanhar-pedido/'.$order->id.'/'.$token.'?payment_returned=1'
            : null;
    }
}
