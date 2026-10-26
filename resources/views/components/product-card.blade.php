@props(['product'])

<article class="min-w-0">
    <a href="{{ route('products.show', $product) }}" class="relative block">
        <x-product-image :product="$product" variant="card" />
    </a>
    <div class="pt-4">
        <a href="{{ route('products.show', $product) }}" class="font-serif text-xl leading-none">{{ $product->name }}</a>
        <p class="mt-2 text-sm text-ink/60">{{ $product->isStockProduct() ? ($product->is_available ? 'Em estoque' : 'Esgotado') : 'Sob demanda' }}</p>
        <strong class="mt-2 block text-base">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</strong>
        <x-action-button :href="route('products.show', $product)" class="mt-5 w-full" aria-label="Ver {{ $product->name }}">
            Ver produto
        </x-action-button>
    </div>
</article>
