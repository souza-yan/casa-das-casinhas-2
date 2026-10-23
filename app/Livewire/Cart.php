<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

#[Layout('layouts.public')]
class Cart extends Component
{
    use Interactions;

    public function update(Product $product, int $quantity, CartService $cart): void
    {
        $cart->update($product, $quantity);
        $this->dispatch('cart-updated');
        $this->toast()->success('Carrinho atualizado', 'A nova quantidade foi salva.')->send();
    }

    public function remove(Product $product, CartService $cart): void
    {
        $cart->remove($product);
        $this->dispatch('cart-updated');
        $this->toast()->info('Produto removido', "{$product->name} foi retirado do carrinho.")->send();
    }

    public function render(CartService $cart)
    {
        return view('livewire.cart', ['items' => $cart->items(), 'subtotal' => $cart->subtotal()]);
    }
}
