@php
    $isAdmin = auth()->check() && auth()->user()->role === \App\Enums\Role::ADMIN;
    $isHome = request()->routeIs('home');
    $sectionUrl = fn (string $section) => $isHome ? "#{$section}" : route('home')."#{$section}";
    $navigation = [
        ['label' => 'Início', 'section' => 'inicio'],
        ['label' => 'Produtos', 'section' => 'produtos'],
        ['label' => 'Sobre nós', 'section' => 'sobre'],
        ['label' => 'Como produzimos', 'section' => 'como-produzimos'],
    ];
    $initialSection = $isHome ? 'inicio' : (request()->routeIs('store', 'products.*') ? 'produtos' : '');
@endphp

<header
    x-data="{
        menuOpen: false,
        profileOpen: false,
        visible: true,
        lastY: window.scrollY,
        scrollThreshold: 12,
        activeSection: '{{ $initialSection }}',
        isHome: {{ $isHome ? 'true' : 'false' }},
        init() {
            if (! this.isHome) return

            const sections = ['inicio', 'produtos', 'sobre', 'como-produzimos']
                .map(id => document.getElementById(id))
                .filter(Boolean)

            const observer = new IntersectionObserver(entries => {
                const current = entries
                    .filter(entry => entry.isIntersecting)
                    .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0]

                if (current) this.activeSection = current.target.id
            }, { rootMargin: '-35% 0px -55% 0px', threshold: [0.1, 0.5] })

            sections.forEach(section => observer.observe(section))
        },
        handleScroll() {
            const currentY = Math.max(window.scrollY, 0)

            if (this.menuOpen || this.profileOpen || currentY < 80) {
                this.visible = true
                this.lastY = currentY
                return
            }

            const distance = currentY - this.lastY
            if (Math.abs(distance) < this.scrollThreshold) return

            this.visible = distance < 0
            this.lastY = currentY
        }
    }"
    @scroll.window.throttle.100ms="handleScroll()"
    @keydown.escape.window="menuOpen = false; profileOpen = false"
    :class="visible || menuOpen || profileOpen ? 'translate-y-0' : '-translate-y-full'"
    class="sticky top-0 z-50 border-b border-clay bg-paper transition-transform duration-300 ease-out will-change-transform"
>
    <div class="bg-ink px-4 py-1.5 text-center text-sm font-medium text-paper">
        Produção artesanal em até 15 dias
    </div>

    <div class="hidden h-20 items-center justify-center border-b border-clay md:flex">
        <a href="{{ route('home') }}" class="font-serif text-2xl tracking-[-.04em]" aria-label="Ir para a página inicial">
            Casa das Casinhas
        </a>
    </div>

    <nav class="hidden md:block" aria-label="Navegação principal">
        <div class="mx-auto grid h-16 max-w-[1400px] grid-cols-[1fr_auto_1fr] items-center px-8 lg:px-12">
            <div class="flex items-center gap-1">
                <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" class="grid size-11 place-items-center" aria-label="Instagram">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".8" fill="currentColor" stroke="none"/></svg>
                </a>
                <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" class="grid size-11 place-items-center" aria-label="Facebook">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M14 8h3V4h-3c-3.1 0-5 2.1-5 5.2V12H6v4h3v5h4v-5h3l1-4h-4V9.5c0-.9.3-1.5 1-1.5Z"/></svg>
                </a>
            </div>
            <div class="flex items-center justify-center gap-8 lg:gap-11">
                @foreach ($navigation as $item)
                    <a
                        href="{{ $sectionUrl($item['section']) }}"
                        @click="activeSection = '{{ $item['section'] }}'"
                        class="border-b py-1 text-sm font-medium transition-colors"
                        :class="activeSection === '{{ $item['section'] }}' ? 'border-ink' : 'border-transparent hover:border-clay'"
                        :aria-current="activeSection === '{{ $item['section'] }}' ? 'page' : null"
                    >{{ $item['label'] }}</a>
                @endforeach
            </div>
            <div class="flex items-center justify-end gap-1">
                @auth
                    <div class="relative" @click.outside="profileOpen = false">
                        <button type="button" @click="profileOpen = ! profileOpen; visible = true" class="grid size-11 place-items-center" aria-label="Abrir menu do perfil" :aria-expanded="profileOpen">
                            <svg class="size-8" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="16" cy="11" r="5"/><path d="M6 28a10 10 0 0 1 20 0"/></svg>
                        </button>
                        <div x-show="profileOpen" x-cloak x-transition.opacity class="absolute right-0 top-full z-50 mt-2 w-52 rounded-sm border border-clay bg-paper p-2" role="menu">
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
        </div>
    </nav>

    <nav class="flex h-20 items-center justify-between px-4 md:hidden" aria-label="Navegação principal">
        <button type="button" @click="menuOpen = true; visible = true" class="grid size-11 place-items-center" aria-label="Abrir menu">
            <svg class="size-8" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 10h22M5 16h22M5 22h22"/></svg>
        </button>

        <a href="{{ route('home') }}" class="font-serif text-lg tracking-[-.04em]" aria-label="Ir para a página inicial">
            Casa das Casinhas
        </a>

        <div class="flex items-center gap-1">
            @auth
                <div class="relative" @click.outside="profileOpen = false">
                    <button type="button" @click="profileOpen = ! profileOpen; visible = true" class="grid size-11 place-items-center" aria-label="Abrir menu do perfil" :aria-expanded="profileOpen">
                        <svg class="size-8" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="16" cy="11" r="5"/><path d="M6 28a10 10 0 0 1 20 0"/></svg>
                    </button>
                    <div x-show="profileOpen" x-cloak x-transition.opacity class="absolute right-0 top-full z-50 mt-2 w-52 rounded-sm border border-clay bg-paper p-2" role="menu">
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

    <div x-show="menuOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[60] min-h-screen bg-paper px-6 py-5 md:hidden">
        <div class="flex items-center justify-between border-b border-clay pb-5">
            <a href="{{ route('home') }}" class="font-serif text-xl tracking-[-.04em]" aria-label="Ir para a página inicial">
                Casa das Casinhas
            </a>
            <button type="button" @click="menuOpen = false" class="grid size-11 place-items-center" aria-label="Fechar menu">
                <svg class="size-8" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m7 7 18 18M25 7 7 25"/></svg>
            </button>
        </div>
        <div class="grid pt-8 font-serif text-4xl">
            @foreach ($navigation as $item)
                <a
                    href="{{ $sectionUrl($item['section']) }}"
                    @click="menuOpen = false; activeSection = '{{ $item['section'] }}'"
                    class="border-b border-clay py-4"
                >{{ $item['label'] }}</a>
            @endforeach
        </div>
    </div>
</header>
