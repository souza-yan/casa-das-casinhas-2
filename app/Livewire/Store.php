<?php

namespace App\Livewire;

use App\Enums\Role;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;

#[Layout('layouts.public')]
#[Title('Produtos')]
class Store extends Component
{
    use Interactions, WithPagination;

    public string $search = '';

    public string $sort = 'latest';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function add(Product $product, CartService $cart): void
    {
        abort_if(auth()->user()?->role === Role::ADMIN, 403);
        $cart->add($product);
        $this->dispatch('cart-updated');
        $this->toast()->success('Produto adicionado', "{$product->name} já está no seu carrinho.")->send();
    }

    public function render()
    {
        $query = Product::query()->where('is_active', true)->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'));
        match ($this->sort) {
            'price_asc' => $query->orderBy('price'), 'price_desc' => $query->orderByDesc('price'), default => $query->latest()
        };

        return view('livewire.store', ['products' => $query->paginate(12)]);
    }
}
