<div class="mx-auto max-w-[1120px] px-5 py-12 sm:px-8 lg:py-20">
    <div class="flex items-end justify-between border-b border-ink/10 pb-7"><div><p class="text-sm font-semibold uppercase tracking-[.22em] text-clay">Sua seleção</p><h1 class="mt-2 font-serif text-4xl font-semibold tracking-[-.04em] sm:text-6xl">Carrinho</h1></div><a href="{{ route('store') }}" class="hidden text-base font-semibold sm:block">Continuar comprando →</a></div>
    <div class="mt-8 grid gap-12 lg:grid-cols-[1fr_340px]">
        <section class="divide-y divide-ink/10">
            @forelse ($items as $item)
                <article class="grid grid-cols-[100px_1fr] gap-4 py-6 first:pt-0 sm:grid-cols-[130px_1fr_auto]" wire:key="cart-{{ $item['key'] }}">
                    <a href="{{ route('products.show', $item['product']) }}"><x-product-image :product="$item['product']" variant="thumb" size="cart" /></a>
                    <div><h2 class="font-serif text-2xl font-semibold">{{ $item['product']->name }}</h2><p class="mt-1 text-sm text-ink/45">Painel artesanal em MDF</p>@if ($item['product']->is_customizable)<div class="mt-4 flex items-center gap-3">@if ($item['reference_paths'])<p class="text-sm text-ink/50">{{ count($item['reference_paths']) }} imagem(ns) de referência anexada(s).</p>@endif<button type="button" wire:click="editPersonalization('{{ $item['key'] }}')" class="grid size-9 place-items-center rounded-full border border-ink/20 text-ink transition hover:bg-ink hover:text-paper" aria-label="Editar personalização de {{ $item['product']->name }}" title="Editar personalização"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m14.5 4.5 5 5M4 20l4.1-1 10.8-10.8a3.5 3.5 0 0 0-5-5L3.1 14 2 18.9 4 20Z"/></svg></button></div>@endif<div class="mt-5 flex items-center gap-4"><input aria-label="Quantidade" type="number" min="1" max="99" value="{{ $item['quantity'] }}" wire:change="update('{{ $item['key'] }}', $event.target.value)" class="h-9 w-16 rounded-full border-ink/15 bg-paper py-1 text-center text-base"><button wire:click="remove('{{ $item['key'] }}')" class="text-sm text-ink/45 underline underline-offset-4 hover:text-red-700">Remover</button></div></div>
                    <strong class="col-start-2 text-base sm:col-auto">R$ {{ number_format($item['subtotal'], 2, ',', '.') }}</strong>
                </article>
            @empty
                <div class="py-16 text-center"><p class="font-serif text-3xl">Seu carrinho está esperando uma história.</p><p class="mt-2 text-base text-ink/50">Escolha uma peça artesanal para começar.</p><x-action-button :href="route('store')" class="mt-6">Ver produtos</x-action-button></div>
            @endforelse
        </section>
        <aside class="h-fit border border-ink/10 bg-cream p-6 sm:p-8"><p class="text-sm font-semibold uppercase tracking-[.2em] text-clay">Resumo do pedido</p><div class="mt-6 flex justify-between text-base"><span class="text-ink/55">Subtotal</span><strong>R$ {{ number_format($subtotal, 2, ',', '.') }}</strong></div><p class="mt-3 text-sm leading-5 text-ink/45">O frete será calculado na próxima etapa.</p>@if ($items->isNotEmpty())<x-action-button :href="route('checkout')" class="mt-7 w-full">Finalizar compra</x-action-button>@endif<div class="mt-6 border-t border-ink/10 pt-5 text-center text-sm uppercase tracking-[.14em] text-ink/40">Pagamento seguro</div></aside>
    </div>
<x-modal wire="personalizationModal" size="lg" center title="Personalize a sua peça">
    <form wire:submit="savePersonalization" class="grid gap-5">
        @if ($editingPersonalizationKey)
            <label class="grid gap-2 text-sm font-semibold"><span>Descrição da personalização <span class="text-terracotta">*</span></span><textarea wire:model="personalizations.{{ $editingPersonalizationKey }}" rows="6" maxlength="1500" required class="w-full rounded-sm border border-ink/20 bg-paper p-3 text-base font-normal focus:border-ink focus:ring-ink" placeholder="Ex.: usar tons de azul, incluir o nome Ana e uma janela maior."></textarea></label>
            @error('personalizations.'.$editingPersonalizationKey)<p class="text-sm text-red-700">{{ $message }}</p>@enderror
        @endif
        <div class="flex justify-end"><x-action-button type="submit" wire:loading.attr="disabled">Salvar personalização</x-action-button></div>
    </form>
</x-modal>
</div>
