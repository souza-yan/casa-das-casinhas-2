<div>
    <header class="relative overflow-hidden bg-bark px-5 py-14 text-white sm:px-8 lg:py-20">
        <div class="absolute inset-0 opacity-[.08]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 18px 18px"></div>
        <div class="relative mx-auto max-w-[1320px]"><p class="text-[11px] font-semibold uppercase tracking-[.22em] text-sand">Loja online</p><h1 class="mt-3 font-serif text-5xl font-semibold tracking-[-.04em] sm:text-6xl">Nossa coleção</h1><p class="mt-4 max-w-xl text-sm leading-7 text-white/65">Peças feitas à mão, uma por vez. Escolha a casinha que vai contar uma história no seu espaço.</p></div>
    </header>

    <div class="mx-auto max-w-[1320px] px-5 py-12 sm:px-8 lg:py-16">
        @if (session('status'))<div class="mb-8 border border-emerald-800/15 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>@endif
        <div class="flex flex-col gap-4 border-b border-ink/10 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <label class="relative block w-full sm:max-w-sm"><span class="mb-2 block text-[10px] font-semibold uppercase tracking-[.18em] text-ink/45">Buscar</span><svg class="absolute bottom-3 left-0 size-4 text-ink/40" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><input wire:model.live.debounce.300ms="search" type="search" placeholder="Nome da peça..." class="w-full border-0 border-b border-ink/20 bg-transparent py-2.5 pl-6 pr-2 text-sm focus:border-bark focus:ring-0"></label>
            <label class="block sm:w-52"><span class="mb-2 block text-[10px] font-semibold uppercase tracking-[.18em] text-ink/45">Ordenar por</span><select wire:model.live="sort" class="w-full border-0 border-b border-ink/20 bg-transparent py-2.5 pl-0 text-sm focus:border-bark focus:ring-0"><option value="latest">Mais recentes</option><option value="price_asc">Menor preço</option><option value="price_desc">Maior preço</option></select></label>
        </div>
        <div class="mt-10 grid grid-cols-2 gap-x-4 gap-y-12 md:grid-cols-3 lg:grid-cols-4 lg:gap-x-6">@forelse ($products as $product)<x-product-card :product="$product" wire:key="product-{{ $product->id }}" />@empty<div class="col-span-full py-16 text-center"><p class="font-serif text-3xl">Nenhuma peça encontrada.</p><p class="mt-2 text-sm text-ink/50">Tente buscar por outro nome.</p></div>@endforelse</div>
        <div class="mt-14">{{ $products->links() }}</div>
    </div>
</div>
