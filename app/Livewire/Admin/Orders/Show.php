<?php

namespace App\Livewire\Admin\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Shipping\MelhorEnvioService;
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

    public function prepareSandboxLabel(MelhorEnvioService $shipping): void
    {
        $this->authorize('view', $this->order);

        if (! app()->isLocal()) {
            $this->integrationMessage = 'A preparação manual de etiqueta está disponível somente no ambiente local.';
            $this->toast()->warning('Ação indisponível', $this->integrationMessage)->send();

            return;
        }

        try {
            $shipping->buyLabel($this->order);
            $this->integrationMessage = 'Etiqueta adicionada ao carrinho do Melhor Envio Sandbox.';
            $this->refreshOrder();
            $this->toast()->success('Etiqueta preparada', $this->integrationMessage)->send();
        } catch (Throwable $exception) {
            report($exception);
            $this->integrationMessage = blank(config('services.melhor_envio.token'))
                ? 'Configure o token do Melhor Envio Sandbox para preparar a etiqueta.'
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
