<div>
    <header class="border-b border-clay px-5 py-16 sm:px-8 lg:px-12 lg:py-24">
        <div class="mx-auto max-w-[1400px]"><h1 class="font-serif text-5xl tracking-[-.05em] sm:text-7xl">Produtos</h1><p class="mt-5 max-w-xl text-base leading-7 text-ink/55">Peças feitas à mão, uma por vez. Escolha a paisagem que vai levar cor, memória e presença ao seu espaço.</p></div>
    </header>

    <div class="mx-auto max-w-[1400px] px-5 py-12 sm:px-8 lg:px-12 lg:py-20">
        <div class="flex flex-col gap-4 border-b border-clay pb-6 sm:flex-row sm:items-end sm:justify-between">
            <label class="relative block w-full sm:max-w-sm"><span class="mb-2 block text-sm font-semibold uppercase tracking-[.18em] text-ink/45">Buscar</span><svg class="absolute bottom-3 left-0 size-4 text-ink/40" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><input wire:model.live.debounce.300ms="search" type="search" placeholder="Nome da peça..." class="w-full border-0 border-b border-clay bg-transparent py-2.5 pl-6 pr-2 text-base focus:border-terracotta focus:ring-0"></label>
            <label class="block sm:w-52"><span class="mb-2 block text-sm font-semibold uppercase tracking-[.18em] text-ink/45">Ordenar por</span><select wire:model.live="sort" class="w-full border-0 border-b border-clay bg-transparent py-2.5 pl-0 text-base focus:border-terracotta focus:ring-0"><option value="latest">Mais recentes</option><option value="price_asc">Menor preço</option><option value="price_desc">Maior preço</option></select></label>
        </div>
        <div class="mt-10 grid grid-cols-2 gap-x-4 gap-y-12 md:grid-cols-3 lg:grid-cols-4 lg:gap-x-6">@forelse ($products as $product)<x-product-card :product="$product" :interactive="! auth()->check() || auth()->user()->role !== \App\Enums\Role::ADMIN" wire:key="product-{{ $product->id }}" />@empty<div class="col-span-full py-16 text-center"><p class="font-serif text-3xl">Nenhuma peça encontrada.</p><p class="mt-2 text-base text-ink/50">Tente buscar por outro nome.</p></div>@endforelse</div>
        <div class="mt-14">{{ $products->links() }}</div>
    </div>
</div>
