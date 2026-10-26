@php
    $isAdmin = auth()->check() && auth()->user()->role === \App\Enums\Role::ADMIN;
    $cartCount = collect(session('cart.items', []))->sum(fn ($item) => is_array($item) ? (int) ($item['quantity'] ?? 0) : (int) $item);
    $navigation = [
        ['label' => 'Início', 'url' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'Produtos', 'url' => route('store'), 'active' => request()->routeIs('store', 'products.*')],
        ['label' => 'Sobre nós', 'url' => route('about'), 'active' => request()->routeIs('about')],
        ['label' => 'Como produzimos', 'url' => route('production'), 'active' => request()->routeIs('production')],
    ];
@endphp

<div
    x-data="{
        menuOpen: false,
        profileOpen: false
    }"
    @keydown.escape.window="menuOpen = false; profileOpen = false"
    class="contents"
>
    <div class="border-b border-paper/15 bg-ink px-4 py-1.5 text-center text-sm font-medium text-paper">
        Produzimos sua casinha em 15 dias
    </div>

    <div class="sticky top-0 z-50 flex h-16 items-center justify-center border-b border-clay bg-paper text-ink md:h-20">
        <a href="{{ route('home') }}" class="font-serif text-2xl tracking-[-.04em]" aria-label="Ir para a página inicial">
            Casa das Casinhas
        </a>
    </div>

    <header class="relative z-40 border-b border-paper/15 bg-ink text-paper">
    <nav class="hidden md:block" aria-label="Navegação principal">
        <div class="mx-auto grid h-16 max-w-[1400px] grid-cols-[1fr_auto_1fr] items-center px-8 lg:px-12">
            <x-social-links />
            <div class="flex items-center justify-center gap-8 lg:gap-11">
                @foreach ($navigation as $item)
                    <a
                        href="{{ $item['url'] }}"
                        class="border-b py-1 text-sm font-medium transition-colors {{ $item['active'] ? 'border-paper' : 'border-transparent hover:border-paper/50' }}"
                        @if ($item['active']) aria-current="page" @endif
                    >{{ $item['label'] }}</a>
                @endforeach
            </div>
            <div class="flex items-center justify-end gap-1">
                @auth
                    <div class="relative" @click.outside="profileOpen = false">
                        <button type="button" @click="profileOpen = ! profileOpen" class="grid size-11 place-items-center" aria-label="Abrir menu do perfil" :aria-expanded="profileOpen">
                            <svg class="size-8" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="16" cy="11" r="5"/><path d="M6 28a10 10 0 0 1 20 0"/></svg>
                        </button>
                        <div x-show="profileOpen" x-cloak x-transition.opacity class="absolute right-0 top-full z-50 mt-2 w-52 rounded-sm border border-clay bg-paper p-2 text-ink" role="menu">
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
                        @if ($cartCount > 0)
                            <span class="absolute right-0 top-0 grid min-h-5 min-w-5 place-items-center rounded-full bg-terracotta px-1 text-sm font-semibold leading-none text-paper">{{ $cartCount }}</span>
                        @endif
                    </a>
                @endunless
            </div>
        </div>
    </nav>

    <nav class="flex h-20 items-center justify-between px-4 md:hidden" aria-label="Navegação principal">
        <button type="button" @click="menuOpen = true" class="grid size-11 place-items-center" aria-label="Abrir menu">
            <svg class="size-8" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 10h22M5 16h22M5 22h22"/></svg>
        </button>

        <div class="flex items-center gap-1">
            @auth
                <div class="relative" @click.outside="profileOpen = false">
                    <button type="button" @click="profileOpen = ! profileOpen" class="grid size-11 place-items-center" aria-label="Abrir menu do perfil" :aria-expanded="profileOpen">
                        <svg class="size-8" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="16" cy="11" r="5"/><path d="M6 28a10 10 0 0 1 20 0"/></svg>
                    </button>
                    <div x-show="profileOpen" x-cloak x-transition.opacity class="absolute right-0 top-full z-50 mt-2 w-52 rounded-sm border border-clay bg-paper p-2 text-ink" role="menu">
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
                    @if ($cartCount > 0)
                        <span class="absolute right-0 top-0 grid min-h-5 min-w-5 place-items-center rounded-full bg-terracotta px-1 text-sm font-semibold leading-none text-paper">{{ $cartCount }}</span>
                    @endif
                </a>
            @endunless
        </div>
    </nav>

    <div x-show="menuOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[60] min-h-screen bg-ink px-6 py-5 text-paper md:hidden">
        <div class="flex items-center justify-between border-b border-paper/20 pb-5">
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
                    href="{{ $item['url'] }}"
                    @click="menuOpen = false"
                    class="border-b border-paper/20 py-4"
                    @if ($item['active']) aria-current="page" @endif
                >{{ $item['label'] }}</a>
            @endforeach
        </div>
        <x-social-links class="mt-6" />
    </div>

    </header>
</div>
