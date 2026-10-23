@props(['product', 'interactive' => true])

<article class="group min-w-0">
    <a href="{{ route('products.show', $product) }}" class="relative block aspect-[4/5] overflow-hidden bg-cream">
        @if ($product->image_path)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="size-full object-cover transition duration-700 ease-out group-hover:scale-[1.035]">
        @else
            <div class="grid size-full place-items-center font-serif text-2xl text-bark">Feito à mão</div>
        @endif
        <span class="absolute left-3 top-3 bg-paper px-3 py-2 text-sm font-semibold uppercase tracking-[.16em]">Sob encomenda</span>
    </a>
    <div class="pt-4">
        <a href="{{ route('products.show', $product) }}" class="font-serif text-xl leading-none transition group-hover:text-clay">{{ $product->name }}</a>
        <p class="mt-1.5 text-sm text-ink/50">Painel artesanal em madeira MDF</p>
        <div class="mt-3 flex items-center justify-between gap-3">
            <strong class="text-base">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</strong>
            @if ($interactive)
                <button wire:click="add({{ $product->id }})" wire:loading.attr="disabled" class="grid size-9 place-items-center border border-ink bg-ink text-paper transition hover:bg-paper hover:text-ink disabled:opacity-50" aria-label="Adicionar {{ $product->name }} ao carrinho">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20.5 7H6"/><path d="M12 9v5M9.5 11.5h5"/></svg>
                </button>
            @else
                <span class="text-sm font-semibold uppercase tracking-[.12em] text-bark">Ver peça →</span>
            @endif
        </div>
    </div>
</article>
