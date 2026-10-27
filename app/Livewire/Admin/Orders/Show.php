<?php

namespace App\Livewire\Admin\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Shipping\SuperFreteService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use TallStackUi\Traits\Interactions;
use Throwable;

#[Layout('layouts.admin')]
class Show extends Component
{
    use AuthorizesRequests, Interactions;

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
        $this->refreshOrder();
        $this->toast()->success('Status atualizado', "O pedido agora está como {$this->order->status->label()}.")->send();
    }

    public function prepareSandboxLabel(SuperFreteService $shipping): void
    {
        $this->authorize('view', $this->order);

        try {
            $shipping->buyLabel($this->order);
            $this->integrationMessage = 'Etiqueta emitida pelo SuperFrete.';
            $this->refreshOrder();
            $this->toast()->success('Etiqueta preparada', $this->integrationMessage)->send();
        } catch (Throwable $exception) {
            report($exception);
            $this->integrationMessage = ! $shipping->isConfiguredForLabel()
                ? 'Configure o token e o endereço de origem do SuperFrete para emitir a etiqueta.'
                : 'Não foi possível preparar a etiqueta no Sandbox. Consulte os logs.';
            $this->toast()->error('Falha ao preparar etiqueta', $this->integrationMessage)->send();
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
