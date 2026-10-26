<?php

namespace App\Livewire;

use App\Enums\Role;
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

    public string $personalization = '';

    public function mount(Product $product): void
    {
        abort_unless($product->is_active, 404);
        $this->product = $product;
    }

    public function add(CartService $cart)
    {
        abort_if(auth()->user()?->role === Role::ADMIN, 403);
        $validated = $this->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'personalization' => ['required', 'string', 'max:1500'],
        ], [
            'personalization.required' => 'Descreva como deseja personalizar esta peça.',
        ]);
        $cart->add($this->product, $this->quantity, $validated['personalization'] ?? null);
        $this->dispatch('cart-updated');

        if (! auth()->check()) {
            $this->toast()->success('Produto adicionado', 'Preencha os dados de compra para continuar.')->flash()->send();

            return redirect()->route('checkout');
        }

        $this->toast()->success('Produto adicionado', "{$this->product->name} já está no seu carrinho.")->send();
    }

    public function render()
    {
        return view('livewire.product-detail');
    }
}
