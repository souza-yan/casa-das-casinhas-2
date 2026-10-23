<?php

namespace App\Livewire\Admin\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;

#[Layout('layouts.admin')]
class Index extends Component
{
    use AuthorizesRequests, Interactions, WithPagination;

    public function transition(Order $order, string $status): void
    {
        $this->authorize('view', $order);
        $order->transitionTo(OrderStatus::from($status));
        $this->toast()->success('Status atualizado', "O pedido #{$order->id} avançou para {$order->status->label()}.")->send();
    }

    public function render()
    {
        return view('livewire.admin.orders.index', ['orders' => Order::with(['user', 'payments', 'notifications'])->latest()->paginate(15)]);
    }
}
