@props(['product', 'interactive' => true])

<article class="group min-w-0">
    <a href="{{ route('products.show', $product) }}" class="relative block aspect-[4/3] overflow-hidden rounded-[3px] bg-sand">
        @if ($product->image_path)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="size-full object-cover transition duration-700 ease-out group-hover:scale-[1.025]">
        @else
            <div class="grid size-full place-items-center font-serif text-2xl text-bark">Feito à mão</div>
        @endif
        <span class="absolute left-3 top-3 rounded-full bg-paper/95 px-3 py-1.5 text-[9px] font-semibold uppercase tracking-[.14em] shadow-sm">Sob encomenda</span>
    </a>
    <div class="pt-4">
        <a href="{{ route('products.show', $product) }}" class="font-serif text-[1.45rem] font-semibold leading-none transition group-hover:text-clay">{{ $product->name }}</a>
        <p class="mt-1.5 text-xs text-ink/50">Painel artesanal em madeira MDF</p>
        <div class="mt-3 flex items-center justify-between gap-3">
            <strong class="text-sm">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</strong>
            @if ($interactive)
                <button wire:click="add({{ $product->id }})" wire:loading.attr="disabled" class="grid size-9 place-items-center rounded-full bg-bark text-white transition hover:bg-clay disabled:opacity-50" aria-label="Adicionar {{ $product->name }} ao carrinho">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20.5 7H6"/><path d="M12 9v5M9.5 11.5h5"/></svg>
                </button>
            @else
                <span class="text-xs font-semibold uppercase tracking-[.12em] text-bark">Ver peça →</span>
            @endif
        </div>
    </div>
</article>
