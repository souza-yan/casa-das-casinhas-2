<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use App\Services\Shipping\SuperFreteService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.public')]
class Show extends Component
{
    use AuthorizesRequests;

    public Order $order;

    public ?string $trackingMessage = null;

    public function mount(Order $order): void
    {
        $this->authorize('view', $order);
        $this->order = $order->load(['items', 'address', 'payments', 'shipment']);
    }

    public function syncTracking(SuperFreteService $shipping): void
    {
        $this->authorize('view', $this->order);

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
        return view('livewire.orders.show');
    }
}
