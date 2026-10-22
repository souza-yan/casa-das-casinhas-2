<?php

namespace App\Livewire\Orders;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.orders.index', ['orders' => auth()->user()->orders()->latest()->paginate(10)]);
    }
}
