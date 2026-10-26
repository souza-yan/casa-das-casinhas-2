<x-layouts.public>
    <section id="inicio" class="scroll-mt-24 bg-paper px-5 py-6 sm:px-8 lg:px-12 lg:py-10">
        <div class="mx-auto grid max-w-[1400px] lg:grid-cols-2">
            <div class="order-2 flex flex-col justify-center px-6 py-12 sm:px-10 lg:order-1 lg:px-14 lg:py-20 xl:px-20">
                <h1 class="mt-6 max-w-xl font-serif text-[2.85rem] leading-[1.04] tracking-[-.045em] sm:text-6xl xl:text-[4.7rem]">Pequenas casas.<br>Grandes histórias.</h1>
                <p class="mt-6 max-w-md text-base leading-7 text-ink/60">Painéis únicos, desenhados e pintados à mão para transformar lembranças, cores e paisagens em peças que atravessam o tempo.</p>
                <div class="mt-9 flex flex-wrap items-center gap-6"><x-action-button :href="route('store')">Ver produtos</x-action-button><a href="{{ route('production') }}" class="editorial-link text-sm font-semibold uppercase tracking-[.1em]">Como produzimos</a></div>
            </div>
            <div class="order-1 flex min-h-[360px] items-center justify-center p-8 sm:min-h-[520px] sm:p-12 lg:order-2 lg:min-h-[680px] lg:p-16">
                <div class="aspect-square w-full max-w-[620px] overflow-hidden rounded-full">
                    <img src="{{ asset('images/home-artesoes.png') }}" alt="Artesãos da Casa das Casinhas" class="block size-full object-cover object-center transition duration-1000 hover:scale-[1.02]">
                </div>
            </div>
        </div>
    </section>

    <section id="produtos" class="scroll-mt-24 bg-clay/35 px-5 py-20 text-ink sm:px-8 lg:px-12 lg:py-28">
        <div class="mx-auto max-w-[1400px]">
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
            <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-3">
                @foreach ($popularProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
        <a href="{{ route('store') }}" class="mt-12 inline-flex border-b border-ink pb-1 text-sm font-semibold uppercase tracking-[.12em] text-ink transition-colors hover:text-clay hover:border-clay">Ver todos os produtos</a>
        </div>
    </section>

</x-layouts.public>
