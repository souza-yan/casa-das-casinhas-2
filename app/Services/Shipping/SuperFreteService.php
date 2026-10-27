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

class SuperFreteService
{
    public function isConfiguredForQuote(): bool
    {
        return filled(config('services.superfrete.token'))
            && filled(data_get(config('services.superfrete.origin'), 'postal_code'));
    }

    public function isConfiguredForLabel(): bool
    {
        return $this->isConfiguredForQuote()
            && collect(config('services.superfrete.origin'))
                ->only(['name', 'postal_code', 'address', 'number', 'district', 'city', 'state_abbr'])
                ->every(fn ($value) => filled($value));
    }

    public function quote(string $destinationZipCode, Collection $cartItems): array
    {
        $response = $this->client()->post('/api/v0/calculator', [
            'from' => ['postal_code' => $this->postalCode(data_get(config('services.superfrete.origin'), 'postal_code'))],
            'to' => ['postal_code' => $this->postalCode($destinationZipCode)],
            'products' => $this->quoteProducts($cartItems),
            'services' => '1,2,17,3,31',
            'options' => ['insurance_value' => 0, 'use_insurance_value' => false, 'receipt' => false, 'own_hand' => false],
        ])->throw()->json();

        $options = data_get($response, 'services', data_get($response, 'data', $response));
        $calculatedVolume = data_get($response, 'package', data_get($response, 'volumes.0'));

        return collect($options)->reject(fn (array $option) => (bool) data_get($option, 'error'))->map(fn (array $option) => [
            'id' => (string) data_get($option, 'id'),
            'name' => data_get($option, 'name', 'Entrega'),
            'carrier' => data_get($option, 'company.name', data_get($option, 'carrier', 'Transportadora')),
            'price' => round((float) data_get($option, 'price', 0), 2),
            'delivery_days' => (int) data_get($option, 'delivery_time', data_get($option, 'delivery_days', 0)),
            'volume' => data_get($option, 'package', data_get($option, 'volumes.0', data_get($option, 'packages.0', $calculatedVolume))),
        ])->filter(fn (array $option) => filled($option['id']))->values()->all();
    }

    public function buyLabel(Order $order): array
    {
        throw_unless($order->status === OrderStatus::READY_TO_SHIP, RuntimeException::class, 'O pedido ainda não está pronto para envio.');
        throw_unless($this->isConfiguredForLabel(), RuntimeException::class, 'Os dados de origem do SuperFrete não estão completos.');

        $order->loadMissing(['address', 'items.product', 'shipment']);
        $shipment = $order->shipment ?? throw new RuntimeException('O pedido não possui um frete selecionado.');
        throw_if(blank($order->address?->document), RuntimeException::class, 'O pedido não possui CPF ou CNPJ do destinatário.');

        if ($shipment->provider_shipment_id) {
            throw new RuntimeException('A etiqueta deste pedido já foi emitida.');
        }

        $cart = $this->client()->post('/api/v0/cart', [
            'service' => (int) $shipment->service_id,
            'from' => $this->origin(),
            'to' => $this->recipient($order),
            'products' => $this->products($order),
            'volumes' => $this->selectedVolume($shipment->quote_payload, $order->items),
            'options' => [
                'insurance_value' => round((float) $order->subtotal, 2),
                'receipt' => false,
                'own_hand' => false,
                'non_commercial' => filter_var(config('services.superfrete.non_commercial'), FILTER_VALIDATE_BOOL),
                'tags' => [],
            ],
            'platform' => 'Casa das Casinhas',
        ])->throw()->json();

        $providerShipmentId = (string) (data_get($cart, 'id') ?? data_get($cart, '0.id'));
        throw_if(blank($providerShipmentId), RuntimeException::class, 'O SuperFrete não retornou o identificador da etiqueta.');

        $checkout = $this->client()->post('/api/v0/checkout', ['orders' => [$providerShipmentId]])->throw()->json();
        $shipment->update([
            'provider_shipment_id' => $providerShipmentId,
            'status' => ShipmentStatus::LABEL_PURCHASED,
        ]);

        try {
            $label = $this->client()->get("/api/v0/order/info/{$providerShipmentId}")->throw()->json();
            $shipment->update(['tracking_code' => data_get($label, 'tracking')]);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $checkout;
    }

    private function origin(): array
    {
        $origin = config('services.superfrete.origin');

        return array_filter([
            'name' => data_get($origin, 'name'),
            'address' => data_get($origin, 'address'),
            'number' => data_get($origin, 'number'),
            'complement' => data_get($origin, 'complement'),
            'district' => data_get($origin, 'district'),
            'city' => data_get($origin, 'city'),
            'state_abbr' => strtoupper((string) data_get($origin, 'state_abbr')),
            'postal_code' => $this->postalCode(data_get($origin, 'postal_code')),
            'document' => preg_replace('/\D/', '', (string) data_get($origin, 'document')),
        ], fn ($value) => filled($value));
    }

    private function recipient(Order $order): array
    {
        throw_if(! $order->address, RuntimeException::class, 'O pedido não possui endereço de entrega.');

        return array_filter([
            'name' => $order->customer_name,
            'address' => $order->address->street,
            'number' => $order->address->number,
            'complement' => $order->address->complement,
            'district' => $order->address->district,
            'city' => $order->address->city,
            'state_abbr' => strtoupper($order->address->state),
            'postal_code' => $this->postalCode($order->address->zip_code),
            'email' => $order->customer_email,
            'phone' => preg_replace('/\D/', '', $order->customer_phone),
            'document' => preg_replace('/\D/', '', (string) $order->address->document),
        ], fn ($value) => filled($value));
    }

    private function products(Order $order): array
    {
        return $order->items->map(fn ($item) => [
            'name' => $item->product_name,
            'quantity' => (string) $item->quantity,
            'unitary_value' => number_format((float) $item->unit_price, 2, '.', ''),
        ])->all();
    }

    private function quoteProducts(Collection $items): array
    {
        throw_if($items->isEmpty(), RuntimeException::class, 'Não há produtos para cotar.');

        return $items->map(fn ($item) => [
            'height' => (float) $this->product($item)->height_cm,
            'width' => (float) $this->product($item)->width_cm,
            'length' => (float) $this->product($item)->length_cm,
            'weight' => (float) $this->product($item)->weight_kg,
            'quantity' => $this->quantity($item),
        ])->values()->all();
    }

    private function selectedVolume(?array $quotePayload, Collection $items): array
    {
        $volume = data_get($quotePayload, 'volume');

        if (is_array($volume) && collect(['height', 'width', 'length', 'weight'])->every(fn (string $key) => filled(data_get($volume, $key)))) {
            return collect($volume)->only(['height', 'width', 'length', 'weight'])->map(fn ($value) => (float) $value)->all();
        }

        return collect($this->package($items))->only(['height', 'width', 'length', 'weight'])->all();
    }

    private function package(Collection $items): array
    {
        throw_if($items->isEmpty(), RuntimeException::class, 'Não há produtos para cotar.');

        return [
            'height' => (float) $items->max(fn ($item) => $this->product($item)->height_cm),
            'width' => (float) $items->max(fn ($item) => $this->product($item)->width_cm),
            'length' => (float) $items->max(fn ($item) => $this->product($item)->length_cm),
            'weight' => round($items->sum(fn ($item) => (float) $this->product($item)->weight_kg * $this->quantity($item)), 3),
            'quantity' => 1,
        ];
    }

    private function product(mixed $item): Product
    {
        $product = $item instanceof Product ? $item : data_get($item, 'product');
        throw_unless($product instanceof Product, RuntimeException::class, 'Um produto do pedido não está mais disponível para calcular o frete.');

        return $product;
    }

    private function quantity(mixed $item): int
    {
        return $item instanceof Product ? 1 : max(1, (int) data_get($item, 'quantity', 1));
    }

    private function postalCode(?string $postalCode): string
    {
        return preg_replace('/\D/', '', (string) $postalCode);
    }

    private function client(): PendingRequest
    {
        throw_unless(filled(config('services.superfrete.token')), RuntimeException::class, 'Token do SuperFrete não configurado.');

        return Http::baseUrl(rtrim(config('services.superfrete.url'), '/'))
            ->withToken(config('services.superfrete.token'))
            ->acceptJson()
            ->withUserAgent(config('services.superfrete.user_agent'))
            ->timeout(20);
    }
}
