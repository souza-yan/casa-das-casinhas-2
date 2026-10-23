<x-layouts.public>
    <section id="inicio" class="scroll-mt-24 px-5 py-6 sm:px-8 lg:px-12 lg:py-10">
        <div class="mx-auto grid max-w-[1400px] overflow-hidden bg-cream lg:grid-cols-[.76fr_1.24fr]">
            <div class="order-2 flex flex-col justify-center px-6 py-12 sm:px-10 lg:order-1 lg:px-14 lg:py-20 xl:px-20">
                <h1 class="mt-6 max-w-xl font-serif text-[2.85rem] leading-[1.04] tracking-[-.045em] sm:text-6xl xl:text-[4.7rem]">Pequenas casas.<br>Grandes histórias.</h1>
                <p class="mt-6 max-w-md text-base leading-7 text-ink/60">Painéis únicos, desenhados e pintados à mão para transformar lembranças, cores e paisagens em peças que atravessam o tempo.</p>
                <div class="mt-9 flex flex-wrap items-center gap-6"><a href="{{ route('store') }}" class="editorial-button">Ver produtos</a><a href="#processo" class="editorial-link text-sm font-semibold uppercase tracking-[.1em]">Como produzimos</a></div>
            </div>
            <div class="order-1 min-h-[360px] overflow-hidden bg-sand sm:min-h-[520px] lg:order-2 lg:min-h-[680px]">
                @if ($heroProduct?->image_url)<img src="{{ $heroProduct->image_url }}" alt="{{ $heroProduct->name }}" class="size-full object-cover transition duration-1000 hover:scale-[1.02]">@else<div class="grid size-full place-items-center px-8 text-center font-serif text-3xl">Nossa primeira peça está sendo preparada.</div>@endif
            </div>
        </div>
    </section>

    <section id="produtos" class="mx-auto max-w-[1400px] scroll-mt-24 px-5 py-20 sm:px-8 lg:px-12 lg:py-28">
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
    </section>

    <section id="sobre" class="scroll-mt-24 border-y border-clay px-5 py-20 sm:px-8 lg:px-12 lg:py-28">
        <div class="mx-auto grid max-w-[1280px] gap-10 lg:grid-cols-2 lg:items-center lg:gap-20">
            <div class="min-h-80 overflow-hidden bg-clay sm:min-h-[500px]">
                <img src="{{ asset('images/home-artesoes.png') }}" alt="Artesãos produzindo peças de madeira feitas à mão" class="size-full object-cover">
            </div>
            <div><h2 class="font-serif text-5xl leading-[1.02] tracking-[-.04em] sm:text-6xl">Sobre nós</h2><p class="mt-7 max-w-xl text-lg leading-8 text-ink/65">A Casa das Casinhas reúne peças de madeira feitas à mão por artesãos. Cada casinha é montada e pintada individualmente, por isso nenhuma é idêntica à outra.</p></div>
        </div>
    </section>

    <section id="como-produzimos" class="scroll-mt-24 bg-cream px-5 py-20 sm:px-8 lg:px-12 lg:py-28">
        <div class="mx-auto max-w-[1280px]">
            <div class="max-w-3xl"><h2 class="font-serif text-5xl leading-[1.02] tracking-[-.04em] sm:text-6xl">Como produzimos</h2></div>
            <ol class="mt-14 border-t border-clay lg:mt-20">
                @foreach ([['01', 'Você escolhe', 'Você escolhe a peça e faz o pedido.'], ['02', 'O artesão produz', 'O artesão produz a sua casinha em até 15 dias.'], ['03', 'Nós enviamos', 'Embalamos e enviamos para o seu endereço.']] as [$number, $title, $copy])
                    <li class="grid gap-5 border-b border-clay py-10 sm:grid-cols-[80px_1fr_1.2fr] sm:items-baseline lg:py-14"><span class="text-base text-terracotta">{{ $number }}</span><strong class="font-serif text-3xl font-normal sm:text-4xl">{{ $title }}</strong><p class="text-lg leading-8 text-ink/65">{{ $copy }}</p></li>
                @endforeach
            </ol>
        </div>
    </section>
</x-layouts.public>
