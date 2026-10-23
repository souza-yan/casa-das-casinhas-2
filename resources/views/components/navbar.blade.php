@php($isAdmin = auth()->check() && auth()->user()->role === \App\Enums\Role::ADMIN)

<header
    x-data="{
        open: false,
        visible: true,
        lastY: window.scrollY,
        handleScroll() {
            const currentY = window.scrollY;

            if (this.open || currentY < 24) {
                this.visible = true;
            } else if (Math.abs(currentY - this.lastY) > 6) {
                this.visible = currentY < this.lastY;
            }

            this.lastY = currentY;
        }
    }"
    @scroll.window.throttle.100ms="handleScroll()"
    :class="visible || open ? 'translate-y-0' : '-translate-y-full'"
    class="sticky top-0 z-50 border-b border-clay bg-paper/95 transition-transform duration-300 ease-out will-change-transform"
>
    <nav class="mx-auto flex h-[72px] max-w-[1400px] items-center justify-between px-5 sm:px-8 lg:h-[88px] lg:px-12" aria-label="Navegação principal">
        <button type="button" @click="open = true; visible = true" class="grid size-10 place-items-center md:hidden" aria-label="Abrir menu">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
        <a href="{{ route('home') }}" class="group leading-none">
            <span class="block font-serif text-xl tracking-[-.04em] lg:text-2xl">Casa das Casinhas</span>
            <span class="mt-1 block text-center text-sm font-semibold uppercase tracking-[.34em] text-ink/55">arte em madeira</span>
        </a>
        <div class="hidden items-center gap-9 text-sm font-medium uppercase tracking-[.12em] md:flex">
            <a href="{{ route('home') }}" class="transition hover:text-clay">Início</a>
            <a href="{{ route('store') }}" class="transition hover:text-clay">Loja</a>
            <a href="{{ route('home') }}#processo" class="transition hover:text-clay">Nosso fazer</a>
            @auth
                @if ($isAdmin)<a href="{{ route('admin.dashboard') }}" class="font-semibold text-clay transition hover:text-bark">Painel administrativo</a>@else<a href="{{ route('orders.index') }}" class="transition hover:text-clay">Pedidos</a>@endif
            @endauth
        </div>
        <div class="flex items-center gap-1 sm:gap-2">
            @auth
                @if ($isAdmin)
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="hidden px-2 text-sm font-semibold text-ink/55 transition hover:text-bark sm:block">Sair</button></form>
                @else
                    <a href="{{ route('dashboard') }}" class="grid size-10 place-items-center" aria-label="Minha conta"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="4"/><path d="M4.5 21a7.5 7.5 0 0 1 15 0"/></svg></a>
                @endif
            @else
                <a href="{{ route('login') }}" class="grid size-10 place-items-center" aria-label="Entrar"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="4"/><path d="M4.5 21a7.5 7.5 0 0 1 15 0"/></svg></a>
            @endauth
            @unless ($isAdmin)<a href="{{ route('cart') }}" class="relative grid size-10 place-items-center" aria-label="Carrinho"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20.5 7H6"/><circle cx="10" cy="20" r="1"/><circle cx="17" cy="20" r="1"/></svg>@if (array_sum(session('cart.items', [])) > 0)<span class="absolute right-0.5 top-0.5 grid size-4 place-items-center rounded-full bg-bark text-sm font-semibold text-paper">{{ array_sum(session('cart.items', [])) }}</span>@endif</a>@endunless
        </div>
    </nav>
    <div x-show="open" x-cloak class="fixed inset-0 z-50 min-h-screen bg-paper px-6 py-5 md:hidden" x-transition.opacity>
        <div class="flex items-center justify-between border-b border-clay pb-5"><span class="font-serif text-2xl font-semibold italic text-bark">Casa das Casinhas</span><button type="button" @click="open = false" class="grid size-10 place-items-center" aria-label="Fechar menu"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m5 5 14 14M19 5 5 19"/></svg></button></div>
        <div class="grid gap-1 py-8 font-serif text-4xl"><a href="{{ route('home') }}" @click="open = false" class="border-b border-clay py-4">Início</a><a href="{{ route('store') }}" @click="open = false" class="border-b border-clay py-4">Loja</a><a href="{{ route('home') }}#processo" @click="open = false" class="border-b border-clay py-4">Nosso fazer</a>@auth @if ($isAdmin)<a href="{{ route('admin.dashboard') }}" class="border-b border-clay py-4">Painel administrativo</a>@else<a href="{{ route('orders.index') }}" class="border-b border-clay py-4">Pedidos</a>@endif @endauth</div>
        @auth<form method="POST" action="{{ route('logout') }}">@csrf<button class="text-base font-semibold uppercase tracking-[.15em]">Sair da conta</button></form>@else<a href="{{ route('login') }}" class="text-base font-semibold uppercase tracking-[.15em]">Entrar na conta →</a>@endauth
    </div>
</header>
