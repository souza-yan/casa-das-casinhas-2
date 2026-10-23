@props(['products'])

@php
    $productsBySlug = $products->keyBy('slug');
    $slides = collect(['casinha', 'vila-de-3-casas', 'favela-de-5-casas'])
        ->map(fn (string $slug) => $productsBySlug->get($slug))
        ->filter()
        ->values();
@endphp

<section
    aria-label="As mais pedidas"
    aria-roledescription="carousel"
    aria-live="off"
    x-data="{
        current: 0,
        total: {{ $slides->count() }},
        timer: null,
        touchStartX: 0,
        reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        init() { this.start() },
        start() {
            if (this.reducedMotion || this.total < 2 || this.timer) return
            this.timer = setInterval(() => this.next(), 5000)
        },
        stop() {
            clearInterval(this.timer)
            this.timer = null
        },
        goTo(index) {
            this.current = (index + this.total) % this.total
        },
        next() { this.goTo(this.current + 1) },
        previous() { this.goTo(this.current - 1) },
        finishTouch(event) {
            const distance = event.changedTouches[0].clientX - this.touchStartX
            if (Math.abs(distance) >= 50) distance < 0 ? this.next() : this.previous()
            this.start()
        }
    }"
    @mouseenter="stop()"
    @mouseleave="start()"
    @touchstart.passive="stop(); touchStartX = $event.touches[0].clientX"
    @touchend.passive="finishTouch($event)"
>
    <h2 class="font-serif text-4xl tracking-[-.04em] sm:text-6xl">As mais pedidas</h2>

    @if ($slides->isNotEmpty())
        <div class="relative mt-12 overflow-hidden rounded-sm border border-clay bg-paper">
            <div
                class="flex transition-transform duration-500 ease-out motion-reduce:transition-none"
                :style="`transform: translateX(-${current * 100}%)`"
            >
                @foreach ($slides as $product)
                    <article
                        class="w-full shrink-0"
                        role="group"
                        aria-roledescription="slide"
                        aria-label="{{ $loop->iteration }} de {{ $slides->count() }}"
                    >
                        <a href="{{ route('products.show', $product) }}" class="block">
                            <div class="aspect-[4/3] overflow-hidden bg-clay sm:aspect-[16/9] lg:aspect-[2/1]">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}, produto artesanal em madeira" class="size-full object-cover">
                                @endif
                            </div>
                            <div class="flex flex-col gap-4 border-t border-clay p-5 sm:flex-row sm:items-end sm:justify-between sm:p-7">
                                <div>
                                    <h3 class="font-serif text-3xl sm:text-4xl">{{ $product->name }}</h3>
                                    <p class="mt-2 text-lg font-semibold">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</p>
                                </div>
                                <span class="text-base font-semibold text-terracotta">Ver produto →</span>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>

            @if ($slides->count() > 1)
                <button type="button" @click="previous(); stop(); start()" class="absolute left-3 top-[38%] grid size-11 place-items-center rounded-sm border border-clay bg-paper text-ink" aria-label="Produto anterior">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <button type="button" @click="next(); stop(); start()" class="absolute right-3 top-[38%] grid size-11 place-items-center rounded-sm border border-clay bg-paper text-ink" aria-label="Próximo produto">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            @endif
        </div>

        @if ($slides->count() > 1)
            <div class="mt-4 flex justify-center" role="group" aria-label="Escolher produto">
                @foreach ($slides as $product)
                    <button type="button" @click="goTo({{ $loop->index }}); stop(); start()" class="grid size-11 place-items-center" aria-label="Mostrar {{ $product->name }}" :aria-current="current === {{ $loop->index }} ? 'true' : 'false'">
                        <span class="size-2.5 rounded-full border border-ink transition-colors" :class="current === {{ $loop->index }} ? 'bg-terracotta border-terracotta' : 'bg-paper'"></span>
                    </button>
                @endforeach
            </div>
        @endif
    @endif
</section>
