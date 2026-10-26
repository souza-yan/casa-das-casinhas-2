<?php

namespace App\Livewire\GuestOrders;

use App\Models\Order;
use App\Services\GuestOrderAccessService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class Show extends Component
{
    public Order $order;

    public function mount(Order $order, GuestOrderAccessService $access, ?string $token = null): void
    {
        abort_if($order->user_id !== null, 404);

        if ($token) {
            abort_unless($access->allowsToken($order, $token), 404);
            $access->grantSessionAccess($order);
        }

        abort_unless($access->hasSessionAccess($order), 403);

        $this->order = $order->load(['items', 'address', 'payments', 'shipment']);
    }

    public function render()
    {
        return view('livewire.guest-orders.show');
    }
}
