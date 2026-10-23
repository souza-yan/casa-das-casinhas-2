<footer class="bg-ink text-paper">
    <div class="mx-auto grid max-w-[1400px] gap-12 px-5 py-14 sm:px-8 lg:grid-cols-[1.3fr_.7fr_.7fr] lg:px-12 lg:py-20">
        <div><p class="font-serif text-3xl">Casa das Casinhas</p><p class="mt-5 max-w-md text-sm leading-7 text-paper/60">Arte em madeira feita à mão, sob demanda, para levar cor e memória aos espaços de todos os dias.</p><a href="{{ route('store') }}" class="mt-7 inline-block text-xs font-semibold uppercase tracking-[.14em] underline underline-offset-8">Explorar a coleção</a></div>
        <div><h2 class="text-xs font-semibold uppercase tracking-[.18em] text-paper/45">Navegue</h2><div class="mt-5 grid gap-3 text-sm"><a href="{{ route('home') }}">Início</a><a href="{{ route('store') }}">Loja</a><a href="{{ route('home') }}#processo">Nosso fazer</a></div></div>
        <div><h2 class="text-xs font-semibold uppercase tracking-[.18em] text-paper/45">Sua conta</h2><div class="mt-5 grid gap-3 text-sm">@if (auth()->user()?->role === \App\Enums\Role::ADMIN)<a href="{{ route('admin.dashboard') }}">Administração</a><a href="{{ route('store') }}">Visualizar loja</a>@else<a href="{{ route('cart') }}">Carrinho</a>@auth<a href="{{ route('orders.index') }}">Pedidos</a>@else<a href="{{ route('login') }}">Entrar</a>@endauth @endif</div></div>
    </div>
    <div class="mx-auto flex max-w-[1400px] flex-col gap-2 border-t border-paper/15 px-5 py-6 text-[11px] text-paper/45 sm:flex-row sm:justify-between sm:px-8 lg:px-12"><span>© {{ date('Y') }} Casa das Casinhas</span><span>Feito à mão no Brasil</span></div>
</footer>
