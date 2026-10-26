<x-layouts.public title="Como produzimos — Casa das Casinhas">
    <section class="bg-clay/35 px-5 py-16 sm:px-8 lg:px-12 lg:py-24">
        <div class="mx-auto max-w-[1280px]">
            <h1 class="mt-4 max-w-4xl font-serif text-4xl leading-[1.04] tracking-[-.04em] sm:text-6xl">Como produzimos</h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-ink/65">Cada pedido passa pelas mãos do artesão antes de chegar ao seu endereço.</p>
        </div>
    </section>

    <section class="bg-paper px-5 py-16 sm:px-8 lg:px-12 lg:py-24">
        <div class="mx-auto max-w-[1280px]">
            <ol class="border-t border-clay">
                @foreach ([['01', 'Você escolhe', 'Você escolhe a peça e faz o pedido.'], ['02', 'O artesão produz', 'O artesão produz a sua casinha em até 15 dias.'], ['03', 'Nós enviamos', 'Embalamos e enviamos para o seu endereço.']] as [$number, $title, $copy])
                    <li class="grid gap-5 border-b border-clay py-10 sm:grid-cols-[80px_1fr_1.2fr] sm:items-baseline lg:py-14">
                        <span class="text-base text-terracotta">{{ $number }}</span>
                        <strong class="font-serif text-3xl font-normal sm:text-4xl">{{ $title }}</strong>
                        <p class="text-lg leading-8 text-ink/65">{{ $copy }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
</x-layouts.public>
