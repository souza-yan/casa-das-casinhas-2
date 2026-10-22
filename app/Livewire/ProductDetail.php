<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class ProductDetail extends Component
{
    public Product $product;

    public int $quantity = 1;

    public function mount(Product $product): void
    {
        abort_unless($product->is_active, 404);
        $this->product = $product;
    }

    public function add(CartService $cart): void
    {
        $this->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);
        $cart->add($this->product, $this->quantity);
        session()->flash('status', 'Produto adicionado ao carrinho.');
    }

    public function render()
    {
        return view('livewire.product-detail');
    }
}
