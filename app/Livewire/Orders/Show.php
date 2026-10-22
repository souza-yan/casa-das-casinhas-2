<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class Show extends Component
{
    use AuthorizesRequests;

    public Order $order;

    public function mount(Order $order): void
    {
        $this->authorize('view', $order);
        $this->order = $order->load(['items', 'address', 'payments', 'shipment']);
    }

    public function render()
    {
        return view('livewire.orders.show');
    }
}
