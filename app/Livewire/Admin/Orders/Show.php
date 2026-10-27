<?php

namespace App\Livewire\Admin\Orders;

use App\Enums\OrderStatus;
use App\Jobs\SyncSuperFreteTracking;
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

    public function prepareLabel(SuperFreteService $shipping): void
    {
        $this->authorize('view', $this->order);

        try {
            $shipping->buyLabel($this->order);
            $this->refreshOrder();
            SyncSuperFreteTracking::dispatch($this->order->shipment->id)->delay(now()->addMinute());
            $this->integrationMessage = 'Etiqueta emitida pelo SuperFrete.';
            $this->toast()->success('Etiqueta preparada', $this->integrationMessage)->send();
        } catch (Throwable $exception) {
            report($exception);
            $this->integrationMessage = ! $shipping->isConfiguredForLabel()
                ? 'Configure o token e o endereço de origem do SuperFrete para emitir a etiqueta.'
                : 'Não foi possível preparar a etiqueta. Consulte os logs.';
            $this->toast()->error('Falha ao preparar etiqueta', $this->integrationMessage)->send();
        }
    }

    public function syncLabelUrl(SuperFreteService $shipping): void
    {
        $this->authorize('view', $this->order);

        try {
            $labelUrl = $shipping->syncLabelUrl($this->order->shipment()->firstOrFail());
            $this->refreshOrder();
            $this->integrationMessage = $labelUrl
                ? 'Etiqueta pronta para impressão.'
                : 'A etiqueta ainda está sendo processada pela SuperFrete.';
        } catch (Throwable $exception) {
            report($exception);
            $this->integrationMessage = 'Não foi possível obter a impressão da etiqueta agora.';
        }
    }

    public function syncTracking(SuperFreteService $shipping): void
    {
        $this->authorize('view', $this->order);

        try {
            $trackingCode = $shipping->syncTracking($this->order->shipment()->firstOrFail());
            $this->refreshOrder();
            $this->integrationMessage = $trackingCode
                ? 'Código de rastreio atualizado.'
                : 'A etiqueta foi emitida, mas o código ainda está sendo processado pela SuperFrete.';
            $this->toast()->success('Rastreio consultado', $this->integrationMessage)->send();
        } catch (Throwable $exception) {
            report($exception);
            $this->integrationMessage = 'Não foi possível consultar o rastreio agora. Tente novamente em instantes.';
            $this->toast()->error('Falha ao consultar rastreio', $this->integrationMessage)->send();
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
