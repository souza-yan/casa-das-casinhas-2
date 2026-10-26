<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CartService
{
    private const KEY = 'cart.items';

    public function add(Product $product, int $quantity = 1, ?string $personalization = null, array $referencePaths = []): void
    {
        abort_unless($product->is_active, 422, 'Produto indisponível.');
        $items = session(self::KEY, []);

        // Mantém o formato legado para produtos sem personalização e, assim,
        // não interrompe carrinhos iniciados antes deste recurso.
        if (! $product->is_customizable && blank($personalization) && $referencePaths === []) {
            $items[$product->id] = min(99, max(1, (int) ($items[$product->id] ?? 0) + $quantity));
        } else {
            $items[(string) Str::uuid()] = [
                'product_id' => $product->id,
                'quantity' => min(99, max(1, $quantity)),
                'personalization_description' => filled($personalization) ? trim($personalization) : null,
                'reference_paths' => array_values($referencePaths),
            ];
        }

        session()->put(self::KEY, $items);
        session()->forget('checkout.token');
    }

    public function update(string|int $lineKey, int $quantity): void
    {
        if ($quantity < 1) {
            $this->remove($lineKey);

            return;
        }
        $items = session(self::KEY, []);
        abort_unless(array_key_exists((string) $lineKey, $items), 404);
        if (is_array($items[$lineKey])) {
            $items[$lineKey]['quantity'] = min(99, $quantity);
        } else {
            $items[$lineKey] = min(99, $quantity);
        }
        session()->put(self::KEY, $items);
    }

    public function updatePersonalization(string|int $lineKey, ?string $description): void
    {
        $items = session(self::KEY, []);
        abort_unless(isset($items[$lineKey]) && is_array($items[$lineKey]), 404);
        $items[$lineKey]['personalization_description'] = filled($description) ? trim($description) : null;
        session()->put(self::KEY, $items);
        session()->forget('checkout.token');
    }

    public function remove(string|int $lineKey): void
    {
        $items = session(self::KEY, []);
        unset($items[$lineKey]);
        session()->put(self::KEY, $items);
        session()->forget('checkout.token');
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
        session()->forget('checkout.token');
    }

    public function raw(): array
    {
        return session(self::KEY, []);
    }

    public function items(): Collection
    {
        $lines = $this->lines();
        $productIds = collect($lines)->pluck('product_id')->unique()->all();

        return collect($lines)->map(function (array $line) use ($productIds) {
            return $line;
        })->pipe(function (Collection $lines) use ($productIds) {
            $products = Product::query()->whereIn('id', $productIds)->where('is_active', true)->get()->keyBy('id');

            return $lines->filter(fn (array $line) => $products->has($line['product_id']))->map(function (array $line) use ($products) {
                $product = $products->get($line['product_id']);

                return [
                    ...$line,
                    'product' => $product,
                    'subtotal' => round((float) $product->price * $line['quantity'], 2),
                ];
            })->values();
        });
    }

    /** @return array<int, array{key:string, product_id:int, quantity:int, personalization_description:?string, reference_paths:array}> */
    public function lines(?array $items = null): array
    {
        return collect($items ?? $this->raw())->map(function ($item, $key): array {
            if (is_array($item)) {
                return [
                    'key' => (string) $key,
                    'product_id' => (int) ($item['product_id'] ?? 0),
                    'quantity' => min(99, max(1, (int) ($item['quantity'] ?? 1))),
                    'personalization_description' => filled($item['personalization_description'] ?? null) ? trim((string) $item['personalization_description']) : null,
                    'reference_paths' => array_values(array_filter((array) ($item['reference_paths'] ?? []), 'is_string')),
                ];
            }

            return [
                'key' => (string) $key,
                'product_id' => (int) $key,
                'quantity' => min(99, max(1, (int) $item)),
                'personalization_description' => null,
                'reference_paths' => [],
            ];
        })->filter(fn (array $line) => $line['product_id'] > 0)->values()->all();
    }

    public function subtotal(): float
    {
        return round($this->items()->sum('subtotal'), 2);
    }
}
