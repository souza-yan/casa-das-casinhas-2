<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class Cart extends Component
{
    public function update(Product $product, int $quantity, CartService $cart): void
    {
        $cart->update($product, $quantity);
    }

    public function remove(Product $product, CartService $cart): void
    {
        $cart->remove($product);
    }

    public function render(CartService $cart)
    {
        return view('livewire.cart', ['items' => $cart->items(), 'subtotal' => $cart->subtotal()]);
    }
}
