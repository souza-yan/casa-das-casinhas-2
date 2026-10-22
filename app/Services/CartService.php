<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    private const KEY = 'cart.items';

    public function add(Product $product, int $quantity = 1): void
    {
        abort_unless($product->is_active, 422, 'Produto indisponível.');
        $items = session(self::KEY, []);
        $items[$product->id] = min(99, max(1, ($items[$product->id] ?? 0) + $quantity));
        session()->put(self::KEY, $items);
    }

    public function update(Product $product, int $quantity): void
    {
        if ($quantity < 1) {
            $this->remove($product);

            return;
        }
        $items = session(self::KEY, []);
        $items[$product->id] = min(99, $quantity);
        session()->put(self::KEY, $items);
    }

    public function remove(Product $product): void
    {
        $items = session(self::KEY, []);
        unset($items[$product->id]);
        session()->put(self::KEY, $items);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    public function raw(): array
    {
        return session(self::KEY, []);
    }

    public function items(): Collection
    {
        $quantities = $this->raw();

        return Product::query()->whereIn('id', array_keys($quantities))->where('is_active', true)->get()
            ->map(fn (Product $product) => ['product' => $product, 'quantity' => $quantities[$product->id], 'subtotal' => round((float) $product->price * $quantities[$product->id], 2)]);
    }

    public function subtotal(): float
    {
        return round($this->items()->sum('subtotal'), 2);
    }
}
