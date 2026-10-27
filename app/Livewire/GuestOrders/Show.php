<?php

namespace App\Livewire\GuestOrders;

use App\Models\Order;
use App\Services\GuestOrderAccessService;
use App\Services\Shipping\SuperFreteService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.public')]
class Show extends Component
{
    public Order $order;

    public ?string $accessToken = null;

    public ?string $trackingMessage = null;

    public function mount(Order $order, GuestOrderAccessService $access, ?string $token = null): void
    {
        abort_if($order->user_id !== null, 404);

        if ($token) {
            abort_unless($access->allowsToken($order, $token), 404);
            $access->grantSessionAccess($order);
            $this->accessToken = $token;
        }

        abort_unless($access->hasSessionAccess($order), 403);

        $this->order = $order->load(['items', 'address', 'payments', 'shipment']);
    }

    public function syncTracking(GuestOrderAccessService $access, SuperFreteService $shipping): void
    {
        abort_unless($access->hasSessionAccess($this->order), 403);

        try {
            $trackingCode = $shipping->syncTracking($this->order->shipment()->firstOrFail());
            $this->order->refresh()->load(['items', 'address', 'payments', 'shipment']);
            $this->trackingMessage = $trackingCode
                ? 'Código de rastreio atualizado.'
                : 'O código de rastreio ainda está sendo processado pela transportadora.';
        } catch (Throwable $exception) {
            report($exception);
            $this->trackingMessage = 'Não foi possível atualizar o rastreio agora. Tente novamente em instantes.';
        }
    }

    public function render()
    {
        return view('livewire.guest-orders.show');
    }
}
