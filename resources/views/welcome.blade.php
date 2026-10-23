<x-layouts.public>
    <section class="px-5 py-6 sm:px-8 lg:px-12 lg:py-10">
        <div class="mx-auto grid max-w-[1400px] overflow-hidden bg-cream lg:grid-cols-[.76fr_1.24fr]">
            <div class="order-2 flex flex-col justify-center px-6 py-12 sm:px-10 lg:order-1 lg:px-14 lg:py-20 xl:px-20">
                <p class="text-[10px] font-semibold uppercase tracking-[.24em] text-clay">Arte popular em madeira</p>
                <h1 class="mt-6 max-w-xl font-serif text-[2.85rem] leading-[1.04] tracking-[-.045em] sm:text-6xl xl:text-[4.7rem]">Pequenas casas.<br>Grandes histórias.</h1>
                <p class="mt-6 max-w-md text-sm leading-7 text-ink/60">Painéis únicos, desenhados e pintados à mão para transformar lembranças, cores e paisagens em peças que atravessam o tempo.</p>
                <div class="mt-9 flex flex-wrap items-center gap-6"><a href="{{ route('store') }}" class="editorial-button">Conhecer a coleção</a><a href="#processo" class="editorial-link text-xs font-semibold uppercase tracking-[.1em]">Como fazemos</a></div>
            </div>
            <div class="order-1 min-h-[360px] overflow-hidden bg-sand sm:min-h-[520px] lg:order-2 lg:min-h-[680px]">
                @if ($heroProduct?->image_url)<img src="{{ $heroProduct->image_url }}" alt="{{ $heroProduct->name }}" class="size-full object-cover transition duration-1000 hover:scale-[1.02]">@else<div class="grid size-full place-items-center px-8 text-center font-serif text-3xl">Nossa primeira peça está sendo preparada.</div>@endif
            </div>
        </div>
    </section>

    <section class="border-y border-ink/10 px-5 py-7 sm:px-8">
        <div class="mx-auto grid max-w-[1250px] grid-cols-2 gap-6 text-center lg:grid-cols-4">
            @foreach ([['Feito à mão', 'Cada detalhe é único'], ['Sob demanda', '15 dias de produção'], ['Envio acompanhado', 'Do ateliê até você'], ['Pagamento seguro', 'Compra protegida']] as [$title, $copy])
                <div><strong class="font-serif text-lg">{{ $title }}</strong><span class="mt-1 block text-[10px] uppercase tracking-[.12em] text-ink/45">{{ $copy }}</span></div>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-[1400px] px-5 py-20 sm:px-8 lg:px-12 lg:py-28">
        <div class="flex items-end justify-between gap-6"><div><p class="text-[10px] font-semibold uppercase tracking-[.24em] text-clay">Peças disponíveis</p><h2 class="mt-3 font-serif text-4xl tracking-[-.04em] sm:text-6xl">Da nossa coleção</h2></div><a href="{{ route('store') }}" class="editorial-link hidden text-xs font-semibold uppercase tracking-[.1em] sm:block">Ver todas</a></div>
        <div class="mt-12 grid grid-cols-2 gap-x-4 gap-y-12 md:grid-cols-4 lg:gap-x-7">@forelse ($featuredProducts as $product)<x-product-card :product="$product" :interactive="false" />@empty<div class="col-span-full"><x-empty-state title="A coleção está sendo preparada">Execute <code>php artisan db:seed</code> para cadastrar a peça Casas da Vila.</x-empty-state></div>@endforelse</div>
        <a href="{{ route('store') }}" class="editorial-button mt-10 w-full sm:hidden">Ver coleção completa</a>
    </section>

    <section id="processo" class="bg-cream px-5 py-20 sm:px-8 lg:px-12 lg:py-28">
        <div class="mx-auto grid max-w-[1280px] gap-12 lg:grid-cols-[.8fr_1.2fr] lg:items-start">
            <div class="lg:sticky lg:top-28"><p class="text-[10px] font-semibold uppercase tracking-[.24em] text-clay">Do ateliê para sua casa</p><h2 class="mt-4 max-w-md font-serif text-5xl leading-[1.02] tracking-[-.04em] sm:text-6xl">Uma peça feita no tempo do cuidado.</h2><p class="mt-6 max-w-md text-sm leading-7 text-ink/60">Cada casinha nasce de um processo manual. O desenho, o corte e a pintura tornam cada painel diferente de todos os outros.</p></div>
            <ol class="border-t border-ink/15">
                @foreach ([['01', 'Você escolhe', 'Encontre o modelo que conversa com seu espaço e com a história que deseja contar.'], ['02', 'Nós produzimos', 'A peça é preparada sob demanda, com até 15 dias para ganhar forma, textura e cor.'], ['03', 'A história chega', 'Embalamos com cuidado e enviamos com acompanhamento até a sua casa.']] as [$number, $title, $copy])
                    <li class="grid gap-4 border-b border-ink/15 py-8 sm:grid-cols-[80px_1fr_1.2fr] sm:items-baseline"><span class="text-xs text-ink/40">{{ $number }}</span><strong class="font-serif text-2xl font-normal">{{ $title }}</strong><p class="text-sm leading-7 text-ink/55">{{ $copy }}</p></li>
                @endforeach
            </ol>
        </div>
    </section>

    @if ($heroProduct)
        <section class="px-5 py-20 sm:px-8 lg:px-12 lg:py-28"><div class="mx-auto grid max-w-[1280px] gap-0 lg:grid-cols-[1.15fr_.85fr]"><div class="min-h-[420px]"><img src="{{ $heroProduct->image_url }}" alt="Detalhes da peça {{ $heroProduct->name }}" class="size-full object-cover"></div><div class="flex flex-col justify-center bg-ink p-8 text-paper sm:p-12 lg:p-16"><p class="text-[10px] font-semibold uppercase tracking-[.24em] text-paper/50">Peça em destaque</p><h2 class="mt-5 font-serif text-4xl leading-none sm:text-5xl">{{ $heroProduct->name }}</h2><p class="mt-6 text-sm leading-7 text-paper/60">{{ $heroProduct->description }}</p><a href="{{ route('products.show', $heroProduct) }}" class="mt-8 w-fit border-b border-paper pb-1 text-xs font-semibold uppercase tracking-[.12em]">Conhecer esta peça</a></div></div></section>
    @endif
</x-layouts.public>
