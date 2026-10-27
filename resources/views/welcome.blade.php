<x-layouts.public>
    <section id="inicio" class="scroll-mt-24 bg-paper pb-12 pt-16 lg:pb-16 lg:pt-20">
        <div class="site-container grid gap-8 lg:grid-cols-2 lg:items-center lg:gap-12">
            <div class="flex flex-col items-start">
                <h1 class="max-w-xl font-serif text-[2.85rem] leading-[1.04] tracking-[-.045em] sm:text-6xl xl:text-[4.7rem]">Pequenas casas.<br>Grandes histórias.</h1>
                <p class="mt-6 max-w-md text-base leading-7 text-ink/60">Painéis únicos, desenhados e pintados à mão para transformar lembranças, cores e paisagens em peças que atravessam o tempo.</p>
                <div class="mt-9 flex flex-wrap items-center gap-6"><x-action-button :href="route('store')">Ver produtos</x-action-button><a href="{{ route('about') }}" class="editorial-link text-sm font-semibold uppercase tracking-[.1em]">Conheça mais sobre nós</a></div>
            </div>
            <div class="flex items-start justify-center">
                <div class="aspect-square w-full max-w-[620px] overflow-hidden rounded-full">
                    <img src="{{ asset('images/home-artesoes.png') }}" alt="Artesãos da Casa das Casinhas" class="block size-full object-cover object-center transition duration-1000 hover:scale-[1.02]">
                </div>
            </div>
        </div>
    </section>

    <section id="produtos" class="scroll-mt-24 bg-clay/35 py-12 text-ink lg:py-16">
        <div class="site-container">
        @php
            $popularSlugs = ['casinha', 'vila-de-3-casas', 'favela-de-5-casas'];
            $productsBySlug = \App\Models\Product::query()
                ->where('is_active', true)
                ->whereIn('slug', $popularSlugs)
                ->get()
                ->keyBy('slug');
            $popularProducts = collect($popularSlugs)
                ->map(fn (string $slug) => $productsBySlug->get($slug))
                ->filter();
        @endphp

        <h2 class="text-center font-serif text-4xl tracking-[-.04em] sm:text-5xl">As mais procuradas</h2>
        @if ($popularProducts->isNotEmpty())
            <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-3">
                @foreach ($popularProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
        <a href="{{ route('store') }}" class="mt-12 inline-flex border-b border-ink pb-1 text-sm font-semibold uppercase tracking-[.12em] text-ink transition-colors hover:text-clay hover:border-clay">Ver todos os produtos</a>
        </div>
    </section>

</x-layouts.public>
