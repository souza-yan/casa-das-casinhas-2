@php
    $isAdmin = auth()->check() && auth()->user()->role === \App\Enums\Role::ADMIN;
    $sectionUrl = fn (string $section) => request()->routeIs('home') ? "#{$section}" : route('home')."#{$section}";
    $navigation = [
        ['label' => 'Início', 'section' => 'inicio'],
        ['label' => 'Produtos', 'section' => 'produtos'],
        ['label' => 'Sobre nós', 'section' => 'sobre'],
        ['label' => 'Como produzimos', 'section' => 'como-produzimos'],
    ];
@endphp

<header
    x-data="{
        menuOpen: false,
        profileOpen: false,
        visible: true,
        lastY: window.scrollY,
        scrollThreshold: 12,
        handleScroll() {
            const currentY = Math.max(window.scrollY, 0);

            if (this.menuOpen || this.profileOpen || currentY < 80) {
                this.visible = true;
                this.lastY = currentY;
                return;
            }

            const distance = currentY - this.lastY;

            if (Math.abs(distance) < this.scrollThreshold) return;

            this.visible = distance < 0;
            this.lastY = currentY;
        }
    }"
    @scroll.window.throttle.100ms="handleScroll()"
    @keydown.escape.window="menuOpen = false; profileOpen = false"
    :class="visible || menuOpen || profileOpen ? 'translate-y-0' : '-translate-y-full'"
    class="sticky top-0 z-50 border-b border-clay bg-paper/95 transition-transform duration-300 ease-out will-change-transform"
>
    <nav class="mx-auto grid h-[72px] max-w-[1400px] grid-cols-[44px_1fr_auto] items-center gap-3 px-4 sm:px-8 md:grid-cols-[auto_1fr_auto] lg:h-[88px] lg:px-12" aria-label="Navegação principal">
        <button type="button" @click="menuOpen = true; visible = true" class="grid size-11 place-items-center md:hidden" aria-label="Abrir menu">
            <svg class="size-8" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 10h22M5 16h22M5 22h22"/></svg>
        </button>

        <a href="{{ route('home') }}" class="justify-self-center font-serif text-xl tracking-[-.04em] md:justify-self-start lg:text-2xl">Casa das Casinhas</a>

        <div class="hidden items-center justify-center gap-7 text-sm font-medium md:flex lg:gap-10">
            @foreach ($navigation as $item)
                <a href="{{ $sectionUrl($item['section']) }}" class="transition hover:text-terracotta">{{ $item['label'] }}</a>
            @endforeach
        </div>

        <div class="flex items-center justify-end gap-1">
            @auth
                <div class="relative" @click.outside="profileOpen = false">
                    <button type="button" @click="profileOpen = ! profileOpen; visible = true" class="grid size-11 place-items-center" aria-label="Abrir menu do perfil" :aria-expanded="profileOpen">
                        <svg class="size-8" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="16" cy="11" r="5"/><path d="M6 28a10 10 0 0 1 20 0"/></svg>
                    </button>
                    <div x-show="profileOpen" x-cloak x-transition.opacity class="absolute right-0 top-full z-50 mt-2 w-52 border border-clay bg-paper p-2" role="menu">
                        <a href="{{ $isAdmin ? route('admin.dashboard') : route('dashboard') }}" class="block px-4 py-3 text-base transition hover:bg-clay/35" role="menuitem">Perfil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-3 text-left text-base transition hover:bg-clay/35" role="menuitem">Sair</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="grid size-11 place-items-center" aria-label="Entrar">
                    <svg class="size-8" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="16" cy="11" r="5"/><path d="M6 28a10 10 0 0 1 20 0"/></svg>
                </a>
            @endauth

            @unless ($isAdmin)
                <a href="{{ route('cart') }}" class="relative grid size-11 place-items-center" aria-label="Carrinho">
                    <svg class="size-8" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 6h3l3 14h11a3 3 0 0 0 3-2.4L26 10H8"/><circle cx="13" cy="27" r="1.5"/><circle cx="23" cy="27" r="1.5"/></svg>
                    @if (array_sum(session('cart.items', [])) > 0)
                        <span class="absolute right-0 top-0 grid min-h-5 min-w-5 place-items-center rounded-full bg-terracotta px-1 text-sm font-semibold leading-none text-paper">{{ array_sum(session('cart.items', [])) }}</span>
                    @endif
                </a>
            @endunless
        </div>
    </nav>

    <div x-show="menuOpen" x-cloak class="fixed inset-0 z-50 min-h-screen bg-paper px-6 py-5 md:hidden" x-transition.opacity>
        <div class="flex items-center justify-between border-b border-clay pb-5">
            <span class="font-serif text-2xl text-bark">Casa das Casinhas</span>
            <button type="button" @click="menuOpen = false" class="grid size-11 place-items-center" aria-label="Fechar menu">
                <svg class="size-8" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m7 7 18 18M25 7 7 25"/></svg>
            </button>
        </div>
        <div class="grid py-8 font-serif text-4xl">
            @foreach ($navigation as $item)
                <a href="{{ $sectionUrl($item['section']) }}" @click="menuOpen = false" class="border-b border-clay py-4">{{ $item['label'] }}</a>
            @endforeach
        </div>
    </div>
</header>
