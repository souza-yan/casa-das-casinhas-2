<div>
    <div class="mx-auto max-w-[1400px] px-5 pb-20 pt-6 sm:px-8 lg:px-12 lg:pb-28">
        <nav class="mb-6 text-sm text-ink/45"><a href="{{ route('home') }}" class="hover:text-bark">Início</a><span class="mx-2">/</span><a href="{{ route('store') }}" class="hover:text-bark">Produtos</a><span class="mx-2">/</span><span class="text-ink">{{ $product->name }}</span></nav>
        <div class="grid gap-10 lg:grid-cols-[1.28fr_.72fr] lg:gap-20">
            <x-product-image :product="$product" variant="detail" class="self-start" />
            <div class="self-center lg:py-8">
                <h1 class="font-serif text-5xl leading-none tracking-[-.05em] sm:text-6xl">{{ $product->name }}</h1>
                <p class="mt-5 text-2xl font-semibold">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</p>
                <p class="mt-7 text-base leading-7 text-ink/65">{{ $product->description }}</p>
                @if (auth()->user()?->role === \App\Enums\Role::ADMIN)
                    <x-action-button :href="route('admin.dashboard')" class="mt-8 w-full">Voltar ao painel administrativo</x-action-button>
                @else
                    <section class="mt-8 border-y border-ink/10 py-6">
                        <h2 class="font-serif text-2xl">Personalize a sua peça <span class="text-terracotta">*</span></h2>
                        <label class="mt-5 block"><span class="sr-only">Descrição da personalização</span><textarea wire:model="personalization" rows="5" maxlength="1500" required class="block w-full rounded-sm border border-ink/20 bg-paper p-3 text-base focus:border-ink focus:ring-ink" placeholder="Ex.: usar tons de azul, incluir o nome Ana e uma janela maior."></textarea></label>
                        @error('personalization')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    </section>
                    <div class="mt-8 grid grid-cols-[88px_1fr] gap-3"><label><span class="sr-only">Quantidade</span><input wire:model="quantity" type="number" min="1" max="99" class="h-12 w-full border-ink/20 bg-paper text-center focus:border-bark focus:ring-bark"></label><x-action-button wire:click="add" wire:loading.attr="disabled">{{ auth()->check() ? 'Adicionar ao carrinho' : 'Continuar para compra' }}</x-action-button></div>
                @endif
            </div>
        </div>
        <section class="mt-16 border-y border-ink/10 py-10 lg:mt-24"><p class="text-sm font-semibold uppercase tracking-[.2em] text-clay">Detalhes da peça</p><dl class="mt-6 grid gap-6 text-base sm:grid-cols-3"><div><dt class="text-ink/45">Altura</dt><dd class="mt-1 font-semibold">{{ $product->height_cm }} cm</dd></div><div><dt class="text-ink/45">Largura</dt><dd class="mt-1 font-semibold">{{ $product->width_cm }} cm</dd></div><div><dt class="text-ink/45">Comprimento</dt><dd class="mt-1 font-semibold">{{ $product->length_cm }} cm</dd></div></dl></section>
    </div>
</div>
