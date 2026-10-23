<x-layouts.public>
    <section class="border-b border-ink/10 bg-paper">
        <div class="mx-auto grid max-w-[1440px] lg:grid-cols-[.78fr_1.22fr]">
            <div class="order-2 flex flex-col justify-center px-6 py-12 sm:px-10 lg:order-1 lg:px-14 lg:py-20">
                <p class="text-[11px] font-semibold uppercase tracking-[.22em] text-clay">Artesanato que conta histórias</p>
                <h1 class="mt-5 max-w-xl font-serif text-5xl font-semibold leading-[.92] tracking-[-.045em] sm:text-6xl xl:text-7xl">Casinhas de madeira que transformam espaços.</h1>
                <p class="mt-6 max-w-md text-[15px] leading-7 text-ink/60">Peças únicas, feitas à mão, com pintura e entalhe em madeira MDF. Cada casinha carrega cor, cultura e a beleza do feito à mão.</p>
                <div class="mt-8 flex flex-wrap items-center gap-5"><a href="{{ route('store') }}" class="rounded-full bg-bark px-7 py-3.5 text-sm font-semibold text-white transition hover:bg-clay">Ver coleção →</a><a href="#processo" class="text-sm font-semibold underline decoration-ink/25 underline-offset-4">Conheça nosso fazer</a></div>
            </div>
            <div class="order-1 min-h-[310px] overflow-hidden bg-sand sm:min-h-[440px] lg:order-2 lg:min-h-[620px]">
                @if ($heroProduct?->image_url)
                    <img src="{{ $heroProduct->image_url }}" alt="{{ $heroProduct->name }}" class="size-full object-cover">
                @else
                    <div class="grid size-full place-items-center px-8 text-center font-serif text-3xl text-bark">Nossa primeira peça está sendo preparada.</div>
                @endif
            </div>
        </div>
    </section>

    <section class="border-b border-ink/10 bg-paper px-5 py-7 sm:px-8">
        <div class="mx-auto grid max-w-[1200px] grid-cols-2 gap-x-4 gap-y-7 lg:grid-cols-4">
            @foreach ([['M12 3 4 7v5c0 4.8 3.4 8.1 8 9 4.6-.9 8-4.2 8-9V7l-8-4Z', 'Peças artesanais', 'com significado'], ['M6 7h12v13H6zM9 7V5a3 3 0 0 1 6 0v2', 'Produção sob demanda', 'feita para você'], ['M3 7h11v9H3zM14 10h4l3 3v3h-7zM7 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM18 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z', 'Envio para o Brasil', 'com rastreio'], ['M6 3h12v18H6zM9 7h6M9 11h6M10 16h4', 'Pagamento seguro', 'ambiente protegido']] as [$path, $title, $copy])
                <div class="flex items-center gap-3 lg:justify-center"><svg class="size-7 shrink-0 text-bark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="{{ $path }}"/></svg><div><strong class="block text-xs">{{ $title }}</strong><span class="text-[11px] text-ink/50">{{ $copy }}</span></div></div>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-[1320px] px-5 py-16 sm:px-8 lg:py-24">
        <div class="flex items-end justify-between gap-5"><div><p class="text-[11px] font-semibold uppercase tracking-[.22em] text-clay">Nossa coleção</p><h2 class="mt-2 font-serif text-4xl font-semibold tracking-[-.03em] sm:text-5xl">Destaques da loja</h2></div><a href="{{ route('store') }}" class="hidden text-sm font-semibold sm:block">Ver todos →</a></div>
        <div class="mt-9 grid grid-cols-2 gap-x-4 gap-y-10 lg:grid-cols-4 lg:gap-6">@forelse ($featuredProducts as $product)<x-product-card :product="$product" :interactive="false" />@empty<div class="col-span-full"><x-empty-state title="A coleção está sendo preparada">Execute <code>php artisan db:seed</code> para cadastrar a peça Casas da Vila.</x-empty-state></div>@endforelse</div>
        <a href="{{ route('store') }}" class="mt-10 block rounded-full border border-bark px-6 py-3 text-center text-sm font-semibold sm:hidden">Ver coleção completa</a>
    </section>

    <section id="processo" class="bg-sand/70 px-5 py-16 sm:px-8 lg:py-24">
        <div class="mx-auto grid max-w-[1200px] gap-12 lg:grid-cols-[.8fr_1.2fr] lg:items-center">
            <div><p class="text-[11px] font-semibold uppercase tracking-[.22em] text-clay">Como é feito?</p><h2 class="mt-3 max-w-md font-serif text-5xl font-semibold leading-[.95] tracking-[-.04em]">Do artesanato à sua casa.</h2><p class="mt-5 max-w-md text-sm leading-7 text-ink/60">Cada peça é desenhada, cortada, pintada e detalhada à mão. Um processo cuidadoso que respeita o tempo da criação.</p><a href="{{ route('store') }}" class="mt-7 inline-block text-sm font-semibold underline decoration-ink/25 underline-offset-4">Escolher minha peça →</a></div>
            <ol class="grid gap-px overflow-hidden rounded-sm bg-ink/10 sm:grid-cols-3">
                @foreach ([['01', 'Escolha', 'Encontre o modelo que combina com seu espaço.'], ['02', 'Produzimos', 'A peça ganha forma e cor especialmente para você.'], ['03', 'Receba', 'Enviamos com cuidado e você acompanha o pedido.']] as [$number, $title, $copy])
                    <li class="bg-paper p-7 sm:min-h-60"><span class="font-serif text-4xl text-clay/55">{{ $number }}</span><strong class="mt-12 block font-serif text-2xl">{{ $title }}</strong><p class="mt-3 text-sm leading-6 text-ink/55">{{ $copy }}</p></li>
                @endforeach
            </ol>
        </div>
    </section>

    @if ($heroProduct)
        <section class="bg-paper px-5 py-16 sm:px-8 lg:py-24"><div class="mx-auto grid max-w-[1200px] overflow-hidden bg-cream lg:grid-cols-2"><div class="min-h-72"><img src="{{ $heroProduct->image_url }}" alt="Detalhes da peça {{ $heroProduct->name }}" class="size-full object-cover"></div><div class="flex flex-col justify-center p-8 sm:p-12"><p class="text-[11px] font-semibold uppercase tracking-[.22em] text-clay">Uma peça, muitas histórias</p><h2 class="mt-3 font-serif text-4xl font-semibold leading-none">{{ $heroProduct->name }}</h2><p class="mt-5 text-sm leading-7 text-ink/60">{{ $heroProduct->description }}</p><a href="{{ route('products.show', $heroProduct) }}" class="mt-7 w-fit rounded-full bg-bark px-6 py-3 text-sm font-semibold text-white">Conhecer a peça →</a></div></div></section>
    @endif
</x-layouts.public>
