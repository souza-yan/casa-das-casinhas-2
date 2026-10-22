<?php

namespace App\Livewire\Admin\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Shipping\MelhorEnvioService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.admin')]
class Show extends Component
{
    use AuthorizesRequests;

    public Order $order;

    public ?string $integrationMessage = null;

    public function mount(Order $order): void
    {
        $this->authorize('view', $order);
        $this->order = $order;
        $this->refreshOrder();
    }

    public function transition(string $status): void
    {
        $this->authorize('view', $this->order);
        $this->order->transitionTo(OrderStatus::from($status));
        session()->flash('status', 'Status do pedido atualizado.');
        $this->refreshOrder();
    }

    public function prepareSandboxLabel(MelhorEnvioService $shipping): void
    {
        $this->authorize('view', $this->order);

        if (! app()->isLocal()) {
            $this->integrationMessage = 'A preparação manual de etiqueta está disponível somente no ambiente local.';

            return;
        }

        try {
            $shipping->buyLabel($this->order);
            $this->integrationMessage = 'Etiqueta adicionada ao carrinho do Melhor Envio Sandbox.';
            $this->refreshOrder();
        } catch (Throwable $exception) {
            report($exception);
            $this->integrationMessage = blank(config('services.melhor_envio.token'))
                ? 'Configure o token do Melhor Envio Sandbox para preparar a etiqueta.'
                : 'Não foi possível preparar a etiqueta no Sandbox. Consulte os logs.';
        }
    }

    public function render()
    {
        return view('livewire.admin.orders.show');
    }

    private function refreshOrder(): void
    {
        $this->order->refresh()->load(['items', 'address', 'payments', 'shipment', 'notifications']);
    }
}
