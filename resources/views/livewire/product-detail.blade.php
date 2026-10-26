<div>
    <div class="mx-auto max-w-[1400px] px-5 pb-20 pt-6 sm:px-8 lg:px-12 lg:pb-28">
        <nav class="mb-6 text-sm text-ink/45"><a href="{{ route('home') }}" class="hover:text-bark">Início</a><span class="mx-2">/</span><a href="{{ route('store') }}" class="hover:text-bark">Produtos</a><span class="mx-2">/</span><span class="text-ink">{{ $product->name }}</span></nav>
        <div class="grid gap-10 lg:grid-cols-[1.28fr_.72fr] lg:gap-20">
            <div class="aspect-[4/5] self-start overflow-hidden rounded-sm bg-sand">@if ($product->image_path)<img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="block size-full object-contain">@else<div class="grid size-full place-items-center font-serif text-3xl">Feito à mão</div>@endif</div>
            <div class="self-center lg:py-8">
                <p class="text-sm font-semibold uppercase tracking-[.22em] text-clay">Produzido sob encomenda</p>
                <h1 class="mt-4 font-serif text-5xl leading-none tracking-[-.05em] sm:text-6xl">{{ $product->name }}</h1>
                <p class="mt-5 text-2xl font-semibold">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</p>
                <p class="mt-2 text-sm text-ink/45">Produção em até {{ $product->production_days }} dias úteis</p>
                <div class="my-7 h-px bg-ink/10"></div>
                <p class="text-base leading-7 text-ink/65">{{ $product->description }}</p>
                @if (auth()->user()?->role === \App\Enums\Role::ADMIN)
                    <a href="{{ route('admin.dashboard') }}" class="editorial-button mt-8 w-full">Voltar ao painel administrativo</a>
                @else
                    @if ($product->is_customizable)
                        <section class="mt-8 border-y border-ink/10 py-6">
                            <h2 class="font-serif text-2xl">Personalize a sua peça</h2>
                            <p class="mt-2 text-base text-ink/65">Descreva brevemente as cores, nomes e detalhes que deseja. O artesão analisará as instruções após a confirmação do pagamento.</p>
                            <label class="mt-5 block text-base font-semibold">Como você quer personalizar? <span class="text-terracotta">*</span><textarea wire:model="personalization" rows="5" maxlength="1500" class="mt-2 block w-full rounded-sm border border-ink/20 bg-paper p-3 font-normal focus:border-ink focus:ring-ink" placeholder="Ex.: usar tons de azul, incluir o nome Ana e uma janela maior."></textarea></label>
                            @error('personalization')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                        </section>
                    @endif
                    <div class="mt-8 grid grid-cols-[88px_1fr] gap-3"><label><span class="sr-only">Quantidade</span><input wire:model="quantity" type="number" min="1" max="99" class="h-12 w-full border-ink/20 bg-paper text-center focus:border-bark focus:ring-bark"></label><button wire:click="add" wire:loading.attr="disabled" class="editorial-button disabled:opacity-50">Adicionar ao carrinho</button></div>
                @endif
                <div class="mt-8 grid grid-cols-3 gap-3 border-t border-ink/10 pt-6 text-center"><div><strong class="block text-sm">Feito à mão</strong><span class="mt-1 block text-sm text-ink/45">peça única</span></div><div class="border-x border-ink/10"><strong class="block text-sm">MDF</strong><span class="mt-1 block text-sm text-ink/45">material resistente</span></div><div><strong class="block text-sm">Compra segura</strong><span class="mt-1 block text-sm text-ink/45">via Asaas</span></div></div>
            </div>
        </div>
        <section class="mt-16 border-y border-ink/10 py-10 lg:mt-24"><p class="text-sm font-semibold uppercase tracking-[.2em] text-clay">Detalhes da peça</p><dl class="mt-6 grid gap-6 text-base sm:grid-cols-3"><div><dt class="text-ink/45">Altura</dt><dd class="mt-1 font-semibold">{{ $product->height_cm }} cm</dd></div><div><dt class="text-ink/45">Largura</dt><dd class="mt-1 font-semibold">{{ $product->width_cm }} cm</dd></div><div><dt class="text-ink/45">Comprimento</dt><dd class="mt-1 font-semibold">{{ $product->length_cm }} cm</dd></div></dl></section>
    </div>
</div>
