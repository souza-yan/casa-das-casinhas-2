<x-layouts.public>
    <section id="inicio" class="scroll-mt-24 bg-paper px-5 py-6 sm:px-8 lg:px-12 lg:py-10">
        <div class="mx-auto grid max-w-[1400px] overflow-hidden bg-clay/35 lg:grid-cols-[.76fr_1.24fr]">
            <div class="order-2 flex flex-col justify-center px-6 py-12 sm:px-10 lg:order-1 lg:px-14 lg:py-20 xl:px-20">
                <h1 class="mt-6 max-w-xl font-serif text-[2.85rem] leading-[1.04] tracking-[-.045em] sm:text-6xl xl:text-[4.7rem]">Pequenas casas.<br>Grandes histórias.</h1>
                <p class="mt-6 max-w-md text-base leading-7 text-ink/60">Painéis únicos, desenhados e pintados à mão para transformar lembranças, cores e paisagens em peças que atravessam o tempo.</p>
                <div class="mt-9 flex flex-wrap items-center gap-6"><a href="{{ route('store') }}" class="editorial-button">Ver produtos</a><a href="{{ route('production') }}" class="editorial-link text-sm font-semibold uppercase tracking-[.1em]">Como produzimos</a></div>
            </div>
            <div class="order-1 min-h-[360px] overflow-hidden bg-sand sm:min-h-[520px] lg:order-2 lg:min-h-[680px]">
                @if ($heroProduct?->image_url)<img src="{{ $heroProduct->image_url }}" alt="{{ $heroProduct->name }}" class="size-full object-cover transition duration-1000 hover:scale-[1.02]">@else<div class="grid size-full place-items-center px-8 text-center font-serif text-3xl">Nossa primeira peça está sendo preparada.</div>@endif
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

        <h2 class="font-serif text-4xl tracking-[-.04em] sm:text-5xl">As mais procuradas</h2>
        @if ($popularProducts->isNotEmpty())
            <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-3">
                @foreach ($popularProducts as $product)
                    <x-product-card :product="$product" :interactive="false" />
                @endforeach
            </div>
        @endif
        <a href="{{ route('store') }}" class="editorial-button mt-12">Ver todos os produtos</a>
        </div>
    </section>

</x-layouts.public>
