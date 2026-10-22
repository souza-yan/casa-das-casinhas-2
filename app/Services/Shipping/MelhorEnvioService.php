<?php

namespace App\Services\Shipping;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MelhorEnvioService
{
    public function quote(string $destinationZipCode, Collection $cartItems): array
    {
        $package = $this->package($cartItems);
        $response = $this->client()->post('/api/v2/me/shipment/calculate', [
            'from' => ['postal_code' => preg_replace('/\D/', '', config('services.melhor_envio.origin_zip_code'))],
            'to' => ['postal_code' => preg_replace('/\D/', '', $destinationZipCode)], 'package' => $package,
        ])->throw()->json();

        return collect($response)->reject(fn ($option) => isset($option['error']))->map(fn ($option) => [
            'id' => (string) $option['id'], 'name' => $option['name'], 'carrier' => data_get($option, 'company.name', 'Transportadora'),
            'price' => round((float) $option['price'], 2), 'delivery_days' => (int) ($option['delivery_time'] ?? 0),
        ])->values()->all();
    }

    public function buyLabel(Order $order): array
    {
        throw_unless($order->status === OrderStatus::READY_TO_SHIP, RuntimeException::class, 'O pedido ainda não está pronto para envio.');
        $shipment = $order->shipment()->firstOrFail();
        $result = $this->client()->post('/api/v2/me/cart', ['orders' => [['service' => (int) $shipment->service_id, 'from' => ['postal_code' => config('services.melhor_envio.origin_zip_code')], 'to' => ['postal_code' => $order->address->zip_code]]]])->throw()->json();
        $shipment->update(['provider_shipment_id' => data_get($result, '0.id'), 'status' => ShipmentStatus::LABEL_PURCHASED]);

        return $result;
    }

    private function package(Collection $items): array
    {
        throw_if($items->isEmpty(), RuntimeException::class, 'Não há produtos para cotar.');

        return [
            'height' => (float) $items->max(fn ($item) => $this->product($item)->height_cm),
            'width' => (float) $items->max(fn ($item) => $this->product($item)->width_cm),
            'length' => (float) $items->max(fn ($item) => $this->product($item)->length_cm),
            'weight' => round($items->sum(fn ($item) => (float) $this->product($item)->weight_kg * $this->quantity($item)), 3),
        ];
    }

    private function product($item): Product
    {
        return $item instanceof Product ? $item : $item['product'];
    }

    private function quantity($item): int
    {
        return $item instanceof Product ? 1 : max(1, (int) $item['quantity']);
    }

    private function client(): PendingRequest
    {
        throw_if(blank(config('services.melhor_envio.token')), RuntimeException::class, 'Token do Melhor Envio Sandbox não configurado.');

        return Http::baseUrl(rtrim(config('services.melhor_envio.url'), '/'))->withToken(config('services.melhor_envio.token'))->acceptJson()->withUserAgent(config('services.melhor_envio.user_agent'))->timeout(20)->retry(2, 250);
    }
}
