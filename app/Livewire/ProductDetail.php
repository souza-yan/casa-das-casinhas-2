<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

#[Layout('layouts.public')]
class ProductDetail extends Component
{
    use Interactions;

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
        $this->dispatch('cart-updated');
        $this->toast()->success('Produto adicionado', "{$this->product->name} já está no seu carrinho.")->send();
    }

    public function render()
    {
        return view('livewire.product-detail');
    }
}
