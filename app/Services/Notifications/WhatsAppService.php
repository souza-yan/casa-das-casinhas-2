<?php

namespace App\Services\Notifications;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppService
{
    public function sendPaidOrder(Order $order, string $recipient): string
    {
        throw_if(blank(config('services.whatsapp.token')) || blank(config('services.whatsapp.phone_number_id')), RuntimeException::class, 'Credenciais da WhatsApp Cloud API não configuradas.');
        $items = $order->items->map(fn ($item) => "{$item->quantity}x {$item->product_name}")->join(', ');
        $productionDays = $order->items->max(fn ($item) => $item->product?->production_days ?? 0);
        $response = Http::withToken(config('services.whatsapp.token'))->acceptJson()->timeout(15)->retry(2, 250)
            ->post(rtrim(config('services.whatsapp.url'), '/').'/'.config('services.whatsapp.phone_number_id').'/messages', [
                'messaging_product' => 'whatsapp', 'to' => $recipient, 'type' => 'template',
                'template' => ['name' => config('services.whatsapp.template'), 'language' => ['code' => 'pt_BR'], 'components' => [['type' => 'body', 'parameters' => [
                    ['type' => 'text', 'text' => (string) $order->id], ['type' => 'text', 'text' => $order->customer_name],
                    ['type' => 'text', 'text' => $items], ['type' => 'text', 'text' => 'R$ '.number_format((float) $order->total, 2, ',', '.')],
                    ['type' => 'text', 'text' => 'Pagamento confirmado'], ['type' => 'text', 'text' => $productionDays.' dias'],
                ]]]],
            ])->throw()->json();

        return (string) data_get($response, 'messages.0.id');
    }
}
