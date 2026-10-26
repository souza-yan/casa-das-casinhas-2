<?php

namespace App\Livewire;

use App\Enums\Role;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use TallStackUi\Traits\Interactions;

#[Layout('layouts.public')]
class ProductDetail extends Component
{
    use Interactions, WithFileUploads;

    public Product $product;

    public int $quantity = 1;

    public string $personalization = '';

    public array $referenceImages = [];

    public function mount(Product $product): void
    {
        abort_unless($product->is_active, 404);
        $this->product = $product;
    }

    public function add(CartService $cart): void
    {
        abort_if(auth()->user()?->role === Role::ADMIN, 403);
        $validated = $this->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'personalization' => [$this->product->is_customizable ? 'required' : 'nullable', 'string', 'max:1500'],
            'referenceImages' => ['nullable', 'array', 'max:3'],
            'referenceImages.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'personalization.required' => 'Descreva como deseja personalizar esta peça.',
            'referenceImages.max' => 'Envie no máximo 3 imagens de referência.',
        ]);
        $paths = collect($validated['referenceImages'] ?? [])
            ->map(fn ($image) => $image->store('personalizations', 'local'))
            ->all();
        $cart->add($this->product, $this->quantity, $validated['personalization'] ?? null, $paths);
        $this->dispatch('cart-updated');
        $this->toast()->success('Produto adicionado', "{$this->product->name} já está no seu carrinho.")->send();
    }

    public function render()
    {
        return view('livewire.product-detail');
    }
}
