@props(['products'])

@php
    $productsBySlug = $products->keyBy('slug');
    $slides = collect(['casinha', 'vila-de-3-casas', 'favela-de-5-casas'])
        ->map(fn (string $slug) => $productsBySlug->get($slug))
        ->filter()
        ->values();
@endphp

<section
    class="mx-auto w-full max-w-4xl pb-12 md:pb-16"
    aria-label="As mais pedidas"
    aria-roledescription="carousel"
    aria-live="off"
    x-data="{
        active: 0,
        total: {{ $slides->count() }},
        timer: null,
        touchStartX: 0,
        desktop: window.matchMedia('(min-width: 1024px)').matches,
        reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        init() {
            const desktopQuery = window.matchMedia('(min-width: 1024px)')
            const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)')
            desktopQuery.addEventListener('change', event => this.desktop = event.matches)
            motionQuery.addEventListener('change', event => {
                this.reducedMotion = event.matches
                this.stop()
                this.start()
            })
            this.start()
        },
        destroy() { this.stop() },
        start() {
            if (this.reducedMotion || this.total !== 3 || this.timer) return
            this.timer = setInterval(() => this.next(), 4000)
        },
        stop() {
            clearInterval(this.timer)
            this.timer = null
        },
        position(index) {
            if (this.total !== 3) return index - ((this.total - 1) / 2)
            const position = ((index - this.active + 3) % 3)
            return position === 2 ? -1 : position
        },
        cardStyle(index) {
            const position = this.position(index)
            const offset = this.total === 3
                ? (this.desktop ? 105 : 58)
                : (this.desktop ? 105 : 75)
            const scale = this.total === 3 && position === 0 ? 1 : (this.total === 3 ? .8 : .9)
            const opacity = this.total === 3 && position !== 0 ? .6 : 1
            const zIndex = this.total === 3 && position === 0 ? 20 : 10

            return `left: 50%; top: 0; transform: translateX(calc(-50% + ${position * offset}%)) scale(${scale}); opacity: ${opacity}; z-index: ${zIndex}`
        },
        goTo(index) { if (this.total === 3) this.active = index },
        next() { this.active = (this.active + 1) % this.total },
        previous() { this.active = (this.active - 1 + this.total) % this.total },
        select(index, event) {
            if (this.total === 3 && index !== this.active) {
                event.preventDefault()
                this.goTo(index)
            }
        },
        finishTouch(event) {
            if (this.total !== 3) return
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
    <h2 class="font-serif text-4xl tracking-[-.04em] sm:text-5xl">As mais pedidas</h2>

    @if ($slides->isNotEmpty())
        <div class="relative mt-8 h-96 w-full overflow-visible sm:mt-10 md:h-[28rem]">
            @foreach ($slides as $product)
                <a
                    href="{{ route('products.show', $product) }}"
                    class="absolute w-[52%] rounded-sm border border-clay bg-paper transition-[transform,opacity] duration-700 ease-in-out motion-reduce:transition-none md:w-[28%]"
                    :style="cardStyle({{ $loop->index }})"
                    @click="select({{ $loop->index }}, $event)"
                    :aria-current="active === {{ $loop->index }} ? 'true' : 'false'"
                    aria-label="{{ $product->name }}, R$ {{ number_format((float) $product->price, 2, ',', '.') }}"
                >
                    <div class="aspect-[4/5] overflow-hidden bg-clay">
                        @if ($product->image_path && $product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}, produto artesanal em madeira" class="block w-full aspect-[4/5] object-cover">
                        @else
                            <div class="block w-full aspect-[4/5] bg-clay" aria-hidden="true"></div>
                        @endif
                    </div>
                    <div class="border-t border-clay px-3 py-2">
                        <h3 class="truncate font-serif text-base leading-tight">{{ $product->name }}</h3>
                        <p class="mt-1 text-sm leading-tight text-ink/60">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</p>
                    </div>
                </a>
            @endforeach

            @if ($slides->count() === 3)
                <button type="button" @click="previous(); stop(); start()" class="absolute left-0 top-1/2 z-30 grid size-11 -translate-y-1/2 place-items-center rounded-sm border border-clay bg-paper text-ink sm:left-2" aria-label="Produto anterior">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <button type="button" @click="next(); stop(); start()" class="absolute right-0 top-1/2 z-30 grid size-11 -translate-y-1/2 place-items-center rounded-sm border border-clay bg-paper text-ink sm:right-2" aria-label="Próximo produto">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            @endif
        </div>

        @if ($slides->count() === 3)
            <div class="mt-2 flex justify-center" role="group" aria-label="Escolher produto">
                @foreach ($slides as $product)
                    <button type="button" @click="goTo({{ $loop->index }}); stop(); start()" class="grid size-11 place-items-center" aria-label="Mostrar {{ $product->name }}" :aria-current="active === {{ $loop->index }} ? 'true' : 'false'">
                        <span class="size-2.5 rounded-full border border-ink transition-colors motion-reduce:transition-none" :class="active === {{ $loop->index }} ? 'border-terracotta bg-terracotta' : 'bg-paper'"></span>
                    </button>
                @endforeach
            </div>
        @endif
    @endif
</section>
