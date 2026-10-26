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

    public array $personalizations = [];

    public ?string $editingPersonalizationKey = null;

    public bool $personalizationModal = false;

    public function mount(CartService $cart): void
    {
        foreach ($cart->items() as $item) {
            if ($item['product']->is_customizable) {
                $this->personalizations[$item['key']] = $item['personalization_description'] ?? '';
            }
        }
    }

    public function update(string $lineKey, int $quantity, CartService $cart): void
    {
        $cart->update($lineKey, $quantity);
        $this->dispatch('cart-updated');
        $this->toast()->success('Carrinho atualizado', 'A nova quantidade foi salva.')->send();
    }

    public function remove(string $lineKey, CartService $cart): void
    {
        $item = collect($cart->items())->firstWhere('key', $lineKey);
        $cart->remove($lineKey);
        $this->dispatch('cart-updated');
        $this->toast()->info('Produto removido', "{$item['product']->name} foi retirado do carrinho.")->send();
    }

    public function editPersonalization(string $lineKey, CartService $cart): void
    {
        $item = collect($cart->items())->firstWhere('key', $lineKey);
        abort_unless($item && $item['product']->is_customizable, 404);

        $this->editingPersonalizationKey = $lineKey;
        $this->personalizations[$lineKey] = $item['personalization_description'] ?? '';
        $this->resetValidation();
        $this->personalizationModal = true;
    }

    public function savePersonalization(CartService $cart): void
    {
        $lineKey = $this->editingPersonalizationKey;
        $item = collect($cart->items())->firstWhere('key', $lineKey);
        abort_unless($item && $item['product']->is_customizable, 404);
        $this->validate(['personalizations.'.$lineKey => ['required', 'string', 'max:1500']], [
            'personalizations.'.$lineKey.'.required' => 'Descreva a personalização desejada.',
        ]);
        $cart->updatePersonalization($lineKey, $this->personalizations[$lineKey]);
        $this->personalizationModal = false;
        $this->toast()->success('Personalização atualizada', 'As instruções desta peça foram salvas.')->send();
    }

    public function render(CartService $cart)
    {
        return view('livewire.cart', ['items' => $cart->items(), 'subtotal' => $cart->subtotal()]);
    }
}
