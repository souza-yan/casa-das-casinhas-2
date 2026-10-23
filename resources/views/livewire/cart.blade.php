<div class="mx-auto max-w-[1120px] px-5 py-12 sm:px-8 lg:py-20">
    <div class="flex items-end justify-between border-b border-ink/10 pb-7"><div><p class="text-[10px] font-semibold uppercase tracking-[.22em] text-clay">Sua seleção</p><h1 class="mt-2 font-serif text-5xl font-semibold tracking-[-.04em]">Carrinho</h1></div><a href="{{ route('store') }}" class="hidden text-sm font-semibold sm:block">Continuar comprando →</a></div>
    <div class="mt-8 grid gap-12 lg:grid-cols-[1fr_340px]">
        <section class="divide-y divide-ink/10">
            @forelse ($items as $item)
                <article class="grid grid-cols-[100px_1fr] gap-4 py-6 first:pt-0 sm:grid-cols-[130px_1fr_auto]" wire:key="cart-{{ $item['product']->id }}">
                    <a href="{{ route('products.show', $item['product']) }}" class="aspect-square overflow-hidden rounded-sm bg-sand">@if ($item['product']->image_path)<img src="{{ $item['product']->image_url }}" alt="{{ $item['product']->name }}" class="size-full object-cover">@endif</a>
                    <div><h2 class="font-serif text-2xl font-semibold">{{ $item['product']->name }}</h2><p class="mt-1 text-xs text-ink/45">Painel artesanal em MDF</p><div class="mt-5 flex items-center gap-4"><input aria-label="Quantidade" type="number" min="1" max="99" value="{{ $item['quantity'] }}" wire:change="update({{ $item['product']->id }}, $event.target.value)" class="h-9 w-16 rounded-full border-ink/15 bg-paper py-1 text-center text-sm"><button wire:click="remove({{ $item['product']->id }})" class="text-xs text-ink/45 underline underline-offset-4 hover:text-red-700">Remover</button></div></div>
                    <strong class="col-start-2 text-sm sm:col-auto">R$ {{ number_format($item['subtotal'], 2, ',', '.') }}</strong>
                </article>
            @empty
                <div class="py-16 text-center"><p class="font-serif text-3xl">Seu carrinho está esperando uma história.</p><p class="mt-2 text-sm text-ink/50">Escolha uma peça artesanal para começar.</p><a class="mt-6 inline-block rounded-full bg-bark px-6 py-3 text-sm font-semibold text-white" href="{{ route('store') }}">Conhecer a coleção</a></div>
            @endforelse
        </section>
        <aside class="h-fit border border-ink/10 bg-paper p-6 sm:p-8"><p class="text-[10px] font-semibold uppercase tracking-[.2em] text-clay">Resumo do pedido</p><div class="mt-6 flex justify-between text-sm"><span class="text-ink/55">Subtotal</span><strong>R$ {{ number_format($subtotal, 2, ',', '.') }}</strong></div><p class="mt-3 text-xs leading-5 text-ink/45">O frete será calculado na próxima etapa.</p>@if ($items->isNotEmpty())<a href="{{ route('checkout') }}" class="mt-7 block rounded-full bg-bark px-5 py-3.5 text-center text-sm font-semibold text-white transition hover:bg-clay">Finalizar compra →</a>@endif<div class="mt-6 border-t border-ink/10 pt-5 text-center text-[10px] uppercase tracking-[.14em] text-ink/40">Pagamento seguro</div></aside>
    </div>
</div>
