<div>
    <div class="mx-auto max-w-[1320px] px-5 pb-16 pt-6 sm:px-8 lg:pb-24">
        <nav class="mb-6 text-xs text-ink/45"><a href="{{ route('home') }}" class="hover:text-bark">Início</a><span class="mx-2">/</span><a href="{{ route('store') }}" class="hover:text-bark">Loja</a><span class="mx-2">/</span><span class="text-ink">{{ $product->name }}</span></nav>
        <div class="grid gap-9 lg:grid-cols-[1.18fr_.82fr] lg:gap-16">
            <div class="overflow-hidden rounded-sm bg-sand"><div class="aspect-[4/3]">@if ($product->image_path)<img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="size-full object-cover">@else<div class="grid size-full place-items-center font-serif text-3xl">Feito à mão</div>@endif</div></div>
            <div class="self-center lg:py-8">
                <p class="text-[10px] font-semibold uppercase tracking-[.22em] text-clay">Produzido sob encomenda</p>
                <h1 class="mt-3 font-serif text-5xl font-semibold leading-none tracking-[-.04em] sm:text-6xl">{{ $product->name }}</h1>
                <p class="mt-5 text-2xl font-semibold">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</p>
                <p class="mt-2 text-xs text-ink/45">Produção em até {{ $product->production_days }} dias úteis</p>
                <div class="my-7 h-px bg-ink/10"></div>
                <p class="text-sm leading-7 text-ink/65">{{ $product->description }}</p>
                @if (session('status'))<p class="mt-5 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</p>@endif
                <div class="mt-8 grid grid-cols-[88px_1fr] gap-3"><label><span class="sr-only">Quantidade</span><input wire:model="quantity" type="number" min="1" max="99" class="h-13 w-full rounded-full border-ink/15 bg-paper text-center focus:border-bark focus:ring-bark"></label><button wire:click="add" wire:loading.attr="disabled" class="h-13 rounded-full bg-bark px-6 text-sm font-semibold text-white transition hover:bg-clay disabled:opacity-50">Adicionar ao carrinho</button></div>
                <div class="mt-8 grid grid-cols-3 gap-3 border-t border-ink/10 pt-6 text-center"><div><strong class="block text-xs">Feito à mão</strong><span class="mt-1 block text-[10px] text-ink/45">peça única</span></div><div class="border-x border-ink/10"><strong class="block text-xs">MDF</strong><span class="mt-1 block text-[10px] text-ink/45">material resistente</span></div><div><strong class="block text-xs">Compra segura</strong><span class="mt-1 block text-[10px] text-ink/45">via Asaas</span></div></div>
            </div>
        </div>
        <section class="mt-16 border-y border-ink/10 py-10 lg:mt-24"><p class="text-[10px] font-semibold uppercase tracking-[.2em] text-clay">Detalhes da peça</p><dl class="mt-6 grid gap-6 text-sm sm:grid-cols-4"><div><dt class="text-ink/45">Dimensões</dt><dd class="mt-1 font-semibold">{{ $product->width_cm }} × {{ $product->height_cm }} cm</dd></div><div><dt class="text-ink/45">Profundidade</dt><dd class="mt-1 font-semibold">{{ $product->length_cm }} cm</dd></div><div><dt class="text-ink/45">Peso</dt><dd class="mt-1 font-semibold">{{ $product->weight_kg }} kg</dd></div><div><dt class="text-ink/45">Prazo de produção</dt><dd class="mt-1 font-semibold">{{ $product->production_days }} dias úteis</dd></div></dl></section>
    </div>
</div>
