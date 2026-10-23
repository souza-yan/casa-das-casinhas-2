<aside class="border-b border-white/10 bg-ink px-5 py-5 text-white lg:min-h-screen lg:border-b-0 lg:px-7 lg:py-8">
    <div class="flex items-center justify-between lg:block"><a href="{{ route('admin.dashboard') }}" class="font-serif text-2xl tracking-[-.04em]">Casa das Casinhas</a><span class="border border-white/20 px-3 py-1 text-[9px] font-semibold uppercase tracking-[.18em] lg:mt-3 lg:inline-block">Administração</span></div>
    <nav class="mt-6 flex gap-2 overflow-x-auto lg:mt-10 lg:grid" aria-label="Administração">
        @foreach ([['admin.dashboard', 'admin.dashboard', 'Visão geral'], ['admin.products.index', 'admin.products.*', 'Produtos'], ['admin.orders.index', 'admin.orders.*', 'Pedidos']] as [$route, $pattern, $label])
            <a href="{{ route($route) }}" @class(['border-l-2 px-4 py-3 text-xs font-medium uppercase tracking-[.1em] transition', 'border-white bg-white/10 text-white' => request()->routeIs($pattern), 'border-transparent text-white/60 hover:border-white/40 hover:text-white' => ! request()->routeIs($pattern)])>{{ $label }}</a>
        @endforeach
        <a href="{{ route('store') }}" class="flex items-center gap-2 border-l-2 border-transparent px-4 py-3 text-xs font-medium uppercase tracking-[.1em] text-white/60 transition hover:border-white/40 hover:text-white"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>Visualizar loja</a>
    </nav>
    <form method="POST" action="{{ route('logout') }}" class="mt-8 hidden border-t border-white/10 px-4 pt-6 lg:block">@csrf<button class="text-sm text-white/60 transition hover:text-white">Sair da conta</button></form>
</aside>
